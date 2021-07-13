<?php

namespace Dintero\Checkout\Gateway\Command;

use Dintero\Checkout\Model\Api\Client;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Class VoidCommand
 *
 * @package Dintero\Checkout\Gateway\Command
 */
class FetchTransactionCommand implements CommandInterface
{
    /**
     * API client for dintero
     *
     * @var Client $api
     */
    private $api;

    /**
     * Capture constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->api = $client;
    }

    /**
     * Executing command
     *
     * @param array $commandSubject
     * @return $this|\Magento\Payment\Gateway\Command\ResultInterface|null
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $commandSubject['payment']->getPayment();
        $result = $this->api->getTransaction($commandSubject['transaction_id']);
        if (isset($result['error'])) {
            throw new \Exception(__('Failed to void the transaction'));
        }

        $payment->setTransactionId($commandSubject['transaction_id']);
        return $result;
    }
}
