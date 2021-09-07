<?php
namespace Payments\WeChatPay\Gateway\Request;

class OrderMrnBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\WeChatPay\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        return ['merchantReferenceCode' => $paymentDO->getOrder()->getOrderIncrementId()];
    }
}
