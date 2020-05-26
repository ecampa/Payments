<?php
namespace Payments\SecureAcceptance\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class VaultRefundResponseHandler extends \Payments\SecureAcceptance\Gateway\Response\AbstractResponseHandler implements HandlerInterface
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

        $payment->setTransactionId($response[self::TRANSACTION_ID]);
        $payment->setAdditionalInformation(self::TRANSACTION_ID, $response[self::TRANSACTION_ID]);
        $payment->setAdditionalInformation(self::REASON_CODE, $response[self::REASON_CODE]);
        $payment->setAdditionalInformation(self::DECISION, $response[self::DECISION]);
    }
}
