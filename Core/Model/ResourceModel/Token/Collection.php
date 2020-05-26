<?php
namespace Payments\Core\Model\ResourceModel\Token;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Payments\Core\Model\Token',
            'Payments\Core\Model\ResourceModel\Token'
        );
    }
}
