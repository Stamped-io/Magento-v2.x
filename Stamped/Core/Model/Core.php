<?php

namespace Stamped\Core\Model;

/**
 * Core Model
 *
 * @method \Stamped\Core\Model\Resource\Page _getResource()
 * @method \Stamped\Core\Model\Resource\Page getResource()
 */
class Core extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Stamped\Core\Model\ResourceModel\Core');
    }

}
