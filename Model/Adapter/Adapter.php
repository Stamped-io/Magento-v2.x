<?php

namespace Stamped\Core\Model\Adapter;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Http\LaminasClient;
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
     * @param ConfigProvider $configProvider
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigProvider $configProvider,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * @param int $storeId
     * @return LaminasClient
     */
    private function initClient($storeId = null)
    {
        /** @var LaminasClient $client */
        $client = new Client();
        $client->setAuth($this->configProvider->getPublicKey($storeId), $this->configProvider->getPrivateKey($storeId))
            ->setOptions([
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
     */
    private function post($endpoint, $data = [], $storeId = null)
    {
        try {
            $response = $this->initClient($storeId)
                ->setUri($this->buildUrl($endpoint))
                ->setMethod(Request::METHOD_POST)
                ->setRawBody($this->json->serialize($data))
                ->send();

            if ($response->isSuccess()) {
                return $this->json->unserialize($response->getBody());
            }

            if ($response->getStatusCode() == 401) {
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
     */
    private function get($endpoint, $storeId = null)
    {
        try {
            $response = $this->initClient($storeId)
                ->setUri($this->buildUrl($endpoint))
                ->send();

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
     */
    public function getRichSnippet($productId)
    {
        return $this->get(sprintf('richsnippet?productId=%d', $productId));
    }

    /**
     * @param array $data
     * @param int|null $storeId
     * @return array|bool|float|int|mixed|string|null
     */
    public function createReviewRequest(array $data, $storeId = null)
    {
        return $this->post('survey/reviews', $data);
    }

    /**
     * @param array $data
     * @param int $storeId
     * @return array
     */
    public function createReviewRequestBulk(array $data, $storeId = null)
    {
        return $this->post('survey/reviews/bulk', $data, $storeId);
    }

    /**
     * @param array $data
     * @param int|null $storeId
     * @return array
     */
    public function createRewardRequest(array $data, $storeId = null)
    {
        return $this->post('orders', $data, $storeId);
    }
}
