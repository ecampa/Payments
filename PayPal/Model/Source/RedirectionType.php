<?php
namespace Payments\PayPal\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class RedirectionType
 */
class RedirectionType implements ArrayInterface
{
    const TRADITIONAL = 'traditional';
    const IN_CONTEXT = 'in_context';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TRADITIONAL,
                'label' => __('Traditional Express Checkout'),
            ],
            [
                'value' => self::IN_CONTEXT,
                'label' => __('In-Context Express Checkout')
            ]
        ];
    }
}
