<?php

namespace Payments\ThreeDSecure\Gateway\Request\Cca;

class PayerAuthEnrollBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    const TRANSACTION_MODE_ECOMMERCE = 'S';
    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;
    /**
     * @var \Payments\SecureAcceptance\Model\PaymentTokenManagement
     */
    private $paymentTokenManagement;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;


    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Model\PaymentTokenManagement $paymentTokenManagement,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->request = $request;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        if (!$referenceId = $payment->getAdditionalInformation(
            \Payments\ThreeDSecure\Gateway\Command\Cca\CreateToken::KEY_PAYER_AUTH_ENROLL_REFERENCE_ID
        )
        ) {
            throw new \InvalidArgumentException('3D Secure initialization is required. Reload the page and try again.');
        }

        $result = [
            'payerAuthEnrollService' => [
                'run' => 'true',
                'mobilePhone' => $order->getBillingAddress()->getTelephone() ?? '',
                'referenceID' => $referenceId,
                'transactionMode' => self::TRANSACTION_MODE_ECOMMERCE,
                'httpAccept' => $this->request->getHeader('accept'),
                'httpUserAgent' => $this->request->getHeader('user-agent')
            ],
        ];

        return $result;
    }
}
