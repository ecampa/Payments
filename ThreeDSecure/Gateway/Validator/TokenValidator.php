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
     * @var \Lcobucci\JWT\Parser
     */
    private $parser;

    /**
     * @var \Lcobucci\JWT\Validation\Validator
     */
    private $validator;

    /**
     * @var \Lcobucci\JWT\Validation\Constraint\SignedWithFactory
     */
    private $signedWithConstraintFactory;

    /**
     * @var \Lcobucci\JWT\Signer\KeyFactory
     */
    private $keyFactory;

    /**
     * @var \Lcobucci\JWT\Validation\Constraint\ValidAtFactory
     */
    private $validAtFactory;

    /**
     * @var \Lcobucci\Clock\FrozenClockFactory
     */
    private $clockFactory;

    /**
     * @var \DateTimeImmutableFactory
     */
    private $dateTimeImmutableFactory;

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
     * @param \Lcobucci\JWT\Parser $parser
     * @param \Lcobucci\JWT\Validation\Validator $validator
     * @param \Lcobucci\JWT\Validation\Constraint\SignedWithFactory $signedWithConstraintFactory
     * @param \Lcobucci\JWT\Validation\Constraint\ValidAtFactory $validAtFactory
     * @param \Lcobucci\JWT\Signer\KeyFactory $keyFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\ThreeDSecure\Gateway\Config\Config $config,
        \Lcobucci\JWT\Signer\Hmac\Sha256 $signer,
        \Lcobucci\JWT\Parser $parser,
        \Lcobucci\JWT\Validation\Validator $validator,
        \Lcobucci\JWT\Validation\Constraint\SignedWithFactory $signedWithConstraintFactory,
        \Lcobucci\JWT\Validation\Constraint\ValidAtFactory $validAtFactory,
        \Lcobucci\Clock\FrozenClockFactory $clockFactory,
        \DateTimeImmutableFactory $dateTimeImmutableFactory,
        \Lcobucci\JWT\Signer\KeyFactory $keyFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->signer = $signer;
        $this->parser = $parser;
        $this->validator = $validator;
        $this->signedWithConstraintFactory = $signedWithConstraintFactory;
        $this->validAtFactory = $validAtFactory;
        $this->clockFactory = $clockFactory;
        $this->dateTimeImmutableFactory = $dateTimeImmutableFactory;
        $this->keyFactory = $keyFactory;
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
        if (!$this->validateSignature($token, $this->config->getApiKey())) {
            return $this->createResult(false, ['Invalid JWT token']);
        }
        if (!$this->validateExpiration($token, $this->dateTime->gmtTimestamp())) {
            return $this->createResult(false, ['JWT token has expired']);
        }
        return $this->createResult(true);
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     * @param string $key
     *
     * @return bool
     */
    private function validateSignature($token, $key)
    {
        return $this->validator->validate(
            $token,
            $this->signedWithConstraintFactory->create(
                [
                    'signer' => $this->signer,
                    'key' => $this->keyFactory->create(['content' => $key]),
                ]
            )
        );
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     * @param $currentDate
     *
     * @return bool
     */
    private function validateExpiration($token, $currentDate)
    {
        return $this->validator->validate(
            $token,
            $this->validAtFactory->create(
                [
                    'clock' => $this->clockFactory->create(
                        ['now' => $this->dateTimeImmutableFactory->create()->setTimestamp($currentDate)]
                    ),
                ]
            )
        );
    }
}
