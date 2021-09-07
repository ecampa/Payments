<?php
namespace Payments\Core\Gateway\Request\Rest;


class OperationBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var string
     */
    private $operationName;

    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * OperationBuilder constructor.
     *
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param string $operationName
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader,
        $operationName
    ) {
        $this->subjectReader = $subjectReader;
        $this->operationName = $operationName;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $id = $payment->getParentTransactionId() ?: $payment->getRefundTransactionId();

        return [
            \Payments\Core\Gateway\Http\Client\Rest::KEY_URL_PARAMS => [
                $id,
                $this->operationName
            ]
        ];
    }
}
