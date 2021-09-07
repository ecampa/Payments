<?php
namespace Payments\KlarnaFinancial\Gateway\Config;

use Payments\Core\Model\AbstractGatewayConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Config extends \Payments\Core\Model\AbstractGatewayConfig
{
    const CODE = 'payments_klarna';

    const KEY_ACTIVE = "active";
    const KEY_USE_DEFAULT_MID = "use_default_mid";
    const KEY_TITLE = "title";
    const KEY_TEST = "test_mode";
    const KEY_PAYMENT_ACTION = "payment_action";

    /**
     * @var null|string
     */
    private $methodCode;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $pathPattern;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode,
        $pathPattern
       
    ) {
        $this->methodCode = $methodCode;
        $this->scopeConfig = $scopeConfig;
        $this->pathPattern = $pathPattern;
        parent::__construct($scopeConfig, self::CODE, $pathPattern);
    }

    public function isActive()
    {
        return $this->getValue(self::KEY_ACTIVE);
    }

    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }
    public function isDefaultMid()
    {
        return $this->getValue(self::KEY_USE_DEFAULT_MID);
    }

    public function isTest()
    {
        return $this->getValue(self::KEY_TEST);
    }

    public function getPaymentAction()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }

    public function getValue($field, $storeId = null)
    {
        $value = $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null) {
            $this->setMethodCode(\Payments\Core\Model\Config::CODE);
            $value = parent::getValue($field, $storeId);
        }

        return $value;
    }

    public function isAuthMode()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION) ==
            \Payments\KlarnaFinancial\Model\Source\PaymentAction::ACTION_AUTHORIZE;
    }

    public function isDeveloperMode()
    {
        return ($this->isTest() == 1) ? "true" : "false";
    }
	
	/*
	
	* get module merchant id
	
	* @param $storeId
	* @return merchantId|NULL
	*/
	public function getMerchantId($storeId = null)
    {
		return $this->getModuleValue(self::KEY_MERCHANT_ID, $storeId);
    }
	
	
	/*
	
	* get module transaction key
	
	* @param $storeId
	* @return transactionKey|NULL
	*/
	public function getTransactionKey($storeId = null)
    {
		return $this->getModuleValue(self::KEY_TRANSACTION_KEY, $storeId);
    }
	
	/**
  * return module specific credentials
  * @param string $field
  * @param $storeId
  * @return string $value
  */
 public function getModuleValue($field, $storeId = null)
    {
        $value = null;
        $isDefaultMid = $this->isDefaultMid();
        if(!$isDefaultMid){
            $value = $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
            );
        }
    
        return $value;
    }
	
}
