<?php

namespace Dintero\Checkout\Model;

use Dintero\Checkout\Api\SessionManagementInterface;
use Dintero\Checkout\Model\Api\Client;
use Dintero\Checkout\Model\Api\ClientFactory;

/**
 * Class Session
 *
 * @package Dintero\Checkout\Model
 */
class SessionManagement implements SessionManagementInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var
     */
    protected $sessionFactory;

    /**
     * Session constructor.
     * @param ClientFactory $clientFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        ClientFactory $clientFactory,
        \Dintero\Checkout\Api\Data\SessionInterfaceFactory $sessionFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->client = $clientFactory->create()->setType(Client::TYPE_EMBEDDED);
        $this->sessionFactory = $sessionFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function getSession()
    {
        $quote = $this->checkoutSession->getQuote();
        $response = $this->client
            ->setType(Client::TYPE_EMBEDDED)
            ->initSessionFromQuote($quote);
        $quote->getPayment()->setAdditionalInformation($response)->save();
        return $this->sessionFactory->create()->setId($response['id'] ?? null);
    }
}
