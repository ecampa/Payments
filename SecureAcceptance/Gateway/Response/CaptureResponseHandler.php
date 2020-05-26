<?php
namespace Payments\SecureAcceptance\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class CaptureResponseHandler extends \Payments\SecureAcceptance\Gateway\Response\AbstractResponseHandler implements HandlerInterface
{
    const REASON_CODE = "reasonCode";
    const REQUEST_ID = "requestID";

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);

        $payment->setTransactionId($response[self::REQUEST_ID]);

        $payment->setIsTransactionClosed(false);
        $payment->setIsTransactionPending(false);
        $payment->setIsFraudDetected(false);
    }
}
