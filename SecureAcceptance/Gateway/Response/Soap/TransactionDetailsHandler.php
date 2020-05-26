<?php

namespace Payments\SecureAcceptance\Gateway\Response\Soap;

class TransactionDetailsHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    const KEY_TRANSACTION_ID = 'requestID';

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var bool
     */
    private $shouldCloseTransaction;

    /**
     * @var bool
     */
    private $shouldCloseParentTransaction;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        bool $shouldCloseTransaction = null,
        bool $shouldCloseParentTransaction = null
    ) {
        $this->subjectReader = $subjectReader;
        $this->shouldCloseTransaction = $shouldCloseTransaction;
        $this->shouldCloseParentTransaction = $shouldCloseParentTransaction;
    }

    /**
     * Handles transaction details
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof \Magento\Sales\Model\Order\Payment) {
            return;
        }

        $payment->setTransactionId($response[self::KEY_TRANSACTION_ID]);
        $payment->setCcTransId($response[self::KEY_TRANSACTION_ID]);
        if ($this->shouldCloseTransaction() !== null) {
            $payment->setIsTransactionClosed($this->shouldCloseTransaction());
        }
        if ($this->shouldCloseParentTransaction() !== null) {
            $payment->setShouldCloseParentTransaction($this->shouldCloseParentTransaction());
        }
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return $this->shouldCloseTransaction;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseParentTransaction()
    {
        return $this->shouldCloseParentTransaction;
    }
}
