<?php
namespace Payments\Core\Model;

/**
 * Class Config
 * Access configuration of all child modules
 */
abstract class AbstractGatewayConfig extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_DEVELOPER_ID = 'developer_id';
    const KEY_SECRET_KEY = 'secret_key';
    const KEY_PROFILE_ID = 'profile_id';
    const KEY_ACCESS_KEY = 'access_key';
    const KEY_USE_IFRAME = 'use_iframe';
    const KEY_TITLE = 'title';
    const KEY_TEST_MODE = 'test_mode';
    const KEY_DEBUG_MODE = 'debug';
    const KEY_IGNORE_AVS = 'ignore_avs';
    const KEY_IGNORE_CVN = 'ignore_cvn';
    const KEY_RESPONSE_TEXT_OVERRIDE = 'response_text_override';
    const KEY_PAYMENT_ACTION = 'payment_action';
    const KEY_ALLOWSPECIFIC = 'allowspecific';
    const KEY_SPECIFICCOUNTRY = 'specificcountry';
    const KEY_CCTYPES = 'cctypes';
    const KEY_MERCHANT_ID = 'merchant_id';
    const KEY_TRANSACTION_KEY = 'transaction_key';
    const KEY_USE_TEST_WSDL = 'use_test_wsdl';
    const KEY_PATH_TO_WSDL = 'path_to_wsdl';
    const KEY_PATH_TO_TEST_WSDL = 'path_to_test_wsdl';
    const KEY_PAYMENT_METHOD = 'payment_method';
    const KEY_AUTH_INDICATOR = 'auth_indicator';
    const KEY_ENABLE_CVV = 'enable_cvv';
    const KEY_REPORT_USERNAME = 'report_username';
    const KEY_REPORT_PASSWORD = 'report_password';
    const KEY_REPORT_URL = 'report_url';
    const KEY_ENABLE_DM_CRON = 'enable_dm_cron';
    const KEY_SHOW_EXACT_ERROR = 'show_exact_error';
    const KEY_ENABLED_DM_CRON_ACCEPTED_SETTLEMENT = 'enable_dm_accepted_settlement';
    const KEY_SA_TYPE = 'secureacceptance_type';
    const PATH = 'payment/payments_section/payments/';
    
    public function getDeveloperId()
    {
        return $this->getValue(self::KEY_DEVELOPER_ID);
    }
    
    public function isActive()
    {
        return $this->getValue(self::KEY_ACTIVE);
    }

    public function getSecretKey()
    {
        return ($this->getSaType() == \Payments\Core\Model\Source\SecureAcceptance\Type::SA_WEB)
            ? $this->getValue(self::KEY_SECRET_KEY)
            : $this->getValue('sop_secret_key');
    }

    public function getProfileId()
    {
        return ($this->getSaType() == \Payments\Core\Model\Source\SecureAcceptance\Type::SA_WEB)
            ? $this->getValue(self::KEY_PROFILE_ID)
            : $this->getValue('sop_profile_id');
    }

    public function getAccessKey()
    {
        return ($this->getSaType() == \Payments\Core\Model\Source\SecureAcceptance\Type::SA_WEB)
            ? $this->getValue(self::KEY_ACCESS_KEY)
            : $this->getValue('sop_access_key');
    }

    public function getUseIframe()
    {
        return ($this->getSaType() == \Payments\Core\Model\Source\SecureAcceptance\Type::SA_WEB)
            ? $this->getValue(self::KEY_USE_IFRAME)
            : 0;
    }

    public function isSilent()
    {
        return ($this->getSaType() == \Payments\Core\Model\Source\SecureAcceptance\Type::SA_SOP);
    }

    public function getSaType($storeId = null)
    {
        return $this->getValue(self::KEY_SA_TYPE, $storeId);
    }

    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    public function isTestMode()
    {
        return (bool) $this->getValue(self::KEY_TEST_MODE);
    }

    public function getDebugMode()
    {
        return $this->getValue(self::KEY_DEBUG_MODE);
    }

    public function getResponseTextOverride()
    {
        return $this->getValue(self::KEY_RESPONSE_TEXT_OVERRIDE);
    }

    public function getPaymentAction()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }

    public function getAllowspecific()
    {
        return $this->getValue(self::KEY_ALLOWSPECIFIC);
    }

    public function getSpecificcountry()
    {
        return $this->getValue(self::KEY_SPECIFICCOUNTRY);
    }

    public function getCcTypes()
    {
        return $this->getValue(self::KEY_CCTYPES);
    }

    public function getMerchantId($storeId = null)
    {
        return $this->getValue(self::KEY_MERCHANT_ID, $storeId);
    }

    public function getTransactionKey()
    {
        return $this->getValue(self::KEY_TRANSACTION_KEY);
    }

    public function getUseTestWsdl($storeId = null)
    {
        return $this->getValue(self::KEY_USE_TEST_WSDL, $storeId);
    }

    public function getPathToWsdl()
    {
        return $this->getValue(self::KEY_PATH_TO_WSDL);
    }

    public function getPathToTestWsdl()
    {
        return $this->getValue(self::KEY_PATH_TO_TEST_WSDL);
    }

    public function getPaymentMethod()
    {
        return $this->getValue(self::KEY_PAYMENT_METHOD);
    }

    public function getAuthIndicator()
    {
        return $this->getValue(self::KEY_AUTH_INDICATOR);
    }

    public function isCVVEnabled()
    {
        return (bool) $this->getValue(self::KEY_ENABLE_CVV);
    }

    public function getReportUsername()
    {
        return $this->getValue(self::KEY_REPORT_USERNAME);
    }

    public function getReportPassword()
    {
        return $this->getValue(self::KEY_REPORT_PASSWORD);
    }

    public function getReportUrl()
    {
        return $this->getValue(self::KEY_REPORT_URL);
    }

    public function isDecisionManagerCronEnabled($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_ENABLE_DM_CRON, $storeId);
    }

    public function decisionManagerSettlementEnabled()
    {
        return (bool) $this->getValue(self::KEY_ENABLED_DM_CRON_ACCEPTED_SETTLEMENT);
    }
}
