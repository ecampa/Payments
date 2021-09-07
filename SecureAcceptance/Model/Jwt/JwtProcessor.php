<?php
namespace Payments\SecureAcceptance\Model\Jwt;


class JwtProcessor implements \Payments\SecureAcceptance\Model\Jwt\JwtProcessorInterface
{

    /**
     * @var \Lcobucci\JWT\Parser
     */
    private $parser;

    /**
     * @var \Payments\SecureAcceptance\Model\Jwk\ConverterInterface
     */
    private $jwkConverter;

    /**
     * @var \Lcobucci\JWT\Signer\Rsa\Sha256Factory
     */
    private $sha256Factory;

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

    public function __construct(
        \Lcobucci\JWT\Parser $parser,
        \Payments\SecureAcceptance\Model\Jwk\ConverterInterface $jwkConverter,
        \Lcobucci\JWT\Signer\Rsa\Sha256Factory $sha256Factory,
        \Lcobucci\JWT\Validation\Validator $validator,
        \Lcobucci\JWT\Validation\Constraint\SignedWithFactory $signedWithConstraintFactory,
        \Lcobucci\JWT\Signer\KeyFactory $keyFactory
    ) {
        $this->parser = $parser;
        $this->jwkConverter = $jwkConverter;
        $this->sha256Factory = $sha256Factory;
        $this->validator = $validator;
        $this->signedWithConstraintFactory = $signedWithConstraintFactory;
        $this->keyFactory = $keyFactory;
    }


    private function parse($jwtString)
    {
        return $this->parser->parse($jwtString);
    }

    public function getFlexPaymentToken($jwtString)
    {
        $token = $this->parse($jwtString);

        return $this->getClaim($token, 'jti');
    }

    public function getCardData($jwtString)
    {
        $token = $this->parse($jwtString);

        return (array)$this->getClaim($token, 'data');
    }

    public function getPublicKey($jwtString)
    {

        $token = $this->parse($jwtString);

        $flx = $this->getClaim($token, 'flx');

        $jwk = (array)$flx->jwk;

        return $this->jwkConverter->jwkToPem($jwk);
    }

    public function verifySignature($jwtString, $key)
    {
        $token = $this->parser->parse($jwtString);

        return $this->validator->validate(
            $token,
            $this->signedWithConstraintFactory->create(
                [
                    'signer' => $this->sha256Factory->create(),
                    'key' => $this->keyFactory->create(['content' => $key]),
                ]
            )
        );
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     * @param $name
     */
    private function getClaim($token, $name)
    {
        /** @var \Lcobucci\JWT\Token\DataSet $claims */
        $claims = $token->claims();

        return $claims->get($name);
    }
}
