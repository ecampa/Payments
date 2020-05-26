<?php
namespace Payments\ThreeDSecure\Gateway;

class PaEnrolledException extends \Magento\Framework\Webapi\Exception
{

    public function __construct(
        \Magento\Framework\Phrase $phrase,
        int $httpCode = self::HTTP_BAD_REQUEST,
        array $details = [],
        string $name = '',
        array $errors = null,
        string $stackTrace = null
    ) {
        parent::__construct($phrase, 475, $httpCode, $details, $name, $errors, $stackTrace);
    }
}
