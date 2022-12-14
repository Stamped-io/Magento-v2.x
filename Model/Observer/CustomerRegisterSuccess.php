<?php

namespace Stamped\Core\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CustomerRegisterSuccess implements ObserverInterface
{
    protected $customerRepositoryInterface;

	/**
	 * @var \Stamped\Core\Model\ConfigProvider $config
	 */
	protected $config;

    public function __construct(\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface, \Stamped\Core\Model\ConfigProvider $configProvider)
    {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->config = $configProvider;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
		// Created Stamped customer after customer account creation
        $customer = $observer->getEvent()->getCustomer();

		// Create token
		$message = $customer->getId() . $customer->getEmail();
		$token = hash_hmac('sha256', $message, $this->config->getPrivateKey());

		// Create request body
		$data = [
			'customerId' => $customer->getId(),
			'customerEmail' => $customer->getEmail(),
			'authToken' => $token,
			'customerFirstName' => $customer->getFirstname(),
			'customerLastName' => $customer->getLastName(),
		];

		$options = [
			'http' => [
				'method' => 'POST',
				'content' => json_encode($data),
				'header' => "Content-Type: application/json\r\n" .
					"Accept: application/json\r\n"
			]
		];

		$context = stream_context_create($options);
		$result = file_get_contents('https://stamped.io/api/v2/rewards/');

    }
}
