<?php
namespace Payments\ApplePay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizeRequest extends \Payments\ApplePay\Gateway\Request\AbstractRequest implements BuilderInterface
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->getValidPaymentInstance($buildSubject);
        $request = $this->requestDataBuilder->buildAuthorizationRequestData($payment);

        return (array) $request;
    }
}
