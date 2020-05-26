<?php
namespace Payments\ThreeDSecure\Gateway\Config;

/**
 * Class Config
 */
class Config extends \Payments\Core\Model\AbstractGatewayConfig
{

    const KEY_IS_SANDBOX = 'test_mode_3ds';
    const KEY_IS_ACTIVE = 'active_3ds';
    const KEY_ORG_UNIT_ID = "org_unit_id_3ds";
    const KEY_API_ID = "api_id_3ds";
    const KEY_API_KEY = "api_key_3ds";
    const KEY_ENABLED_CARDS = 'card_types_3ds';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($scopeConfig, \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE);
    }

    public function isEnabled()
    {
        return $this->getValue(self::KEY_IS_ACTIVE);
    }

    public function isTestMode()
    {
        return $this->getValue(self::KEY_IS_SANDBOX);
    }

    public function getOrgUnitId()
    {
        return $this->getValue(self::KEY_ORG_UNIT_ID);
    }

    public function getApiId()
    {
        return $this->getValue(self::KEY_API_ID);
    }

    public function getApiKey()
    {
        return $this->getValue(self::KEY_API_KEY);
    }

    public function getEnabledCards($storeId = null)
    {
        return explode(',', $this->getValue(self::KEY_ENABLED_CARDS, $storeId));
    }

}
