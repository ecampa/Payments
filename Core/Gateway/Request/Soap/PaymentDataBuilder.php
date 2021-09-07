<?php
namespace Payments\Core\Gateway\Request\Soap;

use Magento\Payment\Helper\Formatter;

class PaymentDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    use Formatter;

    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);;

        try {
            $amount = $this->subjectReader->readAmount($buildSubject);
        } catch (\InvalidArgumentException $e) {
            $amount = $paymentDO->getPayment()->getBaseAmountAuthorized();
        }

        return [
            'purchaseTotals' => [
                'currency' => $paymentDO->getOrder()->getCurrencyCode(),
                'grandTotalAmount' => $this->formatPrice($amount)
            ]
        ];
    }
}
