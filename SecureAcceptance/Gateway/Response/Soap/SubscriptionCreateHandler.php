<?php
namespace Payments\SecureAcceptance\Gateway\Response\Soap;


class SubscriptionCreateHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Model\PaymentTokenManagement
     */
    private $paymentTokenManagement;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Model\PaymentTokenManagement $paymentTokenManagement
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDo = $this->subjectReader->readPayment($handlingSubject);

        $paySubscriptionCreateReply = $response['paySubscriptionCreateReply'] ?? null;

        if (!$paySubscriptionCreateReply) {
            return;
        }

        if ($token = $paySubscriptionCreateReply->subscriptionID ?? null) {
            $this->paymentTokenManagement->storeTokenIntoPayment($paymentDo->getPayment(), $token);
        }

        if ($instrumentId = $paySubscriptionCreateReply->instrumentIdentifierID ?? null) {
            $this->paymentTokenManagement->storeInstrumentIdIntoPayment($paymentDo->getPayment(), $instrumentId);
        }
    }
}
