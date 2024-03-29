<?php
namespace Payments\BankTransfer\Model\Payment;

use Payments\BankTransfer\Service\SofortSoap;
use Magento\Checkout\Model\Session;

/**
 * Class Config
 */
class Sofort extends \Magento\Payment\Model\Method\AbstractMethod
{
    
    const CODE = 'payments_bank_transfer_sofort';
    protected $_code = self::CODE;
    protected $_isOffline = false;
    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canInvoice                  = true;
    protected $_canRefund                   = true;
    protected $_canUseInternal              = false;
    protected $_supportedCurrencyCodes = ['USD'];
    protected $_gatewayAPI = null;
    protected $_checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * Payment constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param array $gatewayAPI
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Payments\BankTransfer\Service\SofortSoap $gatewayAPI,
        Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->storeManager = $storeManager;
        $this->_gatewayAPI = $gatewayAPI;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }
    
    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return $this->isCurrencyAvailable() && (bool)(int)$this->_scopeConfig->getValue(
            "payment/payments_bank_transfer/sofort_active",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    private function isCurrencyAvailable()
    {
        return (in_array(
            $this->storeManager->getStore()->getCurrentCurrency()->getCode(),
            explode(',', $this->_scopeConfig->getValue(
                "payment/payments_bank_transfer/sofort_currency",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ))
        ));
    }
}
