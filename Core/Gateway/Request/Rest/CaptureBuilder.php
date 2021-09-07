<?php
namespace Payments\Core\Gateway\Request\Rest;

class CaptureBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    public function build(array $buildSubject)
    {
        return [
            'processingInformation' => [
                'capture' => 'true',
            ]
        ];
    }
}
