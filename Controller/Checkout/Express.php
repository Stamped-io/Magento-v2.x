<?php

namespace Dintero\Checkout\Controller\Checkout;

use Dintero\Checkout\Helper\Config;
use Dintero\Checkout\Model\Api\Client;
use Dintero\Checkout\Model\Dintero;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\ResourceModel\Quote;

/**
 * Class Express
 *
 * @package Dintero\Checkout\Controller\Checkout
 */
class Express extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var Config $configHelper
     */
    protected $configHelper;

    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var Quote $quoteResource
     */
    protected $quoteResource;

    /**
     * @var SerializerInterface $serializer
     */
    protected $serializer;

    /**
     * Express constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $configHelper
     * @param Client $client
     * @param Quote $quoteResource
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $configHelper,
        Client $client,
        Quote $quoteResource,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
        $this->client = $client;
        $this->quoteResource = $quoteResource;
        $this->serializer = $serializer;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function execute()
    {
        if (!$this->configHelper->isActive() || !$this->configHelper->isExpress()) {
            $this->messageManager->addErrorMessage(__('Dintero Express Checkout is disabled'));
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        $this->client->setType(\Dintero\Checkout\Model\Api\Client::TYPE_EXPRESS);
        $response = $this->client->initSessionFromQuote($this->checkoutSession->getQuote());

        if (!$response['url']) {
            $this->messageManager->addErrorMessage(__('Something went wrong'));
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        return $this->resultRedirectFactory->create()->setUrl($response['url']);
    }
}
