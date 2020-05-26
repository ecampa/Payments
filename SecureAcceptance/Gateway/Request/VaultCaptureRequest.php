<?php

namespace Payments\SecureAcceptance\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class VaultCaptureRequest extends \Payments\SecureAcceptance\Gateway\Request\AbstractRequest implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $this->vaultHelper->unsVaultEnabled();

        $payment = $this->getValidPaymentInstance($buildSubject);
        $request = $this->requestDataBuilder->buildCaptureRequest($payment, $buildSubject['amount']);

        return (array) $request;
    }
}
