<?php
namespace Payments\SecureAcceptance\Model\Jwk;


class Converter implements \Payments\SecureAcceptance\Model\Jwk\ConverterInterface
{

    /**
     * @var \phpseclib\Math\BigIntegerFactory
     */
    private $bigIntegerFactory;

    /**
     * @var \phpseclib\Crypt\RSAFactory
     */
    private $RSAFactory;

    public function __construct(
        \phpseclib\Math\BigIntegerFactory $bigIntegerFactory,
        \phpseclib\Crypt\RSAFactory $RSAFactory
    ) {
        $this->bigIntegerFactory = $bigIntegerFactory;
        $this->RSAFactory = $RSAFactory;
    }

    public function jwkToPem($jwkArray)
    {

        $rsa = $this->RSAFactory->create();

        $exponent = $this->bigIntegerFactory->create([
            'x' => base64_decode($jwkArray['e']),
            'base' => 256,
        ]);

        $modulus = $this->bigIntegerFactory->create([
            'x' => base64_decode(strtr($jwkArray['n'], '-_', '+/'), true),
            'base' => 256,
        ]);

        $rsa->loadKey(['e' => $exponent, 'n' => $modulus,]);

        return $rsa->getPublicKey(\phpseclib\Crypt\RSA::PUBLIC_FORMAT_PKCS8);
    }
}
