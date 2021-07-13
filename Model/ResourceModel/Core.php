<?php

namespace Stamped\Core\Model\ResourceModel;

/**
 * Core Resource Model
 */
class Core extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('stamped_core', 'core_id');
    }
}
