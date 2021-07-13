<?php

namespace Stamped\Core\Model\Adapter;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Stamped\Core\Model\ConfigProvider;

class Adapter
{
    /*
     * API Url Pattern
     */
    const STAMPED_SECURED_API_URL = 'https://stamped.io/api/v2/%s/%s';

    /**
     * @var ZendClientFactory $clientFactory
     */
    private $clientFactory;

    /**
     * @var ConfigProvider $configProvider
     */
    private $configProvider;

    /**
     * @var Json $json
     */
    private $json;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Adapter constructor.
     *
     * @param ZendClientFactory $zendClientFactory
     * @param ConfigProvider $configProvider
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        ZendClientFactory $zendClientFactory,
        ConfigProvider $configProvider,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->clientFactory = $zendClientFactory;
        $this->configProvider = $configProvider;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * @param int $storeId
     * @return \Magento\Framework\Http\ZendClient
     */
    private function initClient($storeId = null)
    {
        /** @var \Magento\Framework\Http\ZendClient $client */
        $client = $this->clientFactory->create();
        $client->setAuth($this->configProvider->getPublicKey($storeId), $this->configProvider->getPrivateKey($storeId))
            ->setConfig([
                'timeout' => 30,
                'verifypeer' => false,
            ])->setHeaders([
                'Content-Type: application/json',
            ]);
        return $client;
    }

    /**
     * @param string $endpoint
     * @return string
     */
    private function buildUrl($endpoint)
    {
        return sprintf(self::STAMPED_SECURED_API_URL, $this->configProvider->getHash(), $endpoint);
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    private function post($endpoint, $data = [], $storeId = null)
    {
        try {
            $response = $this->initClient($storeId)
                ->setUrlEncodeBody(false)
                ->setMethod(ZendClient::POST)
                ->setUri($this->buildUrl($endpoint))
                ->setRawData($this->json->serialize($data))
                ->request();

            if (in_array($response->getStatus(), [200, 201])) {
                return $this->json->unserialize($response->getBody());
            }

            if ($response->getStatus() == 401) {
                throw new LocalizedException(__(
                    'API Key or API Secret is invalid, please do check. If you need any assistance, please contact us.'
                ));
            }
            $this->logger->error($response->getBody());
            throw new LocalizedException(__('Something went wrong'));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return [];
    }

    /**
     * @param int $storeId
     * @param string $endpoint
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    private function get($endpoint, $storeId = null)
    {
        try {
            $response = $this->initClient($storeId)
                ->setUri($this->buildUrl($endpoint))
                ->request();

            if (in_array($response->getStatus(), [200, 201])) {
                return $this->json->unserialize($response->getBody());
            }

            if ($response->getStatus() == 401) {
                throw new LocalizedException(__(
                    'API Key or API Secret is invalid, please do check. If you need any assistance, please contact us.'
                ));
            }
            $this->logger->error($response->getBody());
            throw new LocalizedException(__('Something went wrong'));

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return [];
    }

    /**
     * @param integer $productId
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function getRichSnippet($productId)
    {
        return $this->get(sprintf('richsnippet?productId=%d', $productId));
    }

    /**
     * @param array $data
     * @param int|null $storeId
     * @return array|bool|float|int|mixed|string|null
     * @throws \Zend_Http_Client_Exception
     */
    public function createReviewRequest(array $data, $storeId = null)
    {
        return $this->post('survey/reviews', $data);
    }

    /**
     * @param array $data
     * @param int $storeId
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function createReviewRequestBulk(array $data, $storeId = null)
    {
        return $this->post('survey/reviews/bulk', $data, $storeId);
    }

    /**
     * @param array $data
     * @param int|null $storeId
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function createRewardRequest(array $data, $storeId = null)
    {
        return $this->post('orders', $data, $storeId);
    }
}
