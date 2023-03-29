<?php

namespace Stamped\Core\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    /*
     * Path to store config where count of core posts per page is stored
     */
    const XML_PATH_ITEMS_PER_PAGE = 'stamped_core/view/items_per_page';

    /*
     * Api Key
     */
    const STAMPED_API_KEY_CONFIGURATION = 'stamped_core/stamped_settings/stamped_apikey';

    /*
     * Api Secret
     */
    const STAMPED_API_SECRET_CONFIGURATION = 'stamped_core/stamped_settings/stamped_apisecret';

    /*
     * Store URL
     */
    const STAMPED_STORE_URL_CONFIGURATION = 'stamped_core/stamped_settings/stamped_storeurl';

    /*
     * Store Hash
     */
    const STAMPED_STORE_HASH_CONFIGURATION = 'stamped_core/stamped_settings/stamped_storehash';

    /*
     * Order status triggering rewards
     */
    const STAMPED_CORE_REWARDS_TRIGGER_STATUS = 'stamped_core/stamped_settings_rewards/order_status_trigger_rewards';

    /*
     * Order statuses
     */
    const STAMPED_CORE_ORDER_STATUS = 'stamped_core/stamped_settings/order_status_trigger';

    /*
     * Widget status
     */
    const XPATH_WIDGET_STATUS = 'stamped_core/stamped_settings/enable_widget';

    /*
     * Launcher status
     */
    const XPATH_LAUNCHER_STATUS = 'stamped_core/stamped_settings_rewards/enable_launcher';

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param null|integer $storeId
     * @return mixed
     */
    public function getPublicKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::STAMPED_API_KEY_CONFIGURATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPrivateKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::STAMPED_API_SECRET_CONFIGURATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHash($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::STAMPED_STORE_HASH_CONFIGURATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getStoreUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::STAMPED_STORE_URL_CONFIGURATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getItemsPerPage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ITEMS_PER_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getRewardsTriggerStatus($storeId = null)
    {
        return array_filter(explode(
            ',',
            (string) $this->scopeConfig->getValue(
                self::STAMPED_CORE_REWARDS_TRIGGER_STATUS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) ?: ''
        ));
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getOrderStatuses($storeId = null)
    {
        return array_filter(explode(
            ',',
            (string) $this->scopeConfig->getValue(
                self::STAMPED_CORE_ORDER_STATUS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) ?: ''
        ));
    }

    /**
     * @return bool
     */
    public function isWidgetEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_WIDGET_STATUS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isLauncherEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_LAUNCHER_STATUS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPaginationFrame()
    {
        return $this->scopeConfig->getValue('design/pagination/pagination_frame', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPaginationFrameSkip()
    {
        return $this->scopeConfig->getValue('design/pagination/pagination_frame_skip', ScopeInterface::SCOPE_STORE);
    }
}
