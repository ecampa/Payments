<?php
namespace Payments\SecureAcceptance\Model\Jwt;


interface JwtProcessorInterface
{

    public function getFlexPaymentToken($jwtString);

    public function getCardData($jwtString);

    public function verifySignature($jwtString, $key);

    public function getPublicKey($jwtString);

}
