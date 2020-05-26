<?php
namespace Payments\ThreeDSecure\Gateway\Validator;

/**
 * Class TokenValidator
 */
class TokenValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\ThreeDSecure\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Lcobucci\JWT\Signer\Hmac\Sha256
     */
    private $signer;

    /**
     * @var \Lcobucci\JWT\ValidationData
     */
    private $validationData;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * TokenValidator constructor.
     *
     * @param \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory
     * @param \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
     * @param \Payments\ThreeDSecure\Gateway\Config\Config $config
     * @param \Lcobucci\JWT\Signer\Hmac\Sha256 $signer
     * @param \Lcobucci\JWT\ValidationData $validationData
     */
    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\ThreeDSecure\Gateway\Config\Config $config,
        \Lcobucci\JWT\Signer\Hmac\Sha256 $signer,
        \Lcobucci\JWT\ValidationData $validationData,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->signer = $signer;
        $this->validationData = $validationData;
        $this->dateTime = $dateTime;
    }

    /**
     * Validates JWT token signature and expiration date
     *
     * @param array $validationSubject
     *
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        /** @var \Lcobucci\JWT\Token $token */
        $token = $validationSubject['response'];
        $key = $this->config->getApiKey();
        if (!$token->verify($this->signer, $key)) {
            return $this->createResult(false, ['Invalid JWT token']);
        }
        $this->validationData->setCurrentTime($this->dateTime->gmtTimestamp());
        if (!$token->validate($this->validationData)) {
            return $this->createResult(false, ['JWT token has expired']);
        }
        return $this->createResult(true);
    }
}
