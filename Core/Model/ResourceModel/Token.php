<?php
namespace Payments\Core\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Token
 */
class Token extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('payments_payment_token', 'token_id');
    }
}
