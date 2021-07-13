<?php

namespace Dintero\Checkout\Gateway\Command;

use Dintero\Checkout\Model\Api\Client;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Class RefundCommand
 *
 * @package Dintero\Checkout\Gateway\Command
 */
class RefundCommand implements CommandInterface
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
    public function __construct(Client $client) {
        $this->api = $client;
    }

    /**
     * Refunding
     *
     * @param array $commandSubject
     * @return $this|\Magento\Payment\Gateway\Command\ResultInterface|null
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\\Payment\\Gateway\\Data\\PaymentDataObject $payment */
        $payment = $commandSubject['payment']->getPayment();
        $payment->setSalesDocument($payment->getCreditMemo());
        $result = $this->api->refund($payment, $commandSubject['amount']);
        if (isset($result['error'])) {
            throw new \Exception(__("Couldn't refund the transaction"));
        }
        return $this;
    }
}
