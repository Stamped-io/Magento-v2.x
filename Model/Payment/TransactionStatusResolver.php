<?php

namespace Dintero\Checkout\Model\Payment;

use Dintero\Checkout\Model\Api\Client;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Class TransactionStatusResolver
 *
 * @package Dintero\Checkout\Model\Payment
 */
class TransactionStatusResolver
{
    /**
     * @var array
     */
    protected $transactionStatusMap = [
        Client::STATUS_AUTHORIZED => TransactionInterface::TYPE_AUTH,
        Client::STATUS_CAPTURED => TransactionInterface::TYPE_CAPTURE,
        Client::STATUS_PARTIALLY_CAPTURED => TransactionInterface::TYPE_CAPTURE,
    ];

    /**
     * @param string $dinteroTransactionStatus
     * @return string
     * @throws \Exception
     */
    public function resolve($dinteroTransactionStatus)
    {
        if (!isset($this->transactionStatusMap[$dinteroTransactionStatus])) {
            throw new \Exception(__('Unable to resolve transaction status'));
        }
        return $this->transactionStatusMap[$dinteroTransactionStatus];
    }
}
