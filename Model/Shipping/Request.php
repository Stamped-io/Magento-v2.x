<?php

namespace Dintero\Checkout\Model\Shipping;

use Dintero\Checkout\Api\Data\ItemInterface;
use Dintero\Checkout\Api\Data\Shipping\RequestInterface;

/**
 * Class Request
 *
 * @package Dintero\Checkout\Model\Shipping
 */
class Request implements RequestInterface
{
    /**
     * @var string $sessionId
     */
    protected $sessionId;

    /**
     * @var string $accountId
     */
    protected $accountId;

    /**
     * @var string $merchantReferenceId
     */
    protected $merchantReferenceId;

    /**
     * @var \Magento\Framework\DataObject $shippingAddress
     */
    protected $shippingAddress;

    /**
     * @var \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     */
    protected $quoteResource;

    /**
     * @var \Dintero\Checkout\Helper\Config $config
     */
    protected $config;

    /**
     * @var array \Magento\Framework\DataObject[]
     */
    protected $items = [];

    /**
     * @var \Dintero\Checkout\Api\Data\ItemInterfaceFactory $itemFactory
     */
    protected $itemFactory;

    /**
     * @var \Dintero\Checkout\Api\Data\AddressInterfaceFactory $addressFactory
     */
    protected $addressFactory;

    /**
     * Request constructor.
     *
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Dintero\Checkout\Helper\Config $config
     * @param \Dintero\Checkout\Api\Data\ItemInterfaceFactory $itemFactory
     * @param \Dintero\Checkout\Api\Data\AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote $quoteResource,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Dintero\Checkout\Helper\Config $config,
        \Dintero\Checkout\Api\Data\ItemInterfaceFactory $itemFactory,
        \Dintero\Checkout\Api\Data\AddressInterfaceFactory $addressFactory
    ) {
        $this->quoteResource = $quoteResource;
        $this->quoteFactory = $quoteFactory;
        $this->config = $config;
        $this->itemFactory = $itemFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @param string $id
     * @return $this|RequestInterface
     */
    public function setId($id)
    {
        $this->sessionId = $id;
        return $this;
    }

    /**
     * @param string $accountId
     * @return $this|RequestInterface
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * @param string $referenceId
     * @return $this|RequestInterface
     */
    public function setMerchantReference($referenceId)
    {
        $this->merchantReferenceId = $referenceId;
        return $this;
    }

    /**
     * @param ItemInterface[] $items
     * @return $this|RequestInterface
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param \Dintero\Checkout\Api\Data\AddressInterface
     * @return $this|RequestInterface
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->sessionId;
    }

    /**
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param $lineId
     * @return mixed|null
     */
    public function getItem($lineId)
    {
        return $this->items[$lineId] ?? null;
    }

    /**
     * @return \Dintero\Checkout\Api\Data\AddressInterface
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @return string
     */
    public function getMerchantReference()
    {
        return $this->merchantReferenceId;
    }

    /**
     * @param string $sessionId
     * @return bool
     */
    public function isValid($sessionId): bool
    {
        return $this->getId() == $sessionId;
    }
}
