<?php
namespace Payments\SecureAcceptance\Gateway\Validator\Sop;

class QuoteValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
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
     *
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);
        $paymentDO = $this->subjectReader->readPayment($validationSubject);

        $responseQuoteId = $response['req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_QUOTE_ID] ?? null;

        $isValid = true;
        $errorMessages = [];

        if (is_null($paymentDO->getOrder()->getId()) || $paymentDO->getOrder()->getId() != $responseQuoteId) {
            $isValid = false;
            $errorMessages[] = __('Incorrect Quote ID');
        }

        return $this->createResult($isValid, $errorMessages);
    }
}
