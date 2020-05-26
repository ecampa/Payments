<?php

namespace Payments\Recaptcha\Model;


class LayoutSettings
{

    /**
     * @var \Payments\Recaptcha\Model\Config
     */
    private $config;

    public function __construct(\Payments\Recaptcha\Model\Config $config)
    {
        $this->config = $config;
    }

    public function getCaptchaSettings()
    {
        return [
            'siteKey' => $this->config->getPublicKey(),
            'size' => ($this->config->getType() == 'invisible') ? 'invisible' : null,
            'badge' => $this->config->getPosition(),
            'lang' => $this->config->getLanguageCode(),
            'enabled' => [
                'payments' => $this->config->isEnabled(),
            ]
        ];
    }
}
