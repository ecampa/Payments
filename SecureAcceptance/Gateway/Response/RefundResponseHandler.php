<?php
namespace Payments\SecureAcceptance\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class RefundResponseHandler extends \Payments\SecureAcceptance\Gateway\Response\AbstractResponseHandler implements HandlerInterface
{
    const CONFIRMATION_NUMBER = 'confirmationNumber';
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
        $payment->setShouldCloseParentTransaction(!$payment->getCreditmemo()->getInvoice()->canRefund());
    }
}
