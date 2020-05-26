<?php
namespace Payments\SecureAcceptance\Gateway\Config;

use Payments\Core\Model\AbstractGatewayConfig;
use Payments\SecureAcceptance\Model\Ui\ConfigProvider;
use Magento\Backend\Model\Auth;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 */
class Config extends \Payments\Core\Model\AbstractGatewayConfig
{
    const KEY_PROFILE_ID = "profile_id";
    const KEY_SECRET_KEY = "secret_key";
    const KEY_ACCESS_KEY = "access_key";
    const KEY_SOP_PROFILE_ID = "sop_profile_id";
    const KEY_SOP_SECRET_KEY = "sop_secret_key";
    const KEY_SOP_ACCESS_KEY = "sop_access_key";
    const KEY_AUTH_ACTIVE = "auth_active";
    const KEY_AUTH_PROFILE_ID = "auth_profile_id";
    const KEY_AUTH_SECRET_KEY = "auth_secret_key";
    const KEY_AUTH_ACCESS_KEY = "auth_access_key";
    const KEY_SOP_AUTH_ACTIVE = "sop_auth_active";
    const KEY_SOP_AUTH_PROFILE_ID = "sop_auth_profile_id";
    const KEY_SOP_AUTH_SECRET_KEY = "sop_auth_secret_key";
    const KEY_SOP_AUTH_ACCESS_KEY = "sop_auth_access_key";
    const KEY_SOP_SERVICE_URL = "service_url";
    const KEY_SOP_SERVICE_URL_TEST = "service_url_test";
    const KEY_ACTIVE = "active";
    const KEY_USE_IFRAME = "use_iframe";
    const KEY_USE_IFRAME_SANDBOX = "use_iframe_sandbox";
    const KEY_TITLE = "title";
    const KEY_TEST_MODE = "test_mode";
    const KEY_DEBUG = "debug";
    const KEY_IGNORE_AVS = "ignore_avs";
    const KEY_IGNORE_CVN = "ignore_cvn";
    const KEY_ALLOWSPECIFIC = "allowspecific";
    const KEY_DEVELOPER_ID = "developer_id";
    const KEY_VAULT_ENABLE = 'active';
    const KEY_VAULT_ADMIN_ENABLE = 'active_admin';
    const KEY_VAULT_ADMIN_ENABLE_CVV = 'enable_admin_cvv';
    const KEY_MODE = 'sa_mode';
    const KEY_TOKEN_SKIP_DM = 'token_skip_decision_manager';
    const KEY_TOKEN_SKIP_AUTO_AUTH = 'token_skip_auto_auth';

    /**
     * @var bool
     */
    private $isAdmin;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     * @param Auth $auth
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        $isAdmin = false
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->isAdmin = $isAdmin;
    }

    public function getProfileId()
    {
        return $this->getValue(self::KEY_PROFILE_ID);
    }

    public function getSecretKey()
    {
        return $this->getValue(self::KEY_SECRET_KEY);
    }

    public function getAccessKey()
    {
        return $this->getValue(self::KEY_ACCESS_KEY);
    }

    public function getSopProfileId()
    {
        return $this->getValue(self::KEY_SOP_PROFILE_ID);
    }

    public function getSopSecretKey()
    {
        return $this->getValue(self::KEY_SOP_SECRET_KEY);
    }

    public function getSopAccessKey()
    {
        return $this->getValue(self::KEY_SOP_ACCESS_KEY);
    }

    public function getSopServiceUrl()
    {
        return $this->getValue(self::KEY_SOP_SERVICE_URL);
    }

    public function getSopServiceUrlTest()
    {
        return $this->getValue(self::KEY_SOP_SERVICE_URL_TEST);
    }

    public function isActive()
    {
        return $this->getValue(self::KEY_ACTIVE);
    }

    public function getUseIFrame()
    {
        return (bool) $this->getValue(self::KEY_USE_IFRAME);
    }

    /**
     * Return option value for WM iframe's sandbox attribute enabled
     *
     * @return bool
     */
    public function getUseIFrameSandbox()
    {
        return (bool) $this->getValue(self::KEY_USE_IFRAME_SANDBOX);
    }

    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    public function getTestMode()
    {
        return $this->getValue(self::KEY_TEST_MODE);
    }

    public function getDebug()
    {
        return $this->getValue(self::KEY_DEBUG);
    }

    public function getIgnoreAvs()
    {
        return (bool)$this->getValue(self::KEY_IGNORE_AVS);
    }

    public function getIgnoreCvn()
    {
        return (bool)$this->getValue(self::KEY_IGNORE_CVN);
    }

    public function getAllowSpecific()
    {
        return $this->getValue(self::KEY_ALLOWSPECIFIC);
    }

    public function getDeveloperId()
    {
        return $this->getValue(self::KEY_DEVELOPER_ID);
    }

    public function isVaultEnabled()
    {
        return $this->isSilent() && $this->isVaultEnabledConfiguredOption();
    }

    /**
     * Returns the *configured* value of vault enabled flag, despite the SOP or other method is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isVaultEnabledConfiguredOption($storeId = null)
    {
        $this->setMethodCode(\Payments\SecureAcceptance\Model\Ui\ConfigProvider::CC_VAULT_CODE);
        $isVaultEnable = (bool) $this->getValue(self::KEY_VAULT_ENABLE, $storeId);
        $this->setMethodCode(\Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE);

        return $isVaultEnable;
    }

    /**
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function isVaultEnabledAdmin($storeId = null)
    {
        $this->setMethodCode(\Payments\SecureAcceptance\Model\Ui\ConfigProvider::CC_VAULT_CODE);
        $value = $this->getValue(self::KEY_VAULT_ADMIN_ENABLE, $storeId);
        $this->setMethodCode(\Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE);

        return $value;
    }

    public function getVaultTitle()
    {
        $this->setMethodCode(\Payments\SecureAcceptance\Model\Ui\ConfigProvider::CC_VAULT_CODE);
        $title = $this->getValue(self::KEY_TITLE);
        $this->setMethodCode(\Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE);
        return $title;
    }

    public function getIsLegacyMode()
    {
        if ($this->isAdmin) {
            return true;
        }
        return $this->getValue(self::KEY_MODE);
    }

    public function isMicroform($storeId = null)
    {
        return $this->getSaType($storeId) == \Payments\Core\Model\Source\SecureAcceptance\Type::SA_FLEX_MICROFORM;
    }

    public function getAuthActive()
    {
        return $this->getValue(self::KEY_AUTH_ACTIVE);
    }

    public function getAuthProfileId()
    {
        return $this->getAuthActive() ? $this->getValue(self::KEY_AUTH_PROFILE_ID) : $this->getProfileId();
    }

    public function getAuthSecretKey()
    {
        return $this->getAuthActive() ? $this->getValue(self::KEY_AUTH_SECRET_KEY) : $this->getSecretKey();
    }

    public function getAuthAccessKey()
    {
        return $this->getAuthActive() ? $this->getValue(self::KEY_AUTH_ACCESS_KEY) : $this->getAccessKey();
    }

    public function getSopAuthActive()
    {
        return $this->getValue(self::KEY_SOP_AUTH_ACTIVE);
    }

    public function getSopAuthProfileId()
    {
        return $this->getSopAuthActive() ? $this->getValue(self::KEY_SOP_AUTH_PROFILE_ID) : $this->getSopProfileId();
    }

    public function getSopAuthSecretKey()
    {
        return $this->getSopAuthActive() ? $this->getValue(self::KEY_SOP_AUTH_SECRET_KEY) : $this->getSopSecretKey();
    }

    public function getSopAuthAccessKey()
    {
        return $this->getSopAuthActive() ? $this->getValue(self::KEY_SOP_AUTH_ACCESS_KEY) : $this->getSopAccessKey();
    }
}
