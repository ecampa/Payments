<?php
namespace Payments\WeChatPay\Model\Config\Source;

/**
 * Class CheckStatusResponse
 */
class CheckStatusResponse implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Settled')],
            ['value' => 'TC200000', 'label' => __('Pending')],
            ['value' => 'TC200001', 'label' => __('Abandoned')],
            ['value' => 'TC200010', 'label' => __('Failed')],
        ];
    }

}
