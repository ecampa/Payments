<?php
namespace Payments\ApplePay\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class CaptureResponseHandler extends \Payments\ApplePay\Gateway\Response\AbstractResponseHandler implements HandlerInterface
{
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
        $payment->setCcTransId($response[self::REQUEST_ID]);
        $payment->setAdditionalInformation(self::REQUEST_ID, $response[self::REQUEST_ID]);
        $payment->setAdditionalInformation(self::REASON_CODE, $response[self::REASON_CODE]);
        $payment->setAdditionalInformation(self::DECISION, $response[self::DECISION]);
        $payment->setIsTransactionClosed(1);
        $payment->setIsTransactionPending(false);
        $payment->setIsFraudDetected(false);
        $payment->getOrder()->setStatus(Order::STATE_PROCESSING);
        $payment->getOrder()->setState(Order::STATE_PROCESSING);
    }
}
