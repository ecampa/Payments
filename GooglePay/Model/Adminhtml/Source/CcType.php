<?php
namespace Payments\GooglePay\Model\Adminhtml\Source;

/**
 * Class CcType
 */
class CcType extends \Magento\Payment\Model\Source\Cctype
{

    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return [
            'AE',
            'DI',
            'JCB',
            'MC',
            'VI',
        ];
    }
}
