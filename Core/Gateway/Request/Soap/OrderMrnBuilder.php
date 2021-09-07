<?php
namespace Payments\Core\Gateway\Request\Soap;

class OrderMrnBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        return ['merchantReferenceCode' => $paymentDO->getOrder()->getOrderIncrementId()];
    }
}
