<?php
namespace Payments\Core\Model;

/**
 * Class Token
 */
class Token extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Payments\Core\Model\ResourceModel\Token');
    }
}
