<?php
namespace Payments\SecureAcceptance\Gateway\Response\Sop;


use Payments\SecureAcceptance\Gateway\Validator\ResponseCodeValidator;

class FailsHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    private $fieldsToRecord = [
        'message',
        'required_fields',
    ];

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {

        $responseCode = $response[\Payments\SecureAcceptance\Gateway\Validator\ResponseCodeValidator::RESULT_CODE] ?? null;

        if (in_array(
            $responseCode,
            [
                \Payments\SecureAcceptance\Gateway\Validator\ResponseCodeValidator::APPROVED,
                \Payments\SecureAcceptance\Gateway\Validator\ResponseCodeValidator::DM_REVIEW,
            ]
        )) {
            return;
        }

        $paymentDo = $this->subjectReader->readPayment($handlingSubject);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $paymentDo->getPayment()->getOrder();

        $detailsToAdd = [__('An error occurred during Secure Acceptance transaction:')];

        foreach ($this->fieldsToRecord as $fieldKey) {
            if (!$fieldValue = $response[$fieldKey] ?? null) {
                continue;
            }
            $detailsToAdd[] = $fieldKey . ': ' . $fieldValue;
        }

        if (!method_exists($order, 'addCommentToStatusHistory')) {
            $order->addStatusHistoryComment(implode("\n", $detailsToAdd));
            return;
        }

        $order->addCommentToStatusHistory(implode("\n", $detailsToAdd));

    }
}
