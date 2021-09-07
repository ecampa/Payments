<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;


class PaBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    public function build(array $buildSubject)
    {
        return [
            'payer_auth_enroll_service_run' => 'true',
        ];
    }
}
