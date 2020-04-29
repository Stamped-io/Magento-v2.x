<?php

/**
 * Core data helper
 */
namespace Stamped\Core\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Path to store config where count of core posts per page is stored
     *
     * @var string
     */
    const XML_PATH_ITEMS_PER_PAGE     = 'stamped_core/view/items_per_page';
    const STAMPED_API_KEY_CONFIGURATION = 'stamped_core/stamped_settings/stamped_apikey';
	const STAMPED_API_SECRET_CONFIGURATION = 'stamped_core/stamped_settings/stamped_apisecret';
	const STAMPED_STORE_URL_CONFIGURATION = 'stamped_core/stamped_settings/stamped_storeurl';

	const STAMPED_SECURED_API_URL = "https://%s:%s@stamped.io/api/%s";
    
    /**
     * Media path to extension images
     *
     * @var string
     */
    const MEDIA_PATH    = 'Core';

    /**
     * Maximum size for image in bytes
     * Default value is 1M
     *
     * @var int
     */
    const MAX_FILE_SIZE = 1048576;

    /**
     * Manimum image height in pixels
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
     * @var array
     */
    protected $_imageSize   = array(
        'minheight'     => self::MIN_HEIGHT,
        'minwidth'      => self::MIN_WIDTH,
        'maxheight'     => self::MAX_HEIGHT,
        'maxwidth'      => self::MAX_WIDTH,
    );
    
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory
     */
    protected $httpFactory;
    
    /**
     * File Uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
    
    /**
     * File Uploader factory
     *
     * @var \Magento\Framework\Io\File
     */
    protected $_ioFile;
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\Factory $imageFactory,
		\Magento\Catalog\Helper\Image $imgHelper,
		\Magento\Framework\Escaper $escaper,
		\Magento\Store\Model\App\Emulation $appEmulation
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->httpFactory = $httpFactory;
		$this->_productRepository = $productRepository;    
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_ioFile = $ioFile;
        $this->_storeManager = $storeManager;
        $this->_imageFactory = $imageFactory;
		$this->_imgHelper = $imgHelper;
		$this->_escaper = $escaper;
 		$this->_appEmulation = $appEmulation;

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
        $io = $this->_ioFile;
        $io->open(array('path' => $this->getBaseDir()));
        if ($io->fileExists($imageFile)) {
            return $io->rm($imageFile);
        }
        return false;
    }
    
    /**
     * Return URL for resized Core Item Image
     *
     * @param Stamped\Core\Model\Core $item
     * @param integer $width
     * @param integer $height
     * @return bool|string
     */
    public function resize(\Stamped\Core\Model\Core $item, $width, $height = null)
    {
        if (!$item->getImage()) {
            return false;
        }

        if ($width < self::MIN_WIDTH || $width > self::MAX_WIDTH) {
            return false;
        }
        $width = (int)$width;

        if (!is_null($height)) {
            if ($height < self::MIN_HEIGHT || $height > self::MAX_HEIGHT) {
                return false;
            }
            $height = (int)$height;
        }

        $imageFile = $item->getImage();
        $cacheDir  = $this->getBaseDir() . '/' . 'cache' . '/' . $width;
        $cacheUrl  = $this->getBaseUrl() . '/' . 'cache' . '/' . $width . '/';

        $io = $this->_ioFile;
        $io->checkAndCreateFolder($cacheDir);
        $io->open(array('path' => $cacheDir));
        if ($io->fileExists($imageFile)) {
            return $cacheUrl . $imageFile;
        }

        try {
            $image = $this->_imageFactory->create($this->getBaseDir() . '/' . $imageFile);
            $image->resize($width, $height);
            $image->save($cacheDir . '/' . $imageFile);
            return $cacheUrl . $imageFile;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Upload image and return uploaded image file name or false
     *
     * @throws Mage_Core_Exception
     * @param string $scope the request key for file
     * @return bool|string
     */
    public function uploadImage($scope)
    {
        $adapter = $this->httpFactory->create();
        $adapter->addValidator(new \Zend_Validate_File_ImageSize($this->_imageSize));
        $adapter->addValidator(
            new \Zend_Validate_File_FilesSize(['max' => self::MAX_FILE_SIZE])
        );
        
        if ($adapter->isUploaded($scope)) {
            // validate image
            if (!$adapter->isValid($scope)) {
                throw new \Magento\Framework\Model\Exception(__('Uploaded image is not valid.'));
            }
            
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $scope]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);
            
            if ($uploader->save($this->getBaseDir())) {
                return $uploader->getUploadedFileName();
            }
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
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(self::MEDIA_PATH);
        return $path;
    }
    
    /**
     * Return the Base URL for Core Item images
     *
     * @return string
     */
    public function getBaseUrl()
    { 
        return $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . '/' . self::MEDIA_PATH;
    }
    
    /**
     * Return the number of items per page
     * @return int
     */
    public function getCorePerPage()
    {
        return abs((int)$this->_scopeConfig->getValue(self::XML_PATH_ITEMS_PER_PAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }
    
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    public function getConfigValue($field, $storeId = null)
   {
       return $this->scopeConfig->getValue(
           $field,
           \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
           $storeId
       );
   }
    public function isConfigured($store)
	{ 
    		//check if both app_key and secret exist
		if(($this->getApiKey($store) == null) || ($this->getApiSecret($store) == null))
		{
                   return false;
		}

		return true;
	}

	public function getOrderProductsData($order) 
	{
		$this->_storeManager->setCurrentStore($order->getStoreId());
			$products = $order->getAllVisibleItems(); //filter out simple products
			$products_arr = array();
			
			foreach ($products as $item) {
				$full_product = $this->_productRepository->get($item->getSku());

				$parent = $item->getProduct();
                if ($parent && !empty($parent->getId())) {
	                $full_product = $this->_productRepository->getById($parent->getId());
                }

				$product_data = array();
				$product_data['productId'] = $full_product->getId();
				$product_data['productTitle'] = $full_product->getName();
				$product_data['productUrl'] = '';
				$product_data['productImageUrl'] = '';
				try {
					$product_data['productUrl'] = $full_product->getUrlInStore(array('_store' => $order->getStoreId()));
					
					$this->_appEmulation->startEnvironmentEmulation($order->getStoredId(), \Magento\Framework\App\Area::AREA_FRONTEND, true);
					$product_data['productImageUrl'] = $this->_imgHelper->init($full_product, 'product_base_image')->getUrl();
					$this->_appEmulation->stopEnvironmentEmulation();

					if ($full_product->getUpc()) {
						$product_data['productBarcode'] = $full_product->getUpc();
					}
					if ($full_product->getBrand()) {
						$product_data['productBrand'] = $full_product->getBrand();
					}
					if ($full_product->getMpn()) {
						$product_data['productSKU'] = $full_product->getMpn();
					}
				} catch (Exception $e) {
				}

				$rawdescription =  str_replace(array('\'', '"'), '', $full_product->getDescription()); 
				$description =  $this->_escaper->escapeHtml(strip_tags($rawdescription));
				$product_data['productDescription'] = $description;
				$product_data['productPrice'] = $item->getPrice();
				$products_arr[] = $product_data;
			}


		return $products_arr;
	}


	public function API_POST($path, $data, $store, $timeout=30) {
	
                try {
			$encodedData = json_encode($data);
                       
			$ch = curl_init($this->getApiUrlAuth($store).$path);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($encodedData),
			));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if (in_array($httpCode, array(200, 201))) {
				return json_decode($result, true);
			}
			if (400 === $httpCode) {
				$result = json_decode($result, true);
			}
			if (401 === $httpCode) {
				throw new Exception('API Key or API Secret is invalid, please do check. If you need any assistance, please contact us.');
			}
			
                } catch (Exception $e) {
                   return;
                }
	}
	
	public function API_GET2($path, $store, $timeout=30) {
	try {
			$ch = curl_init($path);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2000); //timeout in seconds

			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if (in_array($httpCode, array(200, 201))) {
				return json_decode($result, true);
			}
			if (400 === $httpCode) {
				$result = json_decode($result, true);
			}
			if (401 === $httpCode) {
				throw new Exception('API Key or API Secret is invalid, please do check. If you need any assistance, please contact us.');
			}
			
                } catch (Exception $e) {
                   return;
                }
	}

	public function API_GET($path, $store, $timeout=30) 
	{
		try {
			//  Initiate curl
			$ch = curl_init();
			// Disable SSL verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			// Will return the response, if false it print the response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// Set the url
			curl_setopt($ch, CURLOPT_URL,self::getApiUrlAuth($store).$path);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2000); //timeout in seconds

			// Execute
			$result=curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			// Closing
			curl_close($ch);

			//$encodedData = json_encode($result);
			//return $encodedData;

			if (in_array($httpCode, array(200, 201))) {
				return json_decode($result, true);
			}
			if (400 === $httpCode) {
				$result = json_decode($result, true);
			}
			if (401 === $httpCode) {
				throw new Exception('API Key or API Secret is invalid, please do check. If you need any assistance, please contact us.');
			}
			
			
                } catch (Exception $e) {
                   return;
                }
	}

    public function getApiKey($store)
    {
        
        return $this->getConfigValue(self::STAMPED_API_KEY_CONFIGURATION, $store);
    }
	
    public function getApiSecret($store)
    {
        return $this->getConfigValue(self::STAMPED_API_SECRET_CONFIGURATION, $store);
    }
	
    public function getApiStoreUrl($store)
    {
		$store_url = ($this->getConfigValue(self::STAMPED_STORE_URL_CONFIGURATION, $store));
		if (!$store_url){
			$store_url = Mage::app()->getStore($store->getId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		}

        return $store_url;
    }

	public function getApiUrlAuth($store)
	{
		$apiKey = $this->getApiKey($store);
		$apiSecret = $this->getApiSecret($store);
		$store_url = $this->getApiStoreUrl($store);

		return sprintf(self::STAMPED_SECURED_API_URL, $apiKey, $apiSecret, $store_url); 
	}
	
	public function getRichSnippet($productId, $store)
	{
       	return $this->API_GET("/richsnippet?productId=".$productId, $store);
	}

	public function createReviewRequest($order, $store)
	{
       	return $this->API_POST("/survey/reviews", $order, $store);
	}

	public function createReviewRequestBulk($orders, $store)
	{
       	return $this->API_POST("/survey/reviews/bulk", $orders, $store);
	}
        
        public function saveOrderAfter($order)
        {
        
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	        $store_id = $order->getStoreId();
	        $orderStatuses = $this->getConfigValue('stamped_core/stamped_settings/order_status_trigger');
	        if ($orderStatuses == null) {
                $orderStatuses = array('complete');
	        } else {
                    $orderStatuses = array_map('strtolower', explode(',', $orderStatuses));
	        }

            if (!$this->isConfigured($store_id))
            {
                return $this;
            }
            
			if (!in_array($order->getStatus(), $orderStatuses)) {
				return $this;
			}
			
			$data = array();
			if (!$order->getCustomerIsGuest()) {
				$data["user_reference"] = $order->getCustomerId();
			}

			// Get the id of the orders shipping address
            if (!is_object($order->getShippingAddress())) {
                return false;
            }

			$shippingId = $order->getShippingAddress()->getId();
                        $address = $objectManager->create('Magento\Customer\Model\Address')->load($shippingId);

                        $data = array();
                        if (!$order->getCustomerIsGuest()) {
                            $data["userReference"] = $order->getCustomerEmail();
                        }

                        $data["customerId"] = $order->getCustomerId();
                        $data["email"] = $order->getCustomerEmail();
					   
					   /*--------------------start custom changes-----------------------------*/
					   
					    $fName = $order->getCustomerFirstname();
						$lName = $order->getCustomerLastname();						
						if(empty($fName) ||  $fName==''){
						
							$fName = $order->getBillingAddress()->getFirstname();
						}
						
						if(empty($lName) ||  $lName==''){
						
							$lName = $order->getBillingAddress()->getLastname();		
						}					
						$data["firstName"] = $fName;
                        $data["lastName"] = $lName;	
						
						/*---------------------end custom changes----------------------------*/
						
                        $data["location"] = $address->getCountry();
                        $data['orderNumber'] = $order->getIncrementId();
                        $data['orderId'] = $order->getIncrementId();
                        $data['orderCurrencyISO'] = $order->getOrderCurrency()->getCode();
                        $data["orderTotalPrice"] = $order->getGrandTotal();
                        $data["orderSource"] = 'magento';
                        if($order->getCreatedAt()){
                        $data["orderDate"] = $order->getCreatedAt();
                        }else{
                        $data["orderData"] = date('Y-m-d H:m:s');
                        }
                        $data['itemsList'] = $this->getOrderProductsData($order);
						$data['platform'] = 'magento';
						
						

			$this->createReviewRequest($data, $store_id);

			return $this;	

		} catch(Exception $e) {
			return;
		}
        return $this;
    }
}
