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
     * @param ResultInterfaceFactory $resultFactory
     * @param \Payments\SecureAcceptance\Gateway\Config\Config $config
     * @param SubjectReader $subjectReader
     * @param bool $isAdminHtml
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Gateway\Validator\Flex\SignatureValidator\ValidatorInterface $signatureValidator,
        $isAdminHtml = false
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->isAdminHtml = $isAdminHtml;
        $this->signatureValidator = $signatureValidator;
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

        $microformPublicKey = $payment->getAdditionalInformation('microformPublicKey');
        $microformResponseData = $payment->getAdditionalInformation();

        $isValid = $this->verifyToken($microformPublicKey, $microformResponseData);

        return $this->createResult(
            $isValid,
            $isValid ? [] : ['Invalid token signature.']
        );
    }

    /**
     * @param string $publicKey
     * @param array $microformResponseData
     * @return bool
     */
    private function verifyToken($publicKey, $microformResponseData)
    {
        $dataArray = [];

        $signedFields = $microformResponseData[\Payments\SecureAcceptance\Observer\DataAssignObserver::KEY_FLEX_SIGNED_FIELDS] ?? null;

        if (!$signedFields) {
            throw new \InvalidArgumentException('Signature is missing.');
        }

        $signedFields = explode(',', $signedFields);

        $signature = $microformResponseData[\Payments\SecureAcceptance\Observer\DataAssignObserver::KEY_FLEX_SIGNATURE] ?? null;

        if (!$signature) {
            throw new \InvalidArgumentException('Signature is missing.');
        }

        foreach ($signedFields as $v) {
            $dataArray[] = $microformResponseData[$v] ?? null;
        }

        $dataString = implode(',', $dataArray);

        return $this->signatureValidator->validate($dataString, $signature, $publicKey, "sha512");
    }
}
