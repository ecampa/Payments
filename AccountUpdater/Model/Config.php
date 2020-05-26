<?php

namespace Payments\AccountUpdater\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const TEST_ENDPOINT_URL = '__/legacy/test/DownloadReport';
    const PROD_ENDPOINT_URL = '__/legacy/DownloadReport';

    const KEY_ACTIVE = 'payment/payments_sa/au_active';
    const KEY_TEST_MODE = 'payment/payments_sa/au_test_mode';
    const KEY_USERNAME = 'payment/payments_sa/au_username';
    const KEY_PASSWORD = 'payment/payments_sa/au_password';
    const KEY_MERCHANT_ID = 'payment/payments_sa/au_merchant_id';
    const KEY_CRON_EXPR = 'payment/payments_sa/au_cron_expr';
    const KEY_TEST_REPORT_PATH = 'payment/payments_sa/au_test_report_path';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return (bool) $this->getValue(self::KEY_TEST_MODE);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getValue(self::KEY_USERNAME);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getValue(self::KEY_PASSWORD);
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->getValue(self::KEY_MERCHANT_ID);
    }

    /**
     * @return string
     */
    public function getCronExpr()
    {
        return $this->getValue(self::KEY_CRON_EXPR);
    }

    /**
     * @return string
     */
    public function getTestReportPath()
    {
        return $this->getValue(self::KEY_TEST_REPORT_PATH);
    }

    /**
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->isTestMode() ? self::TEST_ENDPOINT_URL : self::PROD_ENDPOINT_URL;
    }

    /**
     * @param string $path
     * @return string
     */
    private function getValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
