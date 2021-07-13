<?php
namespace Stamped\Core\Model\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Stamped\Core\Helper\Data;

class SalesOrderStatusHistory implements ObserverInterface
{
    /**
     * @var Data $helper
     */
    protected $helper;

    /**
     * @var RequestInterface $request
     */
    protected $request;

    /**
     * SalesOrderStatusHistory constructor
     * .
     * @param Data $helper
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helper,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->request->getActionName() == 'addComment') {
            $this->helper->saveOrderAfter($observer->getOrder());
        }
    }
}
