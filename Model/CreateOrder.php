<?php

namespace Dintero\Checkout\Model;

use Dintero\Checkout\Helper\Config;
use Dintero\Checkout\Model\Api\Client;
use Dintero\Checkout\Model\Payment\TransactionStatusResolver;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\Registry;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Api\OrderRepositoryInterface;

class CreateOrder
{

    private $apiClient;

    private $cartManagement;

    private $quoteResource;

    private $quoteFactory;

    private $addressMapperFactory;

    private $registry;

    private $invoiceRepository;

    private $customerRepository;

    private $configHelper;

    private $transactionBuilder;

    private $transactionStatusResolver;

    private $objectManager;

    private $invoiceManagement;

    protected $orderRepository;

    public function __construct(
        Client $apiClient,
        CartManagementInterface $cartManagement,
        Quote $quoteResource,
        QuoteFactory $quoteFactory,
        AddressMapperFactory $addressMapperFactory,
        Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        Config $config,
        Builder $transactionBuilder,
        TransactionStatusResolver $transactionStatusResolver,
        InvoiceManagementInterface $invoiceManagement,
        ObjectManagerInterface $objectManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->apiClient = $apiClient;
        $this->cartManagement = $cartManagement;
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
        $this->addressMapperFactory = $addressMapperFactory;
        $this->configHelper = $config;
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionStatusResolver = $transactionStatusResolver;
        $this->objectManager = $objectManager;
        $this->invoiceManagement = $invoiceManagement;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $transactionId
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function createFromTransaction($quote, $transactionId)
    {
        $transactionData = $this->apiClient->getTransaction($transactionId);
        if (isset($transactionData['error'])) {
            throw new \Exception(__('Transaction is invalid'));
        }

        if ($quote->getReservedOrderId() && $quote->getReservedOrderId() != $transactionData['merchant_reference']) {
            $quote = $this->quoteFactory->create();
            $this->quoteResource->load($quote, $transactionData['merchant_reference'], 'reserved_order_id');
        }

        if (!$quote->getId()) {
            throw new \Exception(__('Quote not found'));
        }

        $dinteroTransaction = new \Magento\Framework\DataObject($transactionData);

        // populating billing address with data from dintero
        $this->addressMapperFactory
            ->create(['address' => $quote->getBillingAddress(), 'dataObject' => $dinteroTransaction])
            ->map();

        if (!$quote->isVirtual() && $dinteroTransaction->getShippingAddress()) {
            // populating shipping address data from dintero
            $this->addressMapperFactory
                ->create(['address' => $quote->getShippingAddress(), 'dataObject' => $dinteroTransaction])
                ->map();
        }

        if ($quote->getShippingAddress()->getId() && !$quote->getShippingAddress()->getShippingMethod()) {
            $quote->getShippingAddress()->setShippingMethod($dinteroTransaction->getData('shipping_option/id'));
        }

        if (!$quote->getCustomerEmail()) {
            $quote->setCustomerEmail(
                !$quote->isVirtual() ? $quote->getBillingAddress()->getEmail() : $quote->getShippingAddress()->getEmail()
            );
        }

        $quote->getPayment()->setMethod(Dintero::METHOD_CODE);
        $this->updateCustomerInfo($quote);
        $quote->collectTotals();
        $this->quoteResource->save($quote);

        /** @var Order $order */
        $order = $this->cartManagement->submit($quote);

        $paymentObject = $order->getPayment();
        $paymentObject->setCcTransId($dinteroTransaction->getId())
            ->setLastTransId($dinteroTransaction->getId());
        $transaction = $this->transactionBuilder->setPayment($paymentObject)
            ->setOrder($order)
            ->setTransactionId($dinteroTransaction->getId())
            ->build($this->transactionStatusResolver->resolve($dinteroTransaction->getStatus()));

        $transaction->setIsClosed($dinteroTransaction->getStatus() == Client::STATUS_CAPTURED)->save();

        if ($order->canInvoice()) {
            /** @var Invoice $invoice */
            $invoice = $order->prepareInvoice()
                ->setTransactionId($transaction->getId())
                ->register()
                ->save();
            if ($invoice->canCapture() && $this->configHelper->isAutocaptureEnabled()) {
                $this->triggerCapture($invoice);
            }
        }
        return $this->orderRepository->get($order->getId());
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @throws LocalizedException
     */
    protected function updateCustomerInfo(\Magento\Quote\Model\Quote $quote)
    {
        try {
            $customer = $this->customerRepository->get($quote->getCustomerEmail());
            $quote->updateCustomerData($customer);
        } catch (NoSuchEntityException $e) {
            $quote->setCustomerIsGuest(true);
        }
    }

    /**
     * @param Invoice $invoice
     * @throws \Exception
     */
    private function triggerCapture(Invoice $invoice)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->invoiceRepository->get($invoice->getEntityId());
        $this->registry->register('current_invoice', $invoice);
        $this->invoiceManagement->setCapture($invoice->getEntityId());
        $invoice->getOrder()->setIsInProcess(true);
        $this->objectManager->create(Transaction::class)
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
    }
}
