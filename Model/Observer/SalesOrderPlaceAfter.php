<?php

namespace Stamped\Core\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Stamped\Core\Helper\Data;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var Data $helper
     */
    protected $helper;

    /**
     * SalesOrderPlaceAfter constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $this->helper->saveOrderAfter($observer->getOrder());
    }
}
