<?php
namespace Payments\SecureAcceptance\Gateway\Config;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CanInitializeHandler implements ValueHandlerInterface
{
    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    public function handle(array $subject, $storeId = null)
    {
        if ($this->config->isAdmin()) {
            return false;
        }

        $paymentDo = $this->subjectReader->readPayment($subject);

        if ($paymentDo->getPayment()->getMethodInstance()->getCode() == \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CC_VAULT_CODE){
            return false;
        }

        return (bool)$this->config->getIsLegacyMode($storeId) && !$this->config->isMicroform();
    }
}
