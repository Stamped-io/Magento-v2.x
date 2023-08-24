<?php
/**
 * Core data helper
 */

namespace Stamped\Core\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Size;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Image\Factory;
use Magento\GroupedProduct\Model\Product\Type\Grouped as ProductTypeGrouped;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Stamped\Core\Model\Adapter\Adapter;
use Stamped\Core\Model\ConfigProvider;
use Stamped\Core\Model\Core;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Media path to extension images
     *
     * @var string
     */
    const MEDIA_PATH = 'Core';

    /**
     * Maximum size for image in bytes
     * Default value is 1M
     *
     * @var int
     */
    const MAX_FILE_SIZE = 1048576;

    /**
     * Minimum image height in pixels
     *
     * @var int
     */
    const MIN_HEIGHT = 50;

    /**
     * Maximum image height in pixels
     *
     * @var int
     */
    const MAX_HEIGHT = 800;

    /**
     * Manimum image width in pixels
     *
     * @var int
     */
    const MIN_WIDTH = 50;

    /**
     * Maximum image width in pixels
     *
     * @var int
     */
    const MAX_WIDTH = 1024;

    /**
     * Array of image size limitation
     *
     * @var array $_imageSize
     */
    protected $_imageSize = [
        'minheight' => self::MIN_HEIGHT,
        'minwidth' => self::MIN_WIDTH,
        'maxheight' => self::MAX_HEIGHT,
        'maxwidth' => self::MAX_WIDTH,
    ];

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var FileTransferFactory
     */
    protected $httpFactory;

    /**
     * @var UploaderFactory $_fileUploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var File $_ioFile
     */
    protected $ioFile;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface $productRepository
     */
    protected $productRepository;

    /**
     * @var Escaper $escaper
     */
    protected $escaper;

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
     * @var AddressFactory $addressFactory
     */
    protected $addressFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Filesystem $filesystem
     * @param Size $fileSize
     * @param FileTransferFactory $httpFactory
     * @param ProductRepositoryInterface $productRepository
     * @param UploaderFactory $fileUploaderFactory
     * @param File $ioFile
     * @param StoreManagerInterface $storeManager
     * @param Factory $imageFactory
     * @param Image $imgHelper
     * @param Escaper $escaper
     * @param Emulation $appEmulation
     * @param ConfigProvider $configProvider
     * @param Adapter $adapter
     * @param AddressFactory $addressFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        Size $fileSize,
        FileTransferFactory $httpFactory,
        ProductRepositoryInterface $productRepository,
        UploaderFactory $fileUploaderFactory,
        File $ioFile,
        StoreManagerInterface $storeManager,
        Factory $imageFactory,
        Image $imgHelper,
        Escaper $escaper,
        Emulation $appEmulation,
        ConfigProvider $configProvider,
        Adapter $adapter,
        AddressFactory $addressFactory
    ) {
        $this->configProvider = $configProvider;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->httpFactory = $httpFactory;
        $this->productRepository = $productRepository;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->ioFile = $ioFile;
        $this->storeManager = $storeManager;
        $this->imageFactory = $imageFactory;
        $this->imgHelper = $imgHelper;
        $this->escaper = $escaper;
        $this->appEmulation = $appEmulation;
        $this->adapter = $adapter;
        $this->addressFactory = $addressFactory;
        parent::__construct($context);
    }

    /**
     * Remove Core item image by image filename
     *
     * @param string $imageFile
     * @return bool
     */
    public function removeImage($imageFile)
    {
        $this->ioFile->open(['path' => $this->getBaseDir()]);

        if ($this->ioFile->fileExists($imageFile)) {
            return $this->ioFile->rm($imageFile);
        }

        return false;
    }

    /**
     * @param Core $item
     * @param $width
     * @param null $height
     * @return false|string
     * @throws \Exception
     */
    public function resize(Core $item, $width, $height = null)
    {
        if (!$item->getImage() || $width < self::MIN_WIDTH || $width > self::MAX_WIDTH) {
            return false;
        }

        $width = (int) $width;
        if ($height !== null) {
            if ($height < self::MIN_HEIGHT || $height > self::MAX_HEIGHT) {
                return false;
            }
            $height = (int) $height;
        }

        $imageFile = $item->getImage();
        $cacheDir = $this->getBaseDir() . '/' . 'cache' . '/' . $width;
        $cacheUrl = $this->getBaseUrl() . '/' . 'cache' . '/' . $width . '/';
        $this->ioFile->checkAndCreateFolder($cacheDir);
        $this->ioFile->open(['path' => $cacheDir]);

        if ($this->ioFile->fileExists($imageFile)) {
            return $cacheUrl . $imageFile;
        }

        try {
            $image = $this->imageFactory->create($this->getBaseDir() . '/' . $imageFile);
            $image->resize($width, $height);
            $image->save($cacheDir . '/' . $imageFile);
            return $cacheUrl . $imageFile;
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return false;
        }
    }

    /**
     * @param $scope
     * @return false
     */
    public function uploadImage($scope)
    {
        $adapter = $this->httpFactory->create();
        $adapter->addValidator(new \Laminas\Validator\File\ImageSize($this->_imageSize));
        $adapter->addValidator(new \Laminas\Validator\File\FilesSize(['max' => self::MAX_FILE_SIZE]));
        if (!$adapter->isUploaded($scope)) {
            return false;
        }

        // validate image
        if (!$adapter->isValid($scope)) {
            throw new LocalizedException(__('Uploaded image is not valid.'));
        }

        $uploader = $this->fileUploaderFactory->create(['fileId' => $scope]);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png'])
            ->setAllowRenameFiles(true)
            ->setFilesDispersion(false)
            ->setAllowCreateFolders(true);

        if ($uploader->save($this->getBaseDir())) {
            return $uploader->getUploadedFileName();
        }
        return false;
    }

    /**
     * Return the base media directory for Core Item images
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath(self::MEDIA_PATH);
    }

    /**
     * Return the Base URL for Core Item images
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . '/' . self::MEDIA_PATH;
    }

    /**
     * @param $order
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderProductsData($order)
    {
        $this->storeManager->setCurrentStore($order->getStoreId());
        $products = $order->getAllVisibleItems(); //filter out simple products
        $productList = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($products as $item) {

            $fullProduct = $this->productRepository->get($item->getSku());

            $parentId = $item->getProduct()->getId();

            if ($item->getParentItem()) {
                $parentId = $item->getParentItem()->getProductId();
            }

            if ($item->getProductType() === ProductTypeGrouped::TYPE_CODE) {
                $productOptions = $item->getProductOptions();
                $productId = (isset($productOptions['super_product_config'])
                    && isset($productOptions['super_product_config']['product_id']))
                    ? $productOptions['super_product_config']['product_id'] : null;
                if ($productId) {
                    if (isset($groupProductsParents[$productId])) {
                        $parentId = $groupProductsParents[$productId];
                    } else {
                        $parentId = $groupProductsParents[$productId] = $productId;
                    }
                }
            }

            if (!empty($parentId)) {
                $fullProduct = $this->productRepository->getById($parentId);
            }

            try {

                $this->appEmulation->startEnvironmentEmulation($order->getStoredId(), Area::AREA_FRONTEND, true);
                $rawDescription = str_replace(['\'', '"'], '', $fullProduct->getDescription());
                $productData = [
                    'productId' => $fullProduct->getId(),
                    'productTitle' => $fullProduct->getName(),
                    'productUrl' => $fullProduct->getProductUrl(['_store' => $order->getStoreId()]),
                    'productImageUrl' => $this->imgHelper->init($item->getProduct(), 'product_base_image')->getUrl(),
                    'productPrice' => $item->getPrice(),
                    'productDescription' => $this->escaper->escapeHtml(strip_tags($rawDescription)),
                ];

                $this->appEmulation->stopEnvironmentEmulation();

                if ($fullProduct->getUpc()) {
                    $productData['productBarcode'] = $fullProduct->getUpc();
                }

                if ($fullProduct->getBrand()) {
                    $productData['productBrand'] = $fullProduct->getBrand();
                }

                if ($fullProduct->getSku()) {
                    $productData['productSKU'] = $fullProduct->getSku();
                }
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
            }

            $productList[] = $productData;
        }

        return $productList;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareData($order)
    {
        $shippingId = $order->getShippingAddress()->getId();
        $address = $this->addressFactory->create()->load($shippingId);
        $data = [
            'customerId' => $order->getCustomerId(),
            'email' => $order->getCustomerEmail(),
            'firstName' => $order->getCustomerFirstname() ?? $order->getBillingAddress()->getFirstname(),
            'lastName' => $order->getCustomerLastname() ?? $order->getBillingAddress()->getLastname(),
            'location' => $address->getCountry(),
            'orderNumber' => $order->getIncrementId(),
            'orderId' => $order->getIncrementId(),
            'orderCurrencyISO' => $order->getOrderCurrency()->getCode(),
            'orderTotalPrice' => $order->getGrandTotal(),
            'orderTotal' => $order->getGrandTotal(),
            'orderStatus' => strtolower($order->getStatus()),
            'orderSource' => 'magento',
            'platform' => 'magento',
            'orderDate' => $order->getCreatedAt() ?? date('Y-m-d H:m:s'),
            'itemsList' => $this->getOrderProductsData($order)
        ];

        if (!$order->getCustomerIsGuest()) {
            $data['userReference'] = $order->getCustomerEmail();
        }

        return $data;
    }

    /**
     * @param $order
     * @return $this|false|void
     */
    public function saveOrderAfter($order)
    {
        try {
            $orderStatuses = array_map('strtolower', $this->configProvider->getOrderStatuses());

            if (empty($orderStatuses)) {
                $orderStatuses = ['complete'];
            }

            $orderStatusesRewards = array_map('strtolower', $this->configProvider->getRewardsTriggerStatus());
            if (empty($orderStatusesRewards)) {
                $orderStatusesRewards = ['processing'];
            }

            if (!$this->configProvider->getPublicKey($order->getStoreId())
                || !$this->configProvider->getPrivateKey($order->getStoreId())) {
                return $this;
            }

            $isProceedReview = in_array($order->getStatus(), $orderStatuses);
            $isProceedReward = in_array($order->getStatus(), $orderStatusesRewards);

            if (!$isProceedReview && !$isProceedReward) {
                return $this;
            }

            // Get the id of the orders shipping address
            if (!is_object($order->getShippingAddress())) {
                return false;
            }

            $data = $this->prepareData($order);

            if ($isProceedReview) {
                $this->adapter->createReviewRequest($data, $order->getStoreId());
            }

            if ($isProceedReward) {
                $this->adapter->createRewardRequest($data, $order->getStoreId());
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return;
        }

        return $this;
    }
}
