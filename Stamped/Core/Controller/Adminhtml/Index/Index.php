<?php

namespace Stamped\Core\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
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
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        try {
            $helper = $this->_objectManager->create('Stamped\Core\Helper\Data');
            $current_store;
            $page = 0;
            $now = time();
            $last = $now - (60*60*24*180); // 180 days ago
            $from = date("Y-m-d", $last);
			

            $store_code = $this->getRequest()->getParam('store');

            foreach ($this->_storeManager->getStores() as $store) {
               
                if ($store->getId() == $store_code) {
                    global $current_store;
                    $current_store = $store;
                    break;
                }
            }

            $store_id = $current_store->getId();
			

            if ($helper->isConfigured($current_store) == false)
            {
                Mage::app()->getResponse()->setBody('Please ensure you have configured the API Public Key and Private Key in Settings.');
                return;   
            }
			
            $salesOrder=$this->_objectManager->create('Magento\Sales\Model\Order');
            $orderStatuses = $helper->getConfigValue('stamped_core/stamped_settings/order_status_trigger', $current_store);
            if ($orderStatuses == null) {
                $orderStatuses = array('complete');
            } else {
                $orderStatuses = array_map('strtolower', (explode(',', $orderStatuses)));
            }
			
            $salesCollection = $salesOrder->getCollection()
                    ->addFieldToFilter('status', $orderStatuses)
                    ->addFieldToFilter('store_id', $store_id)
                    ->addAttributeToFilter('created_at', array('gteq' =>$from))
                    ->addAttributeToSort('created_at', 'DESC')
                    ->setPageSize(20);
            
			
            $pages = $salesCollection->getLastPageNumber();
			
            do {
                try {
                    $page++;
                    $salesCollection->setCurPage($page)->load();
                 
                    $orders = array();
					
                    foreach($salesCollection as $order)
                    {
                        $order_data = array();
                        // Get the id of the orders shipping address
						$shippingAddress = $order->getShippingAddress();
                        // Get shipping address data using the id
						if(!empty($shippingAddress)) {
							$address = $this->_objectManager->create('Magento\Customer\Model\Address')->load($shippingAddress->getId());
                            if (!empty($address)){
                                $order_data["location"] = $address->getCountry();
                            }
                        }

                        if (!$order->getCustomerIsGuest()) {
                            $order_data["userReference"] = $order->getCustomerEmail();
                        }

						$firstName = $order->getCustomerFirstname();
						$lastName = $order->getCustomerLastname();

						try {
							if (!$firstName && !$lastName) {
								$firstName = $order->getBillingAddress()->getFirstname();
								$lastName = $order->getBillingAddress()->getLastname();
							}
						} catch(Exception $e) {}

                        $order_data["customerId"] = $order->getCustomerId();
                        $order_data["email"] = $order->getCustomerEmail();
                        $order_data["firstName"] = $firstName;
                        $order_data["lastName"] = $lastName;
                        $order_data['orderNumber'] = $order->getIncrementId();
                        $order_data['orderId'] = $order->getIncrementId();
                        $order_data['orderCurrencyISO'] = $order->getOrderCurrency()->getCode();
                        $order_data["orderTotalPrice"] = $order->getGrandTotal();
                        $order_data["orderSource"] = 'magento';

                        $order_data["orderDate"] = $order->getCreatedAt();

						$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->setCurrentStore($order->getStoreId());
            
					    $products = $order->getAllVisibleItems(); //filter out simple products
						$products_arr = array();
		
						foreach ($products as $product) {
						//use configurable product instead of simple if still needed
						$full_product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product->getProductId());

				
				        if (!!$full_product->getId()){

				            $configurable_product_model = $this->_objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
				            $parentIds= $configurable_product_model->getParentIdsByChild($full_product->getId());
				            if (count($parentIds) > 0) {
            		            $full_product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($parentIds[0]);
				            }
			
				            $product_data = array();

				            $product_data['productId'] = $full_product->getId();
				            $product_data['productDescription'] = strip_tags($full_product->getDescription());
				            $product_data['productTitle'] = $full_product->getName();
			
				            try 
				            {
            		            $full_product2 = $this->_objectManager->get('Magento\Catalog\Model\ProductRepository')->getById($full_product->getId());
					            $product_data['productUrl'] = $full_product2->getUrlInStore(array('_store' => $order->getStoreId()));
					            $product_data['productImageUrl'] = $this->_objectManager->get('\Magento\Catalog\Helper\Image')->init($full_product2, 'product_base_image')->getUrl();
                                $product_data['productUrl'] = $full_product->getUrlInStore(array('_store' => $order->getStoreId()));

					            if ($full_product->getUpc()) {
						            $product_data['productBarcode'] = $full_product->getUpc();
					            }
					            if ($full_product->getBrand()) {
						            $product_data['productBrand'] = $full_product->getBrand();
					            }
					            if ($full_product->getMpn()) {
						            $product_data['productSKU'] = $full_product->getMpn();
					            }

				            } catch(Exception $e) {}

				            $product_data['productPrice'] = $product->getPrice();

				            $products_arr[] = $product_data;
			            }
		                }

		                $order_data['itemsList'] = $products_arr;
		
                        $order_data['apiUrl'] = $helper->getApiUrlAuth($current_store)."/survey/reviews/bulk";

                        $orders[] = $order_data;
						
                    }

                    if (count($orders) > 0) 
                    {
						$result = $helper->createReviewRequestBulk($orders, $current_store);
                    }
                } catch (Exception $e) {
				
                    return;
                }

                $salesCollection->clear();

            } while ($page <= (3000 / 200) && $page < $pages);
			
		
        } catch(Exception $e) {
			//Mage::log('My variable: '.$e);
           return;
        }
        echo '1';
    }
}
