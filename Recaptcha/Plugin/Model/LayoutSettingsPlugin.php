<?php

namespace Payments\Recaptcha\Plugin\Model;


class LayoutSettingsPlugin
{

    /**
     * @var \Payments\Recaptcha\Model\Config
     */
    private $config;

    public function __construct(
        \Payments\Recaptcha\Model\Config $config
    ) {
        $this->config = $config;
    }

    public function afterGetCaptchaSettings(
        \MSP\ReCaptcha\Model\LayoutSettings $subject,
        array $result
    ) {
        $result['enabled']['payments'] = $this->config->isEnabled();

        return $result;
    }
}
