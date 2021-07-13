<?php

namespace Dintero\Checkout\Gateway\Command;

use Dintero\Checkout\Model\Api\Client;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Class CaptureCommand
 *
 * @package Dintero\Checkout\Gateway\Command
 */
class AuthorizeCommand implements CommandInterface
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
     * @throws \Exception
     */
    public function execute(array $commandSubject)
    {
        return null;
    }
}
