<?php
namespace Payments\SecureAcceptance\Gateway\Config;


class SaConfigProvider implements \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderNonPa
     */
    private $configProviderNonPa;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Magento\Framework\App\RequestInterface $request,
        \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderNonPa $configProviderNonPa
    ) {
        $this->config = $config;
        $this->configProviderNonPa = $configProviderNonPa;
    }

    public function getProfileId($storeId = null)
    {
        if ($this->config->isSilent($storeId)) {
            return $this->config->getSopAuthActive($storeId)
                ? $this->config->getValue(\Payments\SecureAcceptance\Gateway\Config\Config::KEY_SOP_AUTH_PROFILE_ID, $storeId)
                : $this->config->getSopProfileId($storeId);
        }

        return $this->config->getAuthActive($storeId)
            ? $this->config->getValue(\Payments\SecureAcceptance\Gateway\Config\Config::KEY_AUTH_PROFILE_ID, $storeId)
            : $this->config->getProfileId($storeId);
    }

    public function getAccessKey($storeId = null)
    {
        if ($this->config->isSilent($storeId)) {
            return $this->config->getSopAuthActive($storeId)
                ? $this->config->getValue(\Payments\SecureAcceptance\Gateway\Config\Config::KEY_SOP_AUTH_ACCESS_KEY, $storeId)
                : $this->config->getSopAccessKey($storeId);
        }

        return $this->config->getAuthActive($storeId)
            ? $this->config->getValue(\Payments\SecureAcceptance\Gateway\Config\Config::KEY_AUTH_ACCESS_KEY, $storeId)
            : $this->config->getAccessKey($storeId);
    }

    public function getSecretKey($storeId = null)
    {
        if ($this->config->isSilent($storeId)) {
            return $this->config->getSopAuthActive($storeId)
                ? $this->config->getValue(\Payments\SecureAcceptance\Gateway\Config\Config::KEY_SOP_AUTH_SECRET_KEY, $storeId)
                : $this->config->getSopSecretKey($storeId);
        }

        return $this->config->getAuthActive($storeId)
            ? $this->config->getValue(\Payments\SecureAcceptance\Gateway\Config\Config::KEY_AUTH_SECRET_KEY, $storeId)
            : $this->config->getSecretKey($storeId);
    }
}
