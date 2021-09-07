<?php
namespace Payments\SecureAcceptance\Gateway\Config;


class SaConfigProviderNonPa implements \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    public function __construct(\Payments\SecureAcceptance\Gateway\Config\Config $config)
    {
        $this->config = $config;
    }

    public function getProfileId($storeId = null)
    {
        if ($this->config->isSilent($storeId)) {
            return $this->config->getSopProfileId($storeId);
        }

        return $this->config->getProfileId($storeId);
    }

    public function getAccessKey($storeId = null)
    {
        if ($this->config->isSilent($storeId)) {
            return $this->config->getSopAccessKey($storeId);
        }

        return $this->config->getAccessKey($storeId);
    }

    public function getSecretKey($storeId = null)
    {
        if ($this->config->isSilent($storeId)) {
            return $this->config->getSopSecretKey($storeId);
        }

        return $this->config->getSecretKey($storeId);
    }
}
