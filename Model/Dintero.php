<?php

namespace Dintero\Checkout\Model;

use Dintero\Checkout\Model\Api\Client;
use Dintero\Checkout\Model\Gateway\ResponseFactory;

use Dintero\Checkout\Model\Payment\Response;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class Dintero
 *
 * @package Dintero\Payment\Model
 */
class Dintero extends AbstractMethod
{
    /*
     * Method code
     */
    const METHOD_CODE = 'dintero';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCaptureOnce = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canReviewPayment = false;

    /**
     * This may happen when amount is captured, but not settled
     *
     * @var bool
     */
    protected $_canCancelInvoice = true;

    /**
     * Order factory
     *
     * @var OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * Client
     *
     * @var Client $client
     */
    protected $client;

    /**
     * Response
     *
     * @var \Dintero\Checkout\Model\Gateway\Response $response
     */
    protected $response;

    /**
     * @var LoggerInterface $psrLogger
     */
    protected $psrLogger;

    /**
     * Adapter
     *
     * @var Adapter $adapter
     */
    protected $adapter;

    /**
     * Payment session
     *
     * @var Response $paymentSession
     */
    protected $paymentSession;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     */
    protected $orderSender;

    /**
     * Dintero constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     * @param OrderSender $orderSender
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        OrderFactory $orderFactory,
        Client $client,
        Adapter $adapter,
        ResponseFactory $responseFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null,
        OrderSender $orderSender
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
        $this->_code = self::METHOD_CODE;
        $this->orderFactory = $orderFactory;
        $this->client = $client;
        $this->response = $responseFactory->create();
        $this->paymentSession = $responseFactory->create();
        $this->adapter = $adapter;
        $this->orderSender = $orderSender;
    }

    /**
     * Return response.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Retrieving payment session
     *
     * @return Response
     */
    public function getPaymentSession()
    {
        return $this->paymentSession;
    }

    /**
     * Set initialization requirement state
     *
     * @param bool $isInitializeNeeded
     * @return void
     */
    public function setIsInitializeNeeded($isInitializeNeeded = true)
    {
        $this->_isInitializeNeeded = (bool)$isInitializeNeeded;
    }

    /**
     * Process
     *
     * @param string $merchantOrderId
     * @param string $transactionId
     * @param string $sessionId
     * @throws LocalizedException
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function process($merchantOrderId, $transactionId, $sessionId = null)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($merchantOrderId);
        $payment = $order->getPayment();
        if (!$payment || $payment->getMethod() != $this->getCode()) {
            throw new LocalizedException(
                __("This payment didn't work out because we can\'t find this order.")
            );
        }

        $this->getResponse()->setData($this->client->getTransaction($transactionId));

        $this->getPaymentSession()->setData(
            $this->client->getSessionInfo($sessionId ?? $this->getResponse()->getSessionId())
        );

        if ($order->getId()) {
            $this->processOrder($order);
        }
    }

    /**
     * Processing order
     *
     * @param Order $order
     * @throws \Exception
     */
    public function processOrder($order)
    {
        try {
            $this->checkPaymentSession();
            $this->checkTransaction($order);
        } catch (\Exception $e) {
            //decline the order (in case of wrong response code) but don't return money to customer.
            $message = $e->getMessage();
            $this->declineOrder($order, $message, false);
        }

        $payment = $order->getPayment();
        $this->fillPaymentByResponse($payment);
        $payment->getMethodInstance()->setIsInitializeNeeded(false);
        $payment->getMethodInstance()->setResponseData($this->getResponse()->getData());
        $payment->place();
        $this->addStatusComment($payment);
        $order->save();

        // TODO: Send order confirmation email based on user config
        $this->sendOrderEmail($order);
    }

    /**
     * Send order confirmation email
     *
     * @param Order $order
     * @throws \Exception
     */
    public function sendOrderEmail($order)
    {
        try {
            $this->orderSender->send($order);
            $order->addStatusHistoryComment(__("Notified customer about order #%1", $order->getIncrementId()))
                ->setIsCustomerNotified(1)
                ->save();
        } catch (\Exception $e) {
            $order->addStatusHistoryComment(__("Could not send order confirmation for order #%1", $order->getIncrementId()))
                ->setIsCustomerNotified(0)
                ->save();
        }
    }

    /**
     * Validating order
     *
     * @param Order $order
     * @throws \Exception
     */
    protected function checkTransaction($order)
    {
        if (!$order->canInvoice()) {
            throw new \Exception(__('Cannot invoice the transaction'));
        }

        if (!$this->getResponse()->getId() ||
            $order->getIncrementId() !== $this->getResponse()->getMerchantReference()) {
            throw new \Exception(__('Invalid transaction or merchant reference'));
        }
    }

    /**
     * Validating payment session
     *
     * @throws \Exception
     */
    protected function checkPaymentSession()
    {
        if ($this->getResponse()->getTransactionId() !== $this->getPaymentSession()->getTransactionId()) {
            throw new \Exception(__('Payment session validation failed!'));
        }
    }

    /**
     * Register order cancellation. Return money to customer if needed.
     *
     * @param Order $order
     * @param string $message
     * @param bool $voidPayment
     * @return void
     */
    public function declineOrder(Order $order, $message = '', $voidPayment = true)
    {
        try {
            $response = $this->getResponse();
            if ($voidPayment && $response->getId()) {
                $order->getPayment()
                    ->setTransactionId(null)
                    ->setParentTransactionId($response->getId())
                    ->void($response);
            }
            $order->registerCancellation($message)->save();
            $this->_eventManager->dispatch('order_cancel_after', ['order' => $order ]);
        } catch (\Exception $e) {
            //quiet decline
            $this->getPsrLogger()->critical($e);
        }
    }

    /**
     * Get psr logger.
     *
     * @return \Psr\Log\LoggerInterface
     */
    private function getPsrLogger()
    {
        if (null === $this->psrLogger) {
            $this->psrLogger = ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->psrLogger;
    }

    /**
     * Fill payment with credit card data from response from Dintero.
     *
     * @param \Magento\Framework\DataObject $payment
     * @return void
     */
    protected function fillPaymentByResponse(\Magento\Framework\DataObject $payment)
    {
        $response = $this->getResponse();
        $payment->setTransactionId($response->getId())
            ->setParentTransactionId(null)
            ->setIsTransactionClosed(0);
    }

    /**
     * Add status comment to history
     *
     * @param Payment $payment
     * @return $this
     */
    protected function addStatusComment(Payment $payment)
    {
        $transactionId = $this->getResponse()->getId();
        if ($payment->getIsTransactionPending()) {
            $message = 'Amount of %1 is pending approval on the gateway.<br/>'
                    . 'Transaction "%2" status is "%3".';

            $message = __(
                    $message,
                    $payment->getOrder()->getBaseCurrency()->formatTxt($this->getResponse()->getAmount()),
                    $transactionId,
                    $this->getResponse()->getStatus()
                );

            $payment->getOrder()->addStatusHistoryComment($message);
        }

        return $this;
    }

    /**
     * Capture amount
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod|Adapter
     */
    public function capture(InfoInterface $payment, $amount)
    {
        return $this->adapter->capture($payment, $amount);
    }

    /**
     * Authorize
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod|Adapter
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        return $this->adapter->authorize($payment, $amount);
    }

    /**
     * Cancel
     *
     * @param InfoInterface $payment
     * @return AbstractMethod|Adapter
     */
    public function cancel(InfoInterface $payment)
    {
        return $this->adapter->void($payment);
    }

    /**
     * Refund
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod|Adapter
     */
    public function refund(InfoInterface $payment, $amount)
    {
        return $this->adapter->refund($payment, $amount);
    }

    /**
     * Voiding payment
     *
     * @param InfoInterface $payment
     * @return AbstractMethod|mixed
     */
    public function void(InfoInterface $payment)
    {
        return $this->adapter->void($payment);
    }

    /**
     * Fetching transaction info
     *
     * @param InfoInterface $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return $this->adapter->fetchTransactionInfo($payment, $transactionId);
    }
}
