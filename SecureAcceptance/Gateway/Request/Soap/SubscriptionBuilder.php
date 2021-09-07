<?php

namespace Payments\SecureAcceptance\Gateway\Request\Soap;

class SubscriptionBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
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
     * Builds Subscription data request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $token = $this->paymentTokenManagement->getTokenFromPayment($payment);

        if (!$token) {
            throw new \InvalidArgumentException('Subscription Id must be provided');
        }

        return [
            'recurringSubscriptionInfo' => [
                'subscriptionID' => $token,
            ],
            'subsequentAuthFirst' => 'true'
        ];
    }
}
