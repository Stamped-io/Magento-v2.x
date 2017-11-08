<?php
namespace Stamped\Core\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class SalesOrderStatusHistory implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager,
            \Magento\Checkout\Model\Session $checkoutSession,
            StoreManagerInterface $storeManager
            
            )
    {
        $this->_objectManager = $objectmanager;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager    = $storeManager;
    }

    public function execute(EventObserver $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');

        $actionName     = $requestInterface->getActionName();
        if($actionName == 'addComment')
		{
			$order = $observer->getOrder();
			$helper = $this->_objectManager->create('Stamped\Core\Helper\Data');
			$helper->saveOrderAfter($order);
        }
    }
}