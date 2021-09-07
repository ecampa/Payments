<?php
namespace Payments\Core\Gateway\Response\Soap;

class RefundResponseHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    protected $subjectReader;

    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->subjectReader->readPayment($handlingSubject)->getPayment();
        $payment->setTransactionId($response['requestID']);
        $payment->setShouldCloseParentTransaction(!$payment->getCreditmemo()->getInvoice()->canRefund());
    }
}
