<?php
namespace Payments\Core\Model\Source\SecureAcceptance;

/**
 * Class Type
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{

    const SA_WEB = 'web';
    const SA_SOP = 'silent';
    const SA_FLEX_MICROFORM = 'flex';

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SA_WEB,
                'label' => __('Web / Mobile')
            ],
            [
                'value' => self::SA_SOP,
                'label' => __('Silent Order Post (SOP)')
            ],
            [
                'value' => self::SA_FLEX_MICROFORM,
                'label' => __('Flex Microform')
            ],
        ];
    }
}
