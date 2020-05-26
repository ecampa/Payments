<?php
namespace Payments\VisaCheckout\Gateway\Response;

use Payments\VisaCheckout\Gateway\Helper\SubjectReader;
use Payments\VisaCheckout\Gateway\Http\Client\SOAPClient;
use Payments\VisaCheckout\Gateway\Http\TransferFactory;
use Payments\VisaCheckout\Helper\RequestDataBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class AuthorizeResponseHandler extends \Payments\VisaCheckout\Gateway\Response\AbstractResponseHandler implements HandlerInterface
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
        \Payments\VisaCheckout\Helper\RequestDataBuilder $requestDataBuilder,
        \Payments\VisaCheckout\Gateway\Http\Client\SOAPClient $SOAPClient,
        \Payments\VisaCheckout\Gateway\Http\TransferFactory $transferFactory,
        \Payments\VisaCheckout\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->requestDataBuilder = $requestDataBuilder;
        $this->soapClient = $SOAPClient;
        $this->transferFactory = $transferFactory;

        parent::__construct($subjectReader);
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

        $payment->setIsTransactionClosed(false);
    }
}
