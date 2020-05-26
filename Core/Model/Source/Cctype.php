<?php
namespace Payments\Core\Model\Source;

/**
 * Class Cctype
 */
class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'OT', 'DN' , 'MI' ];
    }
}
