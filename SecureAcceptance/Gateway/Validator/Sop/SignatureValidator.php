<?php
namespace Payments\SecureAcceptance\Gateway\Validator\Sop;

class SignatureValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{

    /**
     * @var \Payments\SecureAcceptance\Helper\RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $requestDataBuilder,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);

        $this->requestDataBuilder = $requestDataBuilder;
        $this->config = $config;
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

        $transactionKey = $this->config->getAuthSecretKey();

        if ($this->config->isSilent()) {
            $transactionKey = $this->config->getSopSecretKey();
        }

        if (!$this->requestDataBuilder->validateSignature($response, $transactionKey)) {
            return $this->createResult(false, [__('Invalid Signature')]);
        }

        return $this->createResult(true);
    }
}
