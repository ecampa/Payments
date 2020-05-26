<?php
namespace Payments\BankTransfer\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class IdealOption
 */
class IdealOption extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('payments_ideal_option', 'id');
    }
}
