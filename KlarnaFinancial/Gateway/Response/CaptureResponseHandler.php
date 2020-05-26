<?php
namespace Payments\KlarnaFinancial\Gateway\Response;

use Payments\KlarnaFinancial\Gateway\Helper\SubjectReader;
use Payments\KlarnaFinancial\Gateway\Http\Client\SOAPClient;
use Payments\KlarnaFinancial\Gateway\Http\TransferFactory;
use Payments\KlarnaFinancial\Helper\RequestDataBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class CaptureResponseHandler extends \Payments\KlarnaFinancial\Gateway\Response\AbstractResponseHandler implements HandlerInterface
{
    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * AuthorizeResponseHandler constructor.
     * @param RequestDataBuilder $requestDataBuilder
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\KlarnaFinancial\Helper\RequestDataBuilder $requestDataBuilder,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Payments\KlarnaFinancial\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->requestDataBuilder = $requestDataBuilder;

        parent::__construct($subjectReader, $serializer);
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);

        $payment->setTransactionId($response[self::REQUEST_ID]);

        $payment->setIsTransactionClosed(1);
        $payment->setIsTransactionPending(false);
        $payment->setIsFraudDetected(false);
    }
}
