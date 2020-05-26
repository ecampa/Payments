<?php

namespace Payments\Recaptcha\Block\LayoutProcessor\Checkout;

class Recaptcha implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Payments\Recaptcha\Model\LayoutSettings
     */
    private $layoutSettings;

    public function __construct(
        \Payments\Recaptcha\Model\LayoutSettings $layoutSettings
    ) {
        $this->layoutSettings = $layoutSettings;
    }

    public function process($jsLayout)
    {
        $jsLayout['components']
        ['checkout']['children']
        ['steps']['children']
        ['billing-step']['children']
        ['payment']['children']
        ['payments-list']['children']
        ['payments-recaptcha']['children']
        ['msp_recaptcha']['settings'] = $this->layoutSettings->getCaptchaSettings();

        return $jsLayout;
    }
}
