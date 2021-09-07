<?php
namespace Payments\WeChatPay\Gateway\Request;

class PaymentTypeBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var string
     */
    private $paymentCode;

    /**
     * @param string $paymentCode
     */
    public function __construct(string $paymentCode)
    {
        $this->paymentCode = $paymentCode;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return ['apPaymentType' => $this->paymentCode];
    }
}
