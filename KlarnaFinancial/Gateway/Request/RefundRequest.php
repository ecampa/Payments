<?php
namespace Payments\KlarnaFinancial\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundRequest extends \Payments\KlarnaFinancial\Gateway\Request\AbstractRequest implements BuilderInterface
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
        $request = $this->requestDataBuilder->buildRefundRequestData($payment, $buildSubject['amount']);

        return (array) $request;
    }
}
