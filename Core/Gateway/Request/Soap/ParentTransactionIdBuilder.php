<?php
namespace Payments\Core\Gateway\Request\Soap;

class ParentTransactionIdBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var string
     */
    private $parentTransactionIdFieldName;

    /**
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param string $parentTransactionIdFieldName
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader,
        string $parentTransactionIdFieldName
    ) {
        $this->subjectReader = $subjectReader;
        $this->parentTransactionIdFieldName = $parentTransactionIdFieldName;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();
        $parentTransactionId = $payment->getParentTransactionId() ?: $payment->getRefundTransactionId();
        return [$this->parentTransactionIdFieldName => $parentTransactionId];
    }
}
