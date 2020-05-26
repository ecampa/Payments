<?php
namespace Payments\BankTransfer\Model\ResourceModel\IdealOption;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Payments\BankTransfer\Model\IdealOption',
            'Payments\BankTransfer\Model\ResourceModel\IdealOption'
        );
    }
}
