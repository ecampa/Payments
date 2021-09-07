<?php
namespace Payments\ThreeDSecure\Gateway\Validator;

class ProcessorTransactionIdValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{


    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * Validates that received JWT matches previously initialized session
     *
     * @param array $validationSubject
     *
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {

//        return $this->createResult(true, [__('Invalid CCA response')]);

        /** @var \Lcobucci\JWT\Token $response */
        $response = $validationSubject['response'] ?? null;

        if (!$response) {
            return $this->createResult(false, [__('Invalid CCA response')]);
        }

        $payload = $response->claims()->get('Payload');

        if (!$payload) {
            return $this->createResult(false, [__('Invalid CCA response')]);
        }

        $jwtPayment = $payload->Payment ?? null;

        if (!$jwtPayment || !($jwtPayment->ProcessorTransactionId ?? null)) {
            return $this->createResult(false, [__('Invalid CCA response')]);
        }

        $paymentDo = $this->subjectReader->readPayment($validationSubject);
        $payment = $paymentDo->getPayment();

        $transactionId = $payment->getAdditionalInformation(\Payments\ThreeDSecure\Gateway\Validator\PaEnrolledValidator::KEY_PAYER_AUTH_ENROLL_TRANSACTION_ID);

        if ($transactionId !== $jwtPayment->ProcessorTransactionId) {
            return $this->createResult(false, [__('Invalid CCA response')]);
        }

        return $this->createResult(true, []);
    }
}
