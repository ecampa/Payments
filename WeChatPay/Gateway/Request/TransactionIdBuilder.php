<?php
namespace Payments\WeChatPay\Gateway\Request;

class TransactionIdBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\WeChatPay\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var string
     */
    private $transactionIdFieldName;

    /**
     * @param \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
     * @param string $transactionIdFieldName
     */
    public function __construct(
        \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader,
        string $transactionIdFieldName
    ) {
        $this->subjectReader = $subjectReader;
        $this->transactionIdFieldName = $transactionIdFieldName;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();
        return [$this->transactionIdFieldName => $payment->getLastTransId()];
    }
}
