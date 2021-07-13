<?php

namespace Dintero\Checkout\Controller\Payment;

use Dintero\Checkout\Helper\Config;
use Dintero\Checkout\Model\Api\Client;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class SessionController
 *
 * @package Dintero\Checkout\Controller
 */
class Place extends Action
{
    /**
     * Client
     *
     * @var Client $client
     */
    protected $client;

    /**
     * Onepage checkout
     *
     * @var Onepage $onepageCheckout
     */
    protected $onepageCheckout;

    /**
     * Cart management
     *
     * @var CartManagementInterface $cartManagement
     */
    protected $cartManagement;

    /**
     * Order repository
     *
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * Result builder
     *
     * @var JsonFactory $resultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Logger
     *
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var Config $configHelper
     */
    protected $configHelper;

    /**
     * SessionController constructor.
     *
     * @param Context $context
     * @param Client $client
     * @param Onepage $onepageCheckout
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param Config $configHelper
     */
    public function __construct(
        Context $context,
        Client $client,
        Onepage $onepageCheckout,
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Config $configHelper
    ) {
        parent::__construct($context);
        $this->client = $client;
        $this->onepageCheckout = $onepageCheckout;
        $this->cartManagement = $cartManagement;
        $this->orderRepository = $orderRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->configHelper = $configHelper;
    }

    /**
     * Controller action which has to be returning dintero checkout url
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = new DataObject();
        try {
            $this->onepageCheckout->getCheckoutMethod();
            $orderId = $this->cartManagement->placeOrder($this->_getCheckout()->getQuote()->getId());
            $result->setData('success', true);

            $this->_eventManager->dispatch('checkout_dintero_checkout_placeOrder', [
                'result' => $result,
                'action' => $this
            ]);

            $order = $this->orderRepository->get($orderId);
            $data = $this->client->initCheckout($order);

            if (!isset($data['url'])) {
                throw new \Exception('Something went wrong');
            }

            $data['url'] = $this->configHelper->resolveCheckoutUrl($data['url']);
            $data = array_merge(['success' => true], $data);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $data = ['success' => false, 'error' => __('Something went wrong')];
        }

        return $this->resultJsonFactory->create()->setData($data);
    }

    /**
     * Get checkout model
     *
     * @return CheckoutSession
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get(CheckoutSession::class);
    }
}
