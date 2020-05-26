<?php
namespace Payments\BankTransfer\Model;

class IdealOption extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Payments\BankTransfer\Model\ResourceModel\IdealOption');
    }
}
