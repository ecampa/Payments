<?php
namespace Payments\SecureAcceptance\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class SettlementRequest extends \Payments\SecureAcceptance\Gateway\Request\AbstractRequest implements BuilderInterface
{
    /**
     * Builds request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->getValidPaymentInstance($buildSubject);

        return $payment->getAdditionalInformation();
    }
}
