<?php
namespace Payments\SecureAcceptance\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class AuthorizeResponseHandler extends \Payments\SecureAcceptance\Gateway\Response\AbstractResponseHandler implements HandlerInterface
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
        $payment = $this->handleAuthorizeResponse($payment, $response);

        $payment->setIsTransactionClosed(false);
    }
}
