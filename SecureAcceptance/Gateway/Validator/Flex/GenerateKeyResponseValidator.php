<?php

namespace Payments\SecureAcceptance\Gateway\Validator\Flex;

use Payments\SecureAcceptance\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class GenerateKeyResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Model\Jwt\JwtProcessorInterface
     */
    private $jwtProcessor;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Model\Jwt\JwtProcessorInterface $jwtProcessor,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->jwtProcessor = $jwtProcessor;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        $jwt = $response['keyId'] ?? null;

        if (!$jwt) {
            return $this->createResult(false, [__('JWT is empty.')]);
        }

        //TODO: determine how to validate received jwt token
//        $publicKey = $this->jwtProcessor->getPublicKey($jwt);
//
//        $isValidSignature = $this->jwtProcessor->verifySignature($jwt, $publicKey);

        return $this->createResult(true);
    }
}
