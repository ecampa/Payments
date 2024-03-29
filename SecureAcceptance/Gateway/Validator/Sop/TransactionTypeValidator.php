<?php
namespace Payments\SecureAcceptance\Gateway\Validator\Sop;

use Payments\SecureAcceptance\Gateway\Request\Sop\CreateTokenRequest;

class TransactionTypeValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * Performs SOP/WM request transaction type
     *
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        $responseTransactionType = $response['req_transaction_type'] ?? null;

        $isValid = true;
        $errorMessages = [];

        if ($responseTransactionType != \Payments\SecureAcceptance\Gateway\Request\Sop\CreateTokenRequest::TYPE_CREATE_TOKEN) {
            $isValid = false;
            $errorMessages[] = __('Invalid Transaction Type');
        }

        return $this->createResult($isValid, $errorMessages);
    }
}
