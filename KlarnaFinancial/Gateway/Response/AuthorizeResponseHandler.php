<?php
namespace Payments\KlarnaFinancial\Gateway\Response;

use Payments\KlarnaFinancial\Gateway\Helper\SubjectReader;
use Payments\KlarnaFinancial\Gateway\Http\Client\SOAPClient;
use Payments\KlarnaFinancial\Gateway\Http\TransferFactory;
use Payments\KlarnaFinancial\Helper\RequestDataBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class AuthorizeResponseHandler extends \Payments\KlarnaFinancial\Gateway\Response\AbstractResponseHandler implements HandlerInterface
{
    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var SOAPClient
     */
    private $soapClient;

    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * AuthorizeResponseHandler constructor.
     * @param RequestDataBuilder $requestDataBuilder
     * @param SOAPClient $SOAPClient
     * @param TransferFactory $transferFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\KlarnaFinancial\Helper\RequestDataBuilder $requestDataBuilder,
        \Payments\KlarnaFinancial\Gateway\Http\Client\SOAPClient $SOAPClient,
        \Payments\KlarnaFinancial\Gateway\Http\TransferFactory $transferFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Payments\KlarnaFinancial\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->requestDataBuilder = $requestDataBuilder;
        $this->soapClient = $SOAPClient;
        $this->transferFactory = $transferFactory;

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
        $payment = $this->handleAuthorizeResponse($payment, $response);

        $merchantUrl = $response['apAuthReply']->merchantURL ?? null;
        $payment->setAdditionalInformation('merchantUrl', $merchantUrl);

        $apStatus = $response['apAuthReply']->paymentStatus ?? '';

        $payment->setIsTransactionPending(strtolower($apStatus) == static::PAYMENT_STATUS_PENDING);
        $payment->setIsTransactionClosed(false);
    }
}
