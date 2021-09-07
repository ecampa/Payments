<?php
namespace Payments\WeChatPay\Gateway\Request;

class ParentTransactionIdBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\WeChatPay\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var string
     */
    private $parentTransactionIdFieldName;

    /**
     * @param \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
     * @param string $parentTransactionIdFieldName
     */
    public function __construct(
        \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader,
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
