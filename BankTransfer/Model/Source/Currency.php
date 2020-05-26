<?php
namespace Payments\BankTransfer\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Currency
 */
class Currency implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'USD',
                'label' => __('USD'),
            ],
            [
                'value' => 'EUR',
                'label' => __('EUR')
            ]
        ];
    }
}
