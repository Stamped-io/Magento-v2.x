<?php

namespace Stamped\Core\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Image\Factory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Stamped\Core\Helper\Data;
use Stamped\Core\Model\Adapter\Adapter;
use Stamped\Core\Model\ConfigProvider;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var Factory $imageFactory
     */
    protected $imageFactory;

    /**
     * @var Image $imgHelper
     */
    protected $imgHelper;

    /**
     * @var Emulation $appEmulation
     */
    protected $appEmulation;

    /**
     * @var ConfigProvider $configProvider
     */
    protected $configProvider;

    /**
     * @var Adapter $adapter
     */
    protected $adapter;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var Data $stampedHelper
     */
    protected $stampedHelper;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param PageFactory $resultPageFactory
     * @param Factory $imageFactory
     * @param Image $imgHelper
     * @param Emulation $appEmulation
     * @param ConfigProvider $configProvider
     * @param Adapter $adapter
     * @param CollectionFactory $collectionFactory
     * @param Data $stampedHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory,
        Factory $imageFactory,
        Image $imgHelper,
        Emulation $appEmulation,
        ConfigProvider $configProvider,
        Adapter $adapter,
        CollectionFactory $collectionFactory,
        Data $stampedHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->imageFactory = $imageFactory;
        $this->imgHelper = $imgHelper;
        $this->appEmulation = $appEmulation;
        $this->configProvider = $configProvider;
        $this->adapter = $adapter;
        $this->orderCollectionFactory = $collectionFactory;
        $this->stampedHelper = $stampedHelper;
        $this->logger = $logger;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Stamped_Core::core_manage');
    }

    /**
     * Core List action
     *
     * @return void
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $page = 0;
            $now = time();
            $last = $now - (60 * 60 * 24 * 180); // 180 days ago
            $from = date("Y-m-d", $last);
            $storeId = $this->getRequest()->getParam('store', $this->storeManager->getDefaultStoreView()->getId());
            $currentStore = $this->storeManager->getStore($storeId);

            if (!isset($currentStore) ||
                (!$this->configProvider->getPublicKey($currentStore->getId())
                    || !$this->configProvider->getPrivateKey($currentStore->getId()))
            ) {
                throw new LocalizedException(
                    __('Please ensure you have configured the API Public Key and Private Key in Settings.')
                );
            }

            $orderStatuses = array_map(
                'strtolower',
                $this->configProvider->getOrderStatuses($currentStore->getId())
            );

            if (empty($orderStatuses)) {
                $orderStatuses = ['complete'];
            }

            $salesCollection = $this->orderCollectionFactory->create()
                ->addFieldToFilter('status', $orderStatuses)
                ->addAttributeToFilter('created_at', ['gteq' => $from])
                ->addAttributeToSort('created_at', 'DESC')->setPageSize(20)
                ->addFieldToFilter('store_id', $currentStore->getId());

            $pages = $salesCollection->getLastPageNumber();

            do {
                try {
                    $page++;
                    $salesCollection->setCurPage($page)->load();
                    $orders = [];
                    /** @var \Magento\Sales\Model\Order $order */
                    foreach ($salesCollection as $order) {
                        $orders[] = $this->stampedHelper->prepareData($order);
                    }

                    if (count($orders) > 0) {
                        $this->adapter->createReviewRequestBulk($orders, $currentStore);
                    }

                } catch (\Exception $e) {
                    $this->logger->warning($e->getMessage());
                    return $result->setData(['status' => 'error', 'code' => 3]);
                }

                $salesCollection->clear();

            } while ($page <= (3000 / 200) && $page < $pages);
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
            return $result->setData(['status' => 'error', 'code' => 2]);
        }

        return $result->setData(['status' => 'ok', 'code' => '1']);
    }
}
