<?php
namespace Payments\SecureAcceptance\Model\Jwk;


interface ConverterInterface
{

    public function jwkToPem($jwkArray);

}
