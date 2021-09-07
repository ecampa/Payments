<?php

namespace Payments\SecureAcceptance\Gateway\Validator\Flex;

use Payments\SecureAcceptance\Gateway\Helper\SubjectReader;
use Payments\SecureAcceptance\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class MicroformResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var bool
     */
    private $isAdminHtml;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Validator\Flex\SignatureValidator\ValidatorInterface
     */
    private $signatureValidator;

    /**
     * @var \Payments\SecureAcceptance\Model\Jwt\JwtProcessorInterface
     */
    private $jwtProcessor;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param \Payments\SecureAcceptance\Gateway\Config\Config $config
     * @param SubjectReader $subjectReader
     * @param bool $isAdminHtml
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Model\Jwt\JwtProcessorInterface $jwtProcessor,
        \Payments\SecureAcceptance\Gateway\Validator\Flex\SignatureValidator\ValidatorInterface $signatureValidator,
        $isAdminHtml = false
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->isAdminHtml = $isAdminHtml;
        $this->signatureValidator = $signatureValidator;
        $this->jwtProcessor = $jwtProcessor;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {

        $payment = $validationSubject['payment'] ?? null;

        if ($payment && $payment->getMethod() != \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE) {
            return $this->createResult(
                true
            );
        }

        if (!$this->config->isMicroform() || $this->isAdminHtml) {
            return $this->createResult(
                true
            );
        }

        if (!$payment) {
            return $this->createResult(
                false,
                ['Payment must be provided.']
            );
        }

        if ($payment instanceof \Magento\Quote\Model\Quote\Payment) {
            //we are validating this only for order
            return $this->createResult(
                true
            );
        }

        $jwt = $payment->getAdditionalInformation('flexJwt');
        $microformPublicKey = $payment->getAdditionalInformation('microformPublicKey');;

        $isValid = $this->jwtProcessor->verifySignature($jwt, $microformPublicKey);

        return $this->createResult(
            $isValid,
            $isValid ? [] : ['Invalid token signature.']
        );
    }
}
