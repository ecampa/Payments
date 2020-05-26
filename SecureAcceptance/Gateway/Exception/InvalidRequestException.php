<?php

namespace Payments\SecureAcceptance\Gateway\Exception;

class InvalidRequestException extends \Exception
{
    public $fieldErrors;
    public $links;
}
