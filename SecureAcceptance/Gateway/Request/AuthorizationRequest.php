<?php
namespace Payments\SecureAcceptance\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizationRequest extends \Payments\SecureAcceptance\Gateway\Request\AbstractRequest implements BuilderInterface
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

        if ($this->config->isSilent()) {
            return $payment->getAdditionalInformation();
        }

        return $payment->getAdditionalInformation();
    }
}
