<?php
namespace Payments\ApplePay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundRequest extends \Payments\ApplePay\Gateway\Request\AbstractRequest implements BuilderInterface
{
    const TRANSACTION_TYPE = "34";

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->getValidPaymentInstance($buildSubject);
        $request = $this->requestDataBuilder->buildRefundRequestData($payment, $buildSubject['amount'], $buildSubject);

        return (array) $request;
    }
}
