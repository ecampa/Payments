<?php
namespace Payments\PayPal\Gateway\Request\Soap;


class OrderRequestIdBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(\Payments\Core\Gateway\Helper\SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }


    public function build(array $buildSubject)
    {
        $paymentDo = $this->subjectReader->readPayment($buildSubject);

        if (!$orderRequestId = $paymentDo->getPayment()->getAdditionalInformation(\Payments\PayPal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_ORDER_SETUP_TXN_ID)) {
            throw new\InvalidArgumentException('Order setup transaction Id must be provided.');
        }

        return [
            'orderRequestID' => $orderRequestId,
        ];
    }
}
