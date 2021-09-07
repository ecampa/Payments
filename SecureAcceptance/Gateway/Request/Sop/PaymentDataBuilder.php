<?php

namespace Payments\SecureAcceptance\Gateway\Request\Sop;

use Magento\Payment\Helper\Formatter;

class PaymentDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    use Formatter;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @param \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Framework\Math\Random $random
    ) {
        $this->subjectReader = $subjectReader;
        $this->random = $random;
    }

    /**
     * Builds Order Data
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        try {
            $amount = $this->subjectReader->readAmount($buildSubject);
        } catch (\InvalidArgumentException $e) {
            // seems we are doing authorization reversal, getting a full authorized amount
            $amount = $paymentDO->getPayment()->getBaseAmountAuthorized();
        }

        return [
            'reference_number' => $order->getOrderIncrementId(),
            'currency' => $paymentDO->getOrder()->getCurrencyCode(),
            'amount' => $this->formatPrice($amount),
            'transaction_uuid' => $this->random->getUniqueHash(),
            \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_ORDER_ID => $order->getId(),
            \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_STORE_ID => $order->getStoreId(),
        ];
    }
}
