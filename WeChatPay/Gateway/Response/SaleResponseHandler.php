<?php
namespace Payments\WeChatPay\Gateway\Response;

class SaleResponseHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    protected $subjectReader;

    /**
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     */
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
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);
    }
}
