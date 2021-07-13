<?php

namespace Dintero\Checkout\Model\Api;

use Dintero\Checkout\Helper\Config as ConfigHelper;
use Dintero\Checkout\Model\Gateway\Http\Client as DinteroHpClient;
use Dintero\Checkout\Model\Payment\Token;
use Dintero\Checkout\Model\Payment\TokenFactory;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferBuilderFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * API Client for Dintero payment method
 *
 * @package Dintero\Checkout\Model\Gateway\Http
 */
class Client
{
    /*
     * Dintero api endpoint
     */
    const API_BASE_URL = 'https://api.dintero.com/v1';

    /*
     * Checkout api endpoint
     */
    const CHECKOUT_API_BASE_URL = 'https://checkout.dintero.com/v1';

    /*
     * Status captured
     */
    const STATUS_CAPTURED = 'CAPTURED';

    /*
     * Status authorized
     */
    const STATUS_AUTHORIZED = 'AUTHORIZED';

    /*
     * Status partially captured
     */
    const STATUS_PARTIALLY_CAPTURED = 'PARTIALLY_CAPTURED';

    /*
     * Standard
     */
    const TYPE_STANDARD = 'standard';

    /*
     * Express
     */
    const TYPE_EXPRESS = 'express';

    /*
     * Embedded
     */
    const TYPE_EMBEDDED = 'embedded';

    /**
     * HTTP Client
     *
     * @var DinteroHpClient $client
     */
    private $client;

    /**
     * Config helper
     *
     * @var ConfigHelper $configHelper
     */
    private $configHelper;

    /**
     * Transfer builder factory
     *
     * @var TransferBuilderFactory $transferBuilderFactory
     */
    private $transferBuilderFactory;

    /**
     * Token factory
     *
     * @var TokenFactory $tokenFactory
     */
    private $tokenFactory;

    /**
     * Logger
     *
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * JSON Converter
     *
     * @var Json $converter
     */
    private $converter;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     */
    protected $quoteResource;


    protected $objectManager;

    /**
     * Client constructor.
     *
     * @param DinteroHpClient $client
     * @param ConfigHelper $configHelper
     * @param TransferBuilderFactory $transferBuilderFactory
     * @param TokenFactory $tokenFactory
     * @param LoggerInterface $logger
     * @param Json $converter
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        DinteroHpClient $client,
        ConfigHelper $configHelper,
        TransferBuilderFactory $transferBuilderFactory,
        TokenFactory $tokenFactory,
        LoggerInterface $logger,
        Json $converter,
        \Magento\Quote\Model\ResourceModel\Quote $quoteResource,
        ObjectManagerInterface $objectManager
    ) {
        $this->client = $client;
        $this->configHelper = $configHelper;
        $this->transferBuilderFactory = $transferBuilderFactory;
        $this->tokenFactory = $tokenFactory;
        $this->logger = $logger;
        $this->converter = $converter;
        $this->quoteResource = $quoteResource;
        $this->type = self::TYPE_STANDARD;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type ?? self::TYPE_STANDARD;
    }

    /**
     * @return string
     */
    protected function getCallbackUrl()
    {
        if ($this->isExpress()) {
            return $this->configHelper->getExpressCheckoutCallback();
        }

        if ($this->isEmbedded()) {
            return $this->configHelper->getEmbeddedCheckoutCallback();
        }

        return $this->configHelper->getCallbackUrl();
    }

    /**
     * Retrieving actual version of Magento
     *
     * @return string
     */
    private function getVersion()
    {
        return $this->objectManager->get(ProductMetadata::class)->getVersion();
    }

    /**
     * Building api endpoint
     *
     * @param string $endpoint
     * @return string
     */
    private function getApiUri($endpoint)
    {
        return rtrim(self::API_BASE_URL, '/') . '/' . trim($endpoint, '/');
    }

    /**
     * Building checkout api uri
     *
     * @param string $endpoint
     * @return string
     */
    private function getCheckoutApiUri($endpoint)
    {
        return rtrim(self::CHECKOUT_API_BASE_URL, '/') . '/' . trim($endpoint, '/');
    }

    /**
     * Initializing request
     *
     * @param string $endpoint
     * @param Token|null $token
     * @return \Magento\Payment\Gateway\Http\TransferBuilder
     */
    private function initRequest($endpoint, $token = null)
    {
        $defaultHeaders = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
        ];

        if ($token && $token instanceof Token) {
            $defaultHeaders['Authorization'] = $token->getTokenType() . ' ' . $token->getToken();
        }

        return $this->transferBuilderFactory->create()
            ->setUri($endpoint)
            ->setHeaders($defaultHeaders)
            ->shouldEncode(false)
            ->setMethod(\Zend_Http_Client::POST);
    }

    /**
     * Retrieving metadata
     *
     * @return array
     */
    private function getMetaData()
    {
        return [
            'system_x_id' => __('Magento'),
            'number_x' => $this->getVersion(),
        ];
    }

    /**
     * @return bool
     */
    private function isExpress()
    {
        return $this->getType() === self::TYPE_EXPRESS && $this->configHelper->isExpress();
    }

    /**
     * @return bool
     */
    private function isEmbedded()
    {
        return $this->getType() === self::TYPE_EMBEDDED && $this->configHelper->isEmbedded();
    }

    /**
     * Initialize checkout
     *
     * @param Order $order
     * @return array
     * @throws ClientException
     * @throws ConverterException
     */
    public function initCheckout(Order $order)
    {
        $request = $this->initRequest(
            $this->getCheckoutApiUri('sessions-profile'),
            $this->getToken()
        )->setBody($this->converter->serialize($this->prepareData($order, null)));

        return $this->client->placeRequest($request->build());
    }

    /**
     * @param \Magento\Sales\Model\Order|\Magento\Quote\Model\Quote $salesObject
     * @return array|bool|float|int|mixed|string|null
     * @throws ClientException
     * @throws ConverterException
     */
    private function initSession($salesObject)
    {
        $request = $this->initRequest(
            $this->getCheckoutApiUri('sessions-profile'),
            $this->getToken()
        )->setBody($this->converter->serialize($this->prepareData($salesObject, null)));
        return $this->client->placeRequest($request->build());
    }

    /**
     * @param Quote $quote
     * @return array|bool|float|int|mixed|string|null
     * @throws ClientException
     * @throws ConverterException
     */
    public function initSessionFromQuote(Quote $quote)
    {
        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId();
            $this->quoteResource->save($quote);
        }

        return $this->initSession($quote);
    }

    /**
     * Retrieving token
     *
     * @return Token
     * @throws \Exception
     */
    private function getToken()
    {
        /** @var \Dintero\Checkout\Model\Payment\Token $token */
        $token = $this->tokenFactory->create(['data' => $this->getAccessToken()]);
        if (!$token->getToken()) {
            throw new \Exception(__('Failed to get access token'));
        }
        return $token;
    }

    /**
     * Retrieving access token
     *
     * @throws \Exception
     * @return array
     */
    private function getAccessToken()
    {
        $accountsUrl = $this->getApiUri(sprintf('accounts/%s', $this->configHelper->getFullAccountId()));
        $accessTokenUrl = $this->getApiUri(
            sprintf('accounts/%s/auth/token', $this->configHelper->getFullAccountId())
        );

        $request = $this->initRequest($accessTokenUrl)
            ->setAuthUsername($this->configHelper->getClientId())
            ->setAuthPassword($this->configHelper->getClientSecret())
            ->setBody($this->converter->serialize([
                'grant_type' => 'client_credentials',
                'audience' => $accountsUrl
            ]));

        try {
            $response = $this->client->placeRequest($request->build());

            if (!isset($response['access_token'])) {
                throw new \Exception(__('Could not retrieve the access token'));
            }

            return $response;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return [];
    }

    /**
     * Preparing data for submission
     *
     * @param Order|\Magento\Quote\Model\Quote $salesObject
     * @param AbstractModel|null $salesDocument
     * @return array
     */
    private function prepareData($salesObject, $salesDocument = null)
    {
        $customerEmail = $salesObject->getCustomerIsGuest() ?
            $salesObject->getBillingAddress()->getEmail() :
            $salesObject->getCustomerEmail();
        $baseOrderTotal = $salesDocument ? $salesDocument->getBaseGrandTotal() : $salesObject->getBaseGrandTotal();
        $orderData = [
            'profile_id' => $this->configHelper->getProfileId(),
            'url' => [
                'return_url' => $this->configHelper->getReturnUrl(),
                'callback_url' => $this->getCallbackUrl(),
            ],
            'order' => [
                'amount' => $baseOrderTotal * 100,
                'currency' => $salesObject->getBaseCurrencyCode(),
                'merchant_reference' =>  $salesObject->getReservedOrderId() ?? $salesObject->getIncrementId(),
                'items' => $this->prepareItems($salesObject),
            ],
        ];

        if ($salesObject->getBillingAddress()->getPostcode()) {
            $orderData['customer'] = [
                'phone_number' => $salesObject->getBillingAddress()->getTelephone()
            ];
            $orderData['order']['billing_address'] = [
                'first_name' => $salesObject->getBillingAddress()->getFirstname(),
                'last_name' => $salesObject->getBillingAddress()->getLastname(),
                'address_line' => implode(',', $salesObject->getBillingAddress()->getStreet()),
                'postal_code' => $salesObject->getBillingAddress()->getPostcode(),
                'postal_place' => $salesObject->getBillingAddress()->getCity(),
                'country' => $salesObject->getBillingAddress()->getCountryId(),
                // 'phone_number' => $salesObject->getBillingAddress()->getTelephone(),
            ];
        }

        if ($this->isExpress()) {
            $orderData['express']['customer_types'] = ['b2c', 'b2b'];
            $orderData['express']['shipping_address_callback_url'] = $this->configHelper->getShippingCallbackUrl();
            $orderData['express']['shipping_options'] = [];
        }

        if (!empty($customerEmail)) {
            $orderData['customer']['email'] = $customerEmail;
        }

        if ($salesObject->getShippingAddress() && $salesObject->getShippingAddress()->getPostcode()) {
            $orderData['shipping_address'] = [
                'first_name' => $salesObject->getShippingAddress()->getFirstname(),
                'last_name' => $salesObject->getShippingAddress()->getLastname(),
                'address_line' => implode(',', $salesObject->getShippingAddress()->getStreet()),
                'postal_code' => $salesObject->getShippingAddress()->getPostcode(),
                'postal_place' => $salesObject->getShippingAddress()->getCity(),
                'country' => $salesObject->getShippingAddress()->getCountryId(),
                'phone_number' => $salesObject->getShippingAddress()->getTelephone(),
            ];
        }

        if (!empty($this->getMetaData()) && is_array($this->getMetaData())) {
            $orderData['metadata'] = $this->getMetaData();
        }

        $dataObject = new DataObject($orderData);
        return $dataObject->toArray();
    }

    /**
     * Preparing invoice items
     *
     * @param Order\Invoice $invoice
     * @return array
     */
    private function prepareSalesItems(AbstractModel $invoice)
    {
        $items = [];
        /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
        foreach ($invoice->getAllItems() as $item) {
            if ($item->isDeleted() || $item->getOrderItem()->getParentItemId()) {
                continue;
            }

            array_push($items, [
                'id' => $item->getSku(),
                'line_id' => $item->getSku(),
                'amount' => ($item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount() + $item->getBaseTaxAmount()) * 100,
            ]);
        }

        // adding shipping as a separate item
        if ($invoice->getBaseShippingAmount() > 0) {
            array_push($items, [
                'id' => $invoice->getOrder()->getShippingMethod(),
                'description' => str_replace(' - ', ', ', $invoice->getOrder()->getShippingDescription()),
                'vat_amount' => $invoice->getBaseShippingTaxAmount() * 100,
                'amount' => $invoice->getBaseShippingInclTax() * 100,
                'line_id' => $invoice->getOrder()->getShippingMethod(),
            ]);
        }

        return $items;
    }

    /**
     * Preparing order items for sending
     *
     * @param Order|\Magento\Quote\Model\Quote $order
     * @return array
     */
    private function prepareItems($salesObject)
    {
        $items = [];
        $isQuote = $salesObject instanceof \Magento\Quote\Model\Quote;
        foreach ($salesObject->getAllVisibleItems() as $item) {
            array_push($items, [
                'id' => $item->getSku(),
                'description' => sprintf('%s (%s)', $item->getName(), $item->getSku()),
                'quantity' => ($isQuote ? $item->getQty() : $item->getQtyOrdered()) * 1,
                'amount' =>  ($item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount()) * 100,
                'line_id' => $item->getSku(),
                'vat_amount' => $item->getBaseTaxAmount() * 100, // NOK cannot be floating
                'vat' => $item->getTaxPercent() * 1,
            ]);
        }

        $shippingTotalsObject = $isQuote ? $salesObject->getShippingAddress() : $salesObject;

        // no need to add shipping items for express checkout as shipping options are retrieved via callback
        if ($this->isExpress()) {
            return $items;
        }

        // adding shipping as a separate item
        if (!$salesObject->getIsVirtual() && $shippingTotalsObject->getBaseShippingAmount() > 0) {
            array_push($items, [
                'id' => $shippingTotalsObject->getShippingMethod(),
                'description' => str_replace(' - ', ',', $shippingTotalsObject->getShippingDescription()),
                'quantity' => 1,
                'vat_amount' => $shippingTotalsObject->getBaseShippingTaxAmount() * 100,
                'amount' => $shippingTotalsObject->getBaseShippingInclTax() * 100,
                'line_id' => $shippingTotalsObject->getShippingMethod(),
            ]);
        }

        return $items;
    }

    /**
     * Retrieving transaction by id
     *
     * @param string $transactionId
     * @return array|bool|float|int|mixed|string|null
     * @throws ClientException
     * @throws ConverterException
     */
    public function getTransaction($transactionId)
    {
        $endpoint = $this->getCheckoutApiUri(sprintf('transactions/%s', $transactionId));
        $request = $this->initRequest($endpoint, $this->getToken())
            ->setMethod(\Zend_Http_Client::GET);

        return $this->client->placeRequest($request->build());
    }

    /**
     * Capturing transaction
     *
     * @param string $transactionId
     * @param Order\Payment $payment
     * @param $amount
     * @return bool
     * @throws ClientException
     * @throws ConverterException
     */
    public function capture($transactionId, \Magento\Sales\Model\Order\Payment $payment, $amount)
    {
        $transaction = $this->getTransaction($transactionId);

        if (!$this->canCaptureTransaction($transaction)) {
            throw new \Exception(__('This transaction cannot be captured'));
        }

        $requestData = [
            'id' => $transactionId,
            'amount' => $amount * 100,
            'items' => $payment->getSalesDocument() ?
                $this->prepareSalesItems($payment->getSalesDocument()) : $this->prepareItems($payment->getOrder())
        ];

        $endpoint = $this->getCheckoutApiUri(sprintf('transactions/%s/capture', $transactionId));
        $request = $this->initRequest($endpoint, $this->getToken())
            ->setBody($this->converter->serialize($requestData));

        return $this->client->placeRequest($request->build());
    }

    /**
     * Refunding
     *
     * @param string $transactionId
     * @param Order\Payment $payment
     * @param $amount
     * @return array|bool|float|int|mixed|string|null
     * @throws ClientException
     * @throws ConverterException
     */
    public function refund(\Magento\Sales\Model\Order\Payment $payment, $amount)
    {
        $transactionId = str_replace(
            '-' . TransactionInterface::TYPE_CAPTURE,
            '',
            $payment->getParentTransactionId()
        );
        $requestData = [
            'id' => $transactionId,
            'amount' => $amount * 100,
            'items' => $this->prepareSalesItems($payment->getSalesDocument())
        ];

        $endpoint = $this->getCheckoutApiUri(sprintf('transactions/%s/refund', $transactionId));

        $request = $this->initRequest($endpoint, $this->getToken())
            ->setBody($this->converter->serialize($requestData));

        return $this->client->placeRequest($request->build());
    }

    /**
     * Voiding transaction
     *
     * @param $transactionId
     * @return array|bool|float|int|mixed|string|null
     * @throws ClientException
     * @throws ConverterException
     */
    public function void($transactionId)
    {
        $endpoint = $this->getCheckoutApiUri(sprintf('transactions/%s/void', $transactionId));
        $request = $this->initRequest($endpoint, $this->getToken())->setBody(null);
        return $this->client->placeRequest($request->build());
    }

    /**
     * Retrieving session
     *
     * @param string $sessionId
     * @return array|bool|float|int|mixed|string|null
     * @throws ClientException
     * @throws ConverterException
     */
    public function getSessionInfo($sessionId)
    {
        $endpoint = $this->getCheckoutApiUri(sprintf('session/%s', $sessionId));
        $request = $this->initRequest($endpoint, $this->getToken())->setBody(null);
        return $this->client->placeRequest($request->build());
    }

    /**
     * Checking whether transaction can be captured or not
     *
     * @param array $transaction
     * @return bool
     */
    private function canCaptureTransaction($transaction)
    {
        return isset($transaction['status']) &&
            in_array($transaction['status'], [self::STATUS_AUTHORIZED, self::STATUS_PARTIALLY_CAPTURED]);
    }
}
