<?php
namespace Payments\SecureAcceptance\Gateway\Config;


class SaConfigProviderStrategy implements \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface
     */
    private $paConfigProvider;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface
     */
    private $paNonPaConfigProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface $paConfigProvider,
        \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface $paNonPaConfigProvider,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->config = $config;
        $this->paConfigProvider = $paConfigProvider;
        $this->paNonPaConfigProvider = $paNonPaConfigProvider;
        $this->request = $request;
    }

    public function getProfileId($storeId = null)
    {
        return $this->config->getIsLegacyMode($storeId)
            ? $this->paConfigProvider->getProfileId($storeId)
            : $this->paNonPaConfigProvider->getProfileId($storeId);
    }

    public function getAccessKey($storeId = null)
    {
        return $this->config->getIsLegacyMode($storeId)
            ? $this->paConfigProvider->getAccessKey($storeId)
            : $this->paNonPaConfigProvider->getAccessKey($storeId);

    }

    public function getSecretKey($storeId = null)
    {
        if ($this->request->getParam('req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_SCOPE) == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return $this->paNonPaConfigProvider->getSecretKey($storeId);
        }

        return $this->config->getIsLegacyMode($storeId)
            ? $this->paConfigProvider->getSecretKey($storeId)
            : $this->paNonPaConfigProvider->getSecretKey($storeId);

    }
}
