<?php

namespace Payments\Recaptcha\Model;

class Config
{

    const XML_PATH_RECAPTCHA_ENABLED = 'payment/payments_sa/recaptcha_enabled';
    const XML_PATH_RECAPTCHA_PUBLIC_KEY = 'payment/payments_sa/recaptcha_website_key';
    const XML_PATH_RECAPTCHA_PRIVATE_KEY = 'payment/payments_sa/recaptcha_secret_key';
    const XML_PATH_RECAPTCHA_TYPE = 'payment/payments_sa/recaptcha_type';
    const XML_PATH_RECAPTCHA_POSITION = 'payment/payments_sa/recaptcha_badge_position';
    const XML_PATH_RECAPTCHA_LANGUAGE = 'payment/payments_sa/recaptcha_language';


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(static::XML_PATH_RECAPTCHA_ENABLED);
    }

    public function getPublicKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_RECAPTCHA_PUBLIC_KEY);
    }

    public function getType()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_RECAPTCHA_TYPE);
    }

    public function getPrivateKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_RECAPTCHA_PRIVATE_KEY);
    }

    public function getPosition()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_RECAPTCHA_POSITION);
    }

    public function getLanguageCode()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_RECAPTCHA_LANGUAGE);
    }

}
