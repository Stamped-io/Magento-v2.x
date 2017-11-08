<?php

namespace Stamped\Core\Block;

/**
 * Core content block
 */
class Core extends \Magento\Framework\View\Element\Template
{
    /**
     * Core collection
     *
     * @var Stamped\Core\Model\ResourceModel\Core\Collection
     */
    protected $_coreCollection = null;
    
    /**
     * Core factory
     *
     * @var \Stamped\Core\Model\CoreFactory
     */
    protected $_coreCollectionFactory;
    
    /** @var \Stamped\Core\Helper\Data */
    protected $_dataHelper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Stamped\Core\Model\ResourceModel\Core\CollectionFactory $coreCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Stamped\Core\Model\ResourceModel\Core\CollectionFactory $coreCollectionFactory,
        \Stamped\Core\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreCollectionFactory = $coreCollectionFactory;
        $this->_dataHelper = $dataHelper;
        $this->_registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Retrieve core collection
     *
     * @return Stamped\Core\Model\ResourceModel\Core\Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_coreCollectionFactory->create();
        return $collection;
    }
    
    /**
     * Retrieve prepared core collection
     *
     * @return Stamped\Core\Model\ResourceModel\Core\Collection
     */
    public function getCollection()
    {
        if (is_null($this->_coreCollection)) {
            $this->_coreCollection = $this->_getCollection();
            $this->_coreCollection->setCurPage($this->getCurrentPage());
            $this->_coreCollection->setPageSize($this->_dataHelper->getCorePerPage());
            $this->_coreCollection->setOrder('published_at','asc');
        }

        return $this->_coreCollection;
    }
    
    /**
     * Fetch the current page for the core list
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->getData('current_page') ? $this->getData('current_page') : 1;
    }
    
    /**
     * Return URL to item's view page
     *
     * @param Stamped\Core\Model\Core $coreItem
     * @return string
     */
    public function getItemUrl($coreItem)
    {
        return $this->getUrl('*/*/view', array('id' => $coreItem->getId()));
    }
    
    /**
     * Return URL for resized Core Item image
     *
     * @param Stamped\Core\Model\Core $item
     * @param integer $width
     * @return string|false
     */
    public function getImageUrl($item, $width)
    {
        return $this->_dataHelper->resize($item, $width);
    }
    
    /**
     * Get a pager
     *
     * @return string|null
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('core_list_pager');
        if ($pager instanceof \Magento\Framework\Object) {
            $corePerPage = $this->_dataHelper->getCorePerPage();

            $pager->setAvailableLimit([$corePerPage => $corePerPage]);
            $pager->setTotalNum($this->getCollection()->getSize());
            $pager->setCollection($this->getCollection());
            $pager->setShowPerPage(TRUE);
            $pager->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );

            return $pager->toHtml();
        }

        return NULL;
    }
    public function getWidgetShow()
    {
        return trim($this->_scopeConfig->getValue('core/stamped_settings/enable_widget', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }
    
    public function getApiKey()
    {
        return trim($this->_scopeConfig->getValue('core/stamped_settings/stamped_apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }
	
    public function getApiStoreUrl()
    {
        return trim($this->_scopeConfig->getValue('core/stamped_settings/stamped_storeurl', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }

    public function setProduct($product)
    {
    	$this->setData('product', $product);
    	$_product = $this->getProduct();
    	echo $_product->getName();
    }

    public function getProduct()
	{
        if (!$this->hasData('product'))
        {
            $this->setData('product', $this->_registry->registry('current_product'));
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $this->getData('product');
        $configurable_product_model = $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
        $parentIds= $configurable_product_model->getParentIdsByChild($product->getId());
            if (count($parentIds) > 0) {
                $product = $objectManager->get('Magento\Catalog\Model\Product')->load($parentIds[0]);
            }
        return $product;
    }

    public function getProductId()
    {
     	$_product = $this->getProduct();
     	$productId = $_product->getId();
    	return $productId;
    }

    public function getProductName()
    {
    	$_product = $this->getProduct();
    	$productName = $_product->getName();

    	return htmlspecialchars($productName);
    }

    public function getProductImageUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $image_url = $objectManager->get('Magento\Catalog\Model\Product\Media\Config')->getMediaUrl($this->getProduct()->getSmallImage());
        return $image_url;
    }

    public function getProductModel()
    {
    	$_product = $this->getProduct();
    	$productModel = $_product->getData('sku');
    	return htmlspecialchars($productModel);
    }

    public function getProductDescription()
    {
    	$_product = $this->getProduct();
    	$productDescription = strip_tags($_product->getShortDescription());
    	return $productDescription;
    }

    public function getProductUrl()
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');

        $productUrl = $urlInterface->getCurrentUrl();
    	return $productUrl;
    }
}
