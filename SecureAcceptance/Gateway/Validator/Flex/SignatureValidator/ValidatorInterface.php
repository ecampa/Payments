<?php
namespace Payments\SecureAcceptance\Gateway\Validator\Flex\SignatureValidator;


interface ValidatorInterface
{

    /**
     * @param $dataString
     * @param $signature
     * @param $publicKey
     * @param $algorithm
     *
     * @return bool
     */
    public function validate($dataString, $signature, $publicKey, $algorithm);

}
