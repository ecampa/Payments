<?php
namespace Payments\Core\Gateway\Response\Rest;

use Magento\Payment\Gateway\Response\HandlerInterface;

class CaptureResponseHandler implements HandlerInterface
{
    const REASON_CODE = "reasonCode";
    const REQUEST_ID = "id";

    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;


    public function __construct(\Payments\Core\Gateway\Helper\SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->subjectReader->readPayment($handlingSubject)->getPayment();

        $payment->setTransactionId($response[self::REQUEST_ID]);

        $payment->setIsTransactionClosed(false);
        $payment->setIsTransactionPending(false);
        $payment->setIsFraudDetected(false);
    }
}
