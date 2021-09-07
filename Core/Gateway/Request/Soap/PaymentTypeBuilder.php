<?php
namespace Payments\Core\Gateway\Request\Soap;

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
