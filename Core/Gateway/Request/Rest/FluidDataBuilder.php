<?php
namespace Payments\Core\Gateway\Request\Rest;

class FluidDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{


    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;
    /**
     * @var string|null
     */
    private $additionalInformationKey;

    /**
     * FluidDataBuilder constructor.
     *
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param string|null $additionalDataKey
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader,
        $additionalDataKey = null
    ) {
        $this->subjectReader = $subjectReader;
        $this->additionalInformationKey = $additionalDataKey;
        if ($this->additionalInformationKey === null) {
            throw new \InvalidArgumentException('Additional data key must be provided');
        }
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();

        $fluidDataValue = $payment->getAdditionalInformation($this->additionalInformationKey);

        return [
            'paymentInformation' => [
                'fluidData' => [
                    'value' => $fluidDataValue,
                ]
            ]
        ];
    }
}
