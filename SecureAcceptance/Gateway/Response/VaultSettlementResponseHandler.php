<?php

namespace Payments\SecureAcceptance\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class VaultSettlementResponseHandler extends \Payments\SecureAcceptance\Gateway\Response\AbstractResponseHandler implements HandlerInterface
{
    /**
     * Handles fraud messages
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);

        $payment->setTransactionId($response[self::TRANSACTION_ID]);
        $payment->setCcTransId($response[self::TRANSACTION_ID]);
        $payment->setAdditionalInformation(self::TRANSACTION_ID, $response[self::TRANSACTION_ID]);
        $payment->setAdditionalInformation(self::REASON_CODE, $response[self::REASON_CODE]);
        $payment->setAdditionalInformation(self::DECISION, $response[self::DECISION]);
        $payment->setAdditionalInformation(self::MERCHANT_REFERENCE_CODE, $response[self::MERCHANT_REFERENCE_CODE]);

        if ($response[self::REASON_CODE] === "100") {
            $payment->setIsFraudDetected(false);
            $payment->setIsTransactionPending(false);
        } else {
            $payment->setIsFraudDetected(true);
            $payment->setIsTransactionPending(true);
        }

        $payment->setShouldCloseParentTransaction(false);
    }
}
