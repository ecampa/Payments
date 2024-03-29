<?php
namespace Payments\WeChatPay\Gateway\Response;

class TransactionDetailsHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var string
     */
    private $replySectionName;

    /**
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param string $replySectionName
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader,
        string $replySectionName
    ) {
        $this->subjectReader = $subjectReader;
        $this->replySectionName = $replySectionName;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->subjectReader->readPayment($handlingSubject)->getPayment();
        $payment->setTransactionId($response['requestID']);

        if ($reconciliationId = $response[$this->replySectionName]->reconciliationID ?? false) {
            $payment->setAdditionalInformation('reconciliationID', $reconciliationId);
        }

        if ($merchantUrl = $response[$this->replySectionName]->merchantURL ?? false) {
            $payment->setAdditionalInformation('qrCodeUrl', $merchantUrl);
        }
    }
}
