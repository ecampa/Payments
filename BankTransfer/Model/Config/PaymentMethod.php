<?php
namespace Payments\BankTransfer\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class PaymentMethod implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'ideal',
                'label' => __('iDeal'),
            ],
            [
                'value' => 'sofort',
                'label' => __('Sofort')
            ],
            [
                'value' => 'bancontact',
                'label' => __('Bancontact')
            ]
        ];
    }
}
