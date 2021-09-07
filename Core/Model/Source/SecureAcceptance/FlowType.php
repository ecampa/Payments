<?php
namespace Payments\Core\Model\Source\SecureAcceptance;

/**
 * Class Type
 */
class FlowType implements \Magento\Framework\Option\ArrayInterface
{

    const SA_PROFILE = 0;
    const SA_PLUGIN = 1;

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SA_PROFILE,
                'label' => __('Secure Acceptance API')
            ],
            [
                'value' => self::SA_PLUGIN,
                'label' => __('SOAP Toolkit API')
            ]
        ];
    }
}
