<?php
namespace Payments\SecureAcceptance\Gateway\Validator\Sop;

class SignatureValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface
     */
    private $configProvider;

    /**
     * @var \Payments\SecureAcceptance\Model\SignatureManagementInterface
     */
    private $signatureManagement;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Model\SignatureManagementInterface $signatureManagement,
        \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface $configProvider,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);

        $this->signatureManagement = $signatureManagement;
        $this->configProvider = $configProvider;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Performs SOP/WM response signature validation
     *
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        $transactionKey = $this->configProvider->getSecretKey($this->getStoreId($response));

        if (!$this->signatureManagement->validateSignature($response, $transactionKey)) {
            return $this->createResult(false, [__('Invalid Signature')]);
        }

        return $this->createResult(true);
    }

    private function getStoreId($response)
    {
        return $response['req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_STORE_ID] ?? null;
    }
}
