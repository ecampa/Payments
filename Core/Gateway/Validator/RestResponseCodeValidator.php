<?php
namespace Payments\Core\Gateway\Validator;


class RestResponseCodeValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{

    const RESPONSE_CODE_OK = 200;
    const RESPONSE_CODE_NOT_FOUND = 404;
    const RESPONSE_CODE_UNAUTHORIZED = 401;
    const RESPONSE_CODE_BAD_REQUEST = 400;

    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject)
    {

        $result = $this->subjectReader->readResponse($validationSubject);

        $httpCode = $result['http_code'] ?? static::RESPONSE_CODE_BAD_REQUEST;

        if ($httpCode == static::RESPONSE_CODE_NOT_FOUND) {
            throw new \Payments\Core\Gateway\Validator\NotFoundException('No data found.');
        }

        if ($httpCode != self::RESPONSE_CODE_OK) {
            return $this->createResult(false, ['REST API returned invalid response code: ' . $httpCode]);
        }

        return $this->createResult(true);
    }
}
