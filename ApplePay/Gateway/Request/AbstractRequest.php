<?php
namespace Payments\ApplePay\Gateway\Request;

use Payments\ApplePay\Gateway\Config\Config;
use Payments\ApplePay\Gateway\Helper\SubjectReader;
use Payments\ApplePay\Helper\RequestDataBuilder;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;

abstract class AbstractRequest
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var RequestDataBuilder
     */
    protected $requestDataBuilder;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    public function __construct(
        \Payments\ApplePay\Gateway\Config\Config $config,
        \Payments\ApplePay\Helper\RequestDataBuilder $requestDataBuilder,
        \Payments\ApplePay\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->subjectReader = $subjectReader;
    }

    protected function getValidPaymentInstance(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        return $payment;
    }
}
