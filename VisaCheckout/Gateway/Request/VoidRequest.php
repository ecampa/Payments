<?php
namespace Payments\VisaCheckout\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;

class VoidRequest extends \Payments\VisaCheckout\Gateway\Request\AbstractRequest implements BuilderInterface
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->getValidPaymentInstance($buildSubject);

        $request = $this->requestDataBuilder->buildAuthorizeReversalRequestData($payment);

        return (array)$request;
    }
}
