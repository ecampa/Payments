<?php

namespace Payments\ThreeDSecure\Gateway\Validator;

use Payments\ThreeDSecure\Gateway\PaEnrolledException;

class PaEnrolledValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{

    const CODE_PA_ENROLLED = 475;

    const KEY_ACS_URL = 'acsURL';
    const KEY_PA_REQ = 'paReq';
    const KEY_PAYER_AUTH_ENROLL_REPLY = 'payerAuthEnrollReply';
    const KEY_AUTHENTICATION_TRANSACTION_ID = 'authenticationTransactionID';
    const KEY_PAYER_AUTH_ENROLL_TRANSACTION_ID = 'payer_auth_enroll_transaction_id';

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface
     */
    private $builder;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Payment\Gateway\Request\BuilderInterface $builder
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->builder = $builder;
    }

    /**
     * Handles response code 475 for PA enrolled cards
     *
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     * @throws PaEnrolledException
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);
        $code = $response[\Payments\SecureAcceptance\Gateway\Validator\SoapReasonCodeValidator::RESULT_CODE] ?? null;

        if ($code !== self::CODE_PA_ENROLLED) {
            return $this->createResult(true);
        }

        $payerAuthEnrollReply = (array)$response[self::KEY_PAYER_AUTH_ENROLL_REPLY];

        throw new \Payments\ThreeDSecure\Gateway\PaEnrolledException(
            __('Payer Authentication is required.'),
            \Payments\ThreeDSecure\Gateway\PaEnrolledException::HTTP_BAD_REQUEST,
            [
                'cca' => [
                    'AcsUrl' => $payerAuthEnrollReply[self::KEY_ACS_URL],
                    'Payload' => $payerAuthEnrollReply[self::KEY_PA_REQ]
                ],
                'order' => array_replace_recursive(
                    [
                        'OrderDetails' => [
                            'TransactionId' => $payerAuthEnrollReply[self::KEY_AUTHENTICATION_TRANSACTION_ID],
                        ]
                    ],
                    $this->builder->build($validationSubject)
                ),
            ]
        );
    }
}
