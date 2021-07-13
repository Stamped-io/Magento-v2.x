<?php

namespace Dintero\Checkout\Controller\Payment;

use Dintero\Checkout\Model\CreateOrder;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
use Dintero\Checkout\Model\DinteroFactory;

/**
 * Class Success
 *
 * @package Dintero\Checkout\Controller\Payment
 */
class Success extends Action
{
    /**
     * Order factory
     *
     * @var OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteFactory $quoteFactory
     */
    protected $quoteFactory;

    /**
     * @var CreateOrder $createOrder
     */
    protected $createOrder;

    /**
     * @var DinteroFactory $paymentMethodFactory
     */
    private $paymentMethodFactory;


    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        QuoteFactory $quoteFactory,
        CreateOrder $createOrder,
        DinteroFactory $paymentMethodFactory
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->createOrder = $createOrder;
        $this->paymentMethodFactory = $paymentMethodFactory;
    }

    /**
     * Processing response
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();
        $order = $this->orderFactory->create()
            ->loadByIncrementId($this->getRequest()->getParam('merchant_reference'));

        $transactionId = $this->getRequest()->getParam('transaction_id');

        // processing standard checkout
        if ($transactionId && $order->getId()) {
            $this->paymentMethodFactory->create()->process($order->getIncrementId(), $transactionId);
        }

        // processing express and embedded checkout
        if ($transactionId && !$order->getId()) {
            $order = $this->createOrder->createFromTransaction($this->checkoutSession->getQuote(), $transactionId);
        }

        if ($order->getId()) {
            $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId())
                ->setLastQuoteId($order->getQuoteId())
                ->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());
        }

        if ($order->getId() && $transactionId) {
            return $result->setPath('checkout/onepage/success');
        }

        if ($order->getId() && $order->canCancel()) {
            $order->getPayment()
                ->setTransactionId(null)
                ->cancel();

            $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
            $quote->setIsActive(true)->setReservedOrderId(null)->save();
            $this->checkoutSession->replaceQuote($quote);

            $order->registerCancellation('Payment Failed')->save();
            $this->_eventManager->dispatch('order_cancel_after', ['order' => $order ]);
        }
        $this->messageManager->addErrorMessage(__('Payment failed'));
        return $result->setPath('checkout/cart');
    }
}
