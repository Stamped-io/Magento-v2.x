<?php

namespace Stamped\Core\Block;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Core content block
 */
class Core extends \Magento\Framework\View\Element\Template
{
    /**
     * Core collection
     *
     * @var \Stamped\Core\Model\ResourceModel\Core\Collection
     */
    protected $coreCollection = null;

    /**
     * Core factory
     *
     * @var \Stamped\Core\Model\CoreFactory
     */
    protected $coreCollectionFactory;

    /**
     * @var \Stamped\Core\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Stamped\Core\Model\ConfigProvider $config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    protected $customerSession;

    /**
     * Core constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Stamped\Core\Model\ResourceModel\Core\CollectionFactory $coreCollectionFactory
     * @param \Stamped\Core\Helper\Data $dataHelper
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Stamped\Core\Model\ResourceModel\Core\CollectionFactory $coreCollectionFactory,
        \Stamped\Core\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Stamped\Core\Model\ConfigProvider $configProvider,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        array $data = []
    ) {
        $this->coreCollectionFactory = $coreCollectionFactory;
        $this->dataHelper = $dataHelper;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->config = $configProvider;
        $this->catalogHelper = $catalogHelper;
        $this->productFactory = $productFactory;
        $this->productResource = $productResource;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve core collection
     *
     * @return \Stamped\Core\Model\ResourceModel\Core\Collection
     */
    protected function _getCollection()
    {
        return $this->coreCollectionFactory->create();
    }

    /**
     * Retrieve prepared core collection
     *
     * @return \Stamped\Core\Model\ResourceModel\Core\Collection
     */
    public function getCollection()
    {
        if ($this->coreCollection === null) {
            $this->coreCollection = $this->_getCollection()
                ->setCurPage($this->getCurrentPage())
                ->setPageSize($this->config->getItemsPerPage())
                ->setOrder('published_at', 'asc');
        }

        return $this->coreCollection;
    }

    /**
     * Fetch the current page for the core list
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->getData('current_page') ?? 1;
    }

    /**
     * Return URL to item's view page
     *
     * @param \Stamped\Core\Model\Core $coreItem
     * @return string
     */
    public function getItemUrl($coreItem)
    {
        return $this->getUrl('*/*/view', ['id' => $coreItem->getId()]);
    }

    /**
     * Return URL for resized Core Item image
     *
     * @param \Stamped\Core\Model\Core $item
     * @param integer $width
     * @return string|false
     */
    public function getImageUrl($item, $width)
    {
        return $this->dataHelper->resize($item, $width);
    }

    /**
     * Get a pager
     *
     * @return string|null
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('core_list_pager');
        if ($pager instanceof \Magento\Framework\DataObject) {
            $corePerPage = $this->config->getItemsPerPage();

            $pager->setAvailableLimit([$corePerPage => $corePerPage]);
            $pager->setTotalNum($this->getCollection()->getSize());
            $pager->setCollection($this->getCollection());
            $pager->setShowPerPage(true);
            $pager->setFrameLength($this->config->getPaginationFrame());
            $pager->setJump($this->config->getPaginationFrameSkip());
            return $pager->toHtml();
        }

        return null;
    }

    /**
     * @return string
     */
    public function getRewardsInit()
    {
        $htmlLauncher = "<div id='stamped-rewards-init' class='stamped-rewards-init' data-key-public='%s' %s></div>";
        $htmlLoggedInAttributes = "";

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            $message = $customer->getId() . $customer->getEmail();

            // to lowercase hexits
            $hmacVal = hash_hmac('sha256', $message, $this->config->getPrivateKey());

            $htmlLoggedInAttributesVal = "data-key-auth='%s' data-customer-id='%d' data-customer-email='%s'"
                ." data-customer-first-name='%s' data-customer-last-name='%s' data-customer-orders-count='%d' "
                ." data-customer-tags='%s' data-customer-total-spent='%d'";
            $htmlLoggedInAttributes = sprintf(
                $htmlLoggedInAttributesVal,
                $hmacVal,
                $customer->getId(),
                $customer->getEmail(),
                $customer->getFirstname(),
                $customer->getLastname(),
                "",
                "",
                ""
            );
        }

        return sprintf($htmlLauncher, $this->config->getPublicKey(), $htmlLoggedInAttributes);
    }

    /**
     * @return array|Product|mixed|null
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->catalogHelper->getProduct());
        }

        $objectManager = ObjectManager::getInstance();
        $product = $this->getData('product');
        $configurable_product_model = $objectManager->get(Configurable::class);
        $parentIds = $configurable_product_model->getParentIdsByChild($product->getId());
        if (count($parentIds) > 0) {
            $product = $this->productFactory->create();
            $this->productResource->load($product, $parentIds[0]);
        }
        return $product;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->getProduct()->getName();
    }

    /**
     * @return string
     */
    public function getProductImageUrl()
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class)
            ->getMediaUrl($this->getProduct()->getSmallImage());
    }

    /**
     * Retrieving Product Descritpion
     *
     * @return string
     */
    public function getProductDescription()
    {
        return strip_tags($this->getProduct()->getShortDescription() ?? "");
    }

    /**
     * Retrieving SKU
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * Retrieving product url
     *
     * @return string
     */
    public function getProductUrl()
    {
        return ObjectManager::getInstance()->get(UrlInterface::class)->getCurrentUrl();
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->config->getPublicKey();
    }

    /**
     * @return string
     */
    public function getApiStoreUrl()
    {
        return $this->config->getStoreUrl();
    }

    /**
     *@return string
     */

    public function getStoreHash() {
        return $this->config->getHash();
    }

    /**
     * @return bool
     */
    public function getLauncherShow()
    {
        return $this->config->isLauncherEnabled();
    }

    /**
     * @return bool
     */
    public function getWidgetShow()
    {
        return $this->config->isWidgetEnabled();
    }
}
