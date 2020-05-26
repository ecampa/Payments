<?php
namespace Payments\SecureAcceptance\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Checkout\Model\Session;
use Payments\SecureAcceptance\Model\Ui\ConfigProvider;

class DataAssignObserver extends AbstractDataAssignObserver
{
    const KEY_FLEX_SIGNED_FIELDS = 'signedFields';
    const KEY_FLEX_SIGNATURE = 'signature';
    const KEY_FLEX_TOKEN = 'token';
    const KEY_FLEX_EXP_DATE = 'expDate';

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\SecureAcceptance\Model\PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Payments\SecureAcceptance\Model\PaymentTokenManagement $paymentTokenManagement
     * @param \Payments\SecureAcceptance\Gateway\Config\Config $config
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Payments\SecureAcceptance\Model\PaymentTokenManagement $paymentTokenManagement,
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->session = $session;
        $this->config = $config;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->assignMicroformData($observer);
        $this->assignCvv($observer);
    }

    private function assignMicroformData($observer)
    {

        if (!$this->config->isMicroform()) {
            return;
        }

        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        $additionalData = new DataObject($additionalData);
        $payment = $this->readPaymentModelArgument($observer);

        if ($token = $additionalData->getDataByKey(static::KEY_FLEX_TOKEN)) {
            $this->paymentTokenManagement->storeTokenIntoPayment($payment, $token);
        }

        if ($expDate = $additionalData->getDataByKey(static::KEY_FLEX_EXP_DATE)) {
            $payment->setAdditionalInformation(static::KEY_FLEX_EXP_DATE, $expDate);
        }

        if ($signedFields = $additionalData->getDataByKey(static::KEY_FLEX_SIGNED_FIELDS)) {
            $payment->setAdditionalInformation(static::KEY_FLEX_SIGNED_FIELDS, $signedFields);
            foreach (explode(',', $signedFields) as $field) {
                $payment->setAdditionalInformation($field, $additionalData->getDataByKey($field));
            }
        }

        if ($signature = $additionalData->getDataByKey(static::KEY_FLEX_SIGNATURE)) {
            $payment->setAdditionalInformation(static::KEY_FLEX_SIGNATURE, $signature);
        }
    }

    private function assignCvv($observer)
    {
        $data = $this->readDataArgument($observer);

        if ((!$this->_isVaultCCMethod($data) || !$this->_isCvvEnabled()) && !$this->config->isMicroform()) {
            return;
        }
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $additionalData = new DataObject($additionalData);
        if (!$cvv = $additionalData->getDataByKey('cvv') ?: $additionalData->getDataByKey('vault_cvv')) {
            return;
        }

        $payment = $this->readPaymentModelArgument($observer);
        $payment->setAdditionalInformation('cvv', $cvv);

        $this->session->setData('cvv', $cvv);

    }

    /**
     * @param Data
     * @return boolean
     */
    private function _isVaultCCMethod($data)
    {
        if ($data->getData(PaymentInterface::KEY_METHOD) != \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CC_VAULT_CODE) {
            return false;
        }
        return true;
    }

    /**
     * @return boolean
     */
    private function _isCvvEnabled()
    {
        return
            $this->config->getValue("enable_cvv") || $this->config->getValue("enable_admin_cvv");
    }
}
