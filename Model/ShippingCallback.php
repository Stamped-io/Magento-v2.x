<?php

namespace Dintero\Checkout\Model;

use Dintero\Checkout\Api\Data\Shipping\RequestInterface;
use Dintero\Checkout\Api\Data\Shipping\RequestInterfaceFactory;
use Dintero\Checkout\Api\Data\Shipping\ResponseInterfaceFactory;
use Dintero\Checkout\Api\Data\ShippingMethodInterfaceFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Shipping\Helper\Carrier;
use Psr\Log\LoggerInterface;

/**
 * Class ShippingCallback
 *
 * @package Dintero\Checkout\Model
 */
class ShippingCallback implements \Dintero\Checkout\Api\ShippingCallbackInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var DataObjectHelper
     */
    protected $objectHelper;

    /**
     * @var RequestInterfaceFactory
     */
    protected $requestFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var Quote
     */
    protected $quoteResource;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var ShippingMethodInterfaceFactory
     */
    protected $shippingOptionFactory;

    /**
     * @var Carrier $carrierHelper
     */
    protected $carrierHelper;

    /**
     * @var CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var \Dintero\Checkout\Api\Data\OrderInterfaceFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * @var \Dintero\Checkout\Api\Data\Order\ItemInterfaceFactory $orderItemFactory
     */
    protected $orderItemFactory;

    /**
     * ShippingCallback constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param SerializerInterface $serializer
     * @param ResponseInterfaceFactory $responseFactory
     * @param LoggerInterface $logger
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param RequestInterfaceFactory $requestFactory
     * @param Quote $quoteResource
     * @param QuoteFactory $quoteFactory
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param ShippingMethodInterfaceFactory $shippingOptionFactory
     * @param Carrier $carrierHelper
     * @param CollectionFactory $countryCollectionFactory
     * @param \Dintero\Checkout\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Dintero\Checkout\Api\Data\Order\ItemInterfaceFactory $orderItemFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        SerializerInterface $serializer,
        ResponseInterfaceFactory $responseFactory,
        LoggerInterface $logger,
        DataObjectHelper $dataObjectHelper,
        DataObjectFactory $dataObjectFactory,
        RequestInterfaceFactory $requestFactory,
        Quote $quoteResource,
        QuoteFactory $quoteFactory,
        ShippingMethodManagementInterface $shippingMethodManagement,
        ShippingMethodInterfaceFactory $shippingOptionFactory,
        Carrier $carrierHelper,
        CollectionFactory $countryCollectionFactory,
        \Dintero\Checkout\Api\Data\OrderInterfaceFactory $orderFactory,
        \Dintero\Checkout\Api\Data\Order\ItemInterfaceFactory $orderItemFactory
    ) {
        $this->request = $request;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->responseFactory = $responseFactory;
        $this->objectHelper = $dataObjectHelper;
        $this->requestFactory = $requestFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->quoteResource = $quoteResource;
        $this->quoteFactory = $quoteFactory;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->shippingOptionFactory = $shippingOptionFactory;
        $this->carrierHelper = $carrierHelper;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * @return \Dintero\Checkout\Api\Data\Shipping\ResponseInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function getOptions()
    {
        /** @var \Dintero\Checkout\Api\Data\Shipping\RequestInterface $request */
        $request = $this->requestFactory->create();
        $requestBody = $this->dataObjectFactory->create()
            ->setData($this->serializer->unserialize($this->request->getContent()));
        $this->objectHelper->populateWithArray(
            $request,
            array_merge($requestBody->getData(), $requestBody->getData('order')),
            RequestInterface::class
        );

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $request->getMerchantReference(), 'reserved_order_id');

        if (!$quote->getIsActive()) {
            throw new \Exception(__('Quote is not valid'));
        }

        $quote->getShippingAddress()
            ->setPostcode($request->getShippingAddress()->getPostalCode())
            ->setStreetFull($request->getShippingAddress()->getAddressLine())
            ->setFirstname($request->getShippingAddress()->getFirstName())
            ->setLastname($request->getShippingAddress()->getLastName())
            ->setCountryId($request->getShippingAddress()->getCountry())
            ->setEmail($request->getShippingAddress()->getEmail())
            ->setCity($request->getShippingAddress()->getPostalPlace())
            ->setTelephone($request->getShippingAddress()->getPhoneNumber())
            ->setCollectShippingRates(true)
            ->setTotalsCollected(false);
        $quote->collectTotals();
        $this->quoteResource->save($quote);
        $shippingMethods = $this->shippingMethodManagement->getList($quote->getId());

        $shippingOptions = [];

        /** @var \Magento\Quote\Model\Cart\ShippingMethod $shippingMethod */
        foreach ($shippingMethods as $shippingMethod) {
            /** @var \Dintero\Checkout\Api\Data\ShippingMethodInterface $shippingOption */
            $shippingOption = $this->shippingOptionFactory->create();

            $shippingOption->setAmount($shippingMethod->getPriceExclTax() * 100)
                ->setVat(($shippingMethod->getPriceInclTax() - $shippingMethod->getPriceExclTax()) * 100)
                ->setVatAmount(0)
                ->setOperator($shippingMethod->getCarrierTitle())
                ->setOperatorProductId($shippingMethod->getMethodCode())
                ->setDeliveryMethod(
                    $shippingMethod->getMethodCode() == 'pickup' ?
                        $shippingOption::DELIVERY_METHOD_PICKUP : $shippingOption::DELIVERY_METHOD_DELIVERY
                )
                ->setTitle($shippingMethod->getMethodTitle())
                ->setDescription($shippingMethod->getMethodTitle())
                ->setLineId(sprintf('%s_%s', $shippingMethod->getCarrierCode(), $shippingMethod->getMethodCode()))
                ->setId(sprintf('%s_%s', $shippingMethod->getCarrierCode(), $shippingMethod->getMethodCode()))
                ->setCountries($this->getCountries($shippingMethod->getCarrierCode()));

            if ($shippingOption->getVatAmount() > 0) {
                $shippingOption->setVat($shippingOption->getVatAmount() / ($shippingOption->getAmount() / 100));
            }

            array_push($shippingOptions, $shippingOption);
        }

        return $this->responseFactory->create()
            ->setShippingOptions($shippingOptions)
            ->setOrder($this->prepareOrder($quote));
    }

    /**
     * @param $carrierCode
     * @return false|string[]
     */
    protected function getCountries($carrierCode)
    {
        if ($this->carrierHelper->getCarrierConfigValue($carrierCode, 'sallowspecific')) {
            return explode(
                ',',
                $this->carrierHelper
                    ->getCarrierConfigValue($carrierCode, 'specificcountry')
            );
        }
        return $this->countryCollectionFactory->create()->getAllIds();
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Dintero\Checkout\Api\Data\OrderInterface
     */
    protected function prepareOrder(\Magento\Quote\Model\Quote $quote)
    {
        /** @var \Dintero\Checkout\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create();
        $order->setAmount($quote->getBaseGrandTotal() * 100)
            ->setCurrency($quote->getBaseCurrencyCode());

        $items = [];

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            /** @var \Dintero\Checkout\Api\Data\Order\ItemInterface $orderItem */
            $orderItem = $this->orderItemFactory->create();
            $orderItem->setAmount(($quoteItem->getBaseRowTotalInclTax() - $quoteItem->getBaseDiscountAmount()) * 100)
                ->setId($quoteItem->getSku())
                ->setLineId($quoteItem->getSku())
                ->setDescription(sprintf('%s (%s)', $quoteItem->getName(), $quoteItem->getSku()))
                ->setQuantity($quoteItem->getQty() * 1)
                ->setVat($quoteItem->getTaxPercent())
                ->setVatAmount($quoteItem->getBaseTaxAmount() * 100);
            array_push($items, $orderItem);
        }
        $tax = $quote->getShippingAddress()->getBaseTaxAmount() * 100;
        $order->setVatAmount($tax ?? 0);
        return $order->setItems($items);
    }
}
