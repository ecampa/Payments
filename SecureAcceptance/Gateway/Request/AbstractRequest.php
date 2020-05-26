<?php
namespace Payments\SecureAcceptance\Gateway\Request;

use Payments\SecureAcceptance\Gateway\Config\Config;
use Payments\SecureAcceptance\Gateway\Helper\SubjectReader;
use Payments\SecureAcceptance\Helper\RequestDataBuilder;
use Payments\SecureAcceptance\Helper\Vault;
use Magento\Payment\Gateway\Helper\ContextHelper;

abstract class AbstractRequest
{

    const TRANSPARENT_RESPONSE_KEY = 'gateway_transparent_response';

    /**
     * @var Config
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

    /**
     * @var Vault
     */
    protected $vaultHelper;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $requestDataBuilder,
        \Payments\SecureAcceptance\Helper\Vault $vaultHelper
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->vaultHelper = $vaultHelper;
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
