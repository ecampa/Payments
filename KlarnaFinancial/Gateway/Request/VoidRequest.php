<?php
namespace Payments\KlarnaFinancial\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class VoidRequest extends \Payments\KlarnaFinancial\Gateway\Request\AbstractRequest implements BuilderInterface
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
        $request = $this->requestDataBuilder->buildVoidRequestData($payment);

        return (array) $request;
    }
}
