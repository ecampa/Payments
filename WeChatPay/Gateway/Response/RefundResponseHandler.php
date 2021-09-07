<?php
namespace Payments\WeChatPay\Gateway\Response;

class RefundResponseHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    /**
     * @var \Payments\WeChatPay\Gateway\Helper\SubjectReader
     */
    protected $subjectReader;

    public function __construct(
        \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
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
