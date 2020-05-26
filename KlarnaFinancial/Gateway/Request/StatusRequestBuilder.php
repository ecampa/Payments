<?php
namespace Payments\KlarnaFinancial\Gateway\Request;


class StatusRequestBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\KlarnaFinancial\Gateway\Helper\SubjectReader
     */
    private $subjectReader;
    /**
     * @var \Payments\KlarnaFinancial\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Payments\KlarnaFinancial\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\KlarnaFinancial\Gateway\Config\Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $request = [
            'merchantID' => $this->config->getMerchantId(),
            'merchantReferenceCode' => $order->getOrderIncrementId(),
            'apPaymentType' => \Payments\KlarnaFinancial\Helper\RequestDataBuilder::PAYMENT_TYPE,
            'apCheckStatusService' => [
                'run' => 'true',
                'checkStatusRequestID' => $paymentDO->getPayment()->getLastTransId(),
            ]
        ];

        return $request;
    }
}
