<?php
namespace Payments\VisaCheckout\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class SettlementRequest extends \Payments\VisaCheckout\Gateway\Request\AbstractRequest implements BuilderInterface
{
    /**
     * Builds request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $this->getValidPaymentInstance($buildSubject);
        $request = $this->requestDataBuilder->buildSettlementRequestData();

        return (array) $request;
    }
}
