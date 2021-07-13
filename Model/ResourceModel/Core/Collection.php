<?php

/**
 * Core Resource Collection
 */
namespace Stamped\Core\Model\ResourceModel\Core;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Stamped\Core\Model\Core::class, \Stamped\Core\Model\ResourceModel\Core::class);
    }
}
