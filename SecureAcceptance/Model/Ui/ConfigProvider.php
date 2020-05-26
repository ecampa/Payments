<?php
namespace Payments\SecureAcceptance\Model\Ui;

use Payments\SecureAcceptance\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'payments_sa';
    const CC_VAULT_CODE = 'payments_sa_cc_vault';

    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     */
    public function __construct(\Payments\SecureAcceptance\Gateway\Config\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    "sop_service_url" => $this->config->getSopServiceUrl(),
                    "sop_service_url_test" => $this->config->getSopServiceUrlTest(),
                    "active" => $this->config->isActive(),
                    "use_iframe" => $this->config->getUseIFrame(),
                    "use_iframe_sandbox" => $this->config->getUseIFrameSandbox(),
                    "title" => $this->config->getTitle(),
                    "test_mode" => $this->config->getTestMode(),
                    "debug" => $this->config->getDebug(),
                    "ignore_avs" => $this->config->getIgnoreAvs(),
                    "ignore_cvn" => $this->config->getIgnoreCvn(),
                    "allowspecific" => $this->config->getAllowSpecific(),
                    "developer_id" => $this->config->getDeveloperId(),
                    "silent_post" => $this->config->isSilent(),
                    "vaultCode" => self::CC_VAULT_CODE,
                    'vault_enable' => $this->config->isVaultEnabled(),
                    "availableCardTypes" => $this->config->getCcTypes(),
                    "iframe_post" => !$this->config->getIsLegacyMode(),
                    "sa_type" => $this->config->getSaType(),
                ],
                self::CC_VAULT_CODE => [
                    "is_cvv_enabled" => $this->config->isCVVEnabled()
                ]
            ]
        ];
    }
}
