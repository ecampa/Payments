<?php
namespace Payments\ApplePay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class CaptureRequest extends \Payments\ApplePay\Gateway\Request\AbstractRequest implements BuilderInterface
{
    /**
     * Builds request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $request = $this->requestDataBuilder->buildCaptureRequestData(
            $this->getValidPaymentInstance($buildSubject),
            $this->subjectReader->readAmount($buildSubject)
        );

        return (array) $request;
    }
}
