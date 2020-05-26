<?php

namespace Payments\SecureAcceptance\Gateway\Request\Soap;

class CvnBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    const ADMIN_PREFIX = 'admin';

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var string
     */
    private $isAdmin;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        string $isAdmin = null
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Builds Subscription data request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $result = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        /** @var \Magento\Vault\Model\PaymentToken $vaultPaymentToken */
        $vaultPaymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();

        if ((is_null($vaultPaymentToken) || $vaultPaymentToken->isEmpty()) && !$this->config->isMicroform()) {
            return [];
        }

        if (!$cvv = $payment->getAdditionalInformation('cvv')) {
            return [];
        }

        $prefix = '';

        if ($this->isAdmin) {
            $prefix = self::ADMIN_PREFIX . '_';
        }

        $path = 'enable_' . $prefix . 'cvv';

        if ($this->config->getValue($path)) {
            $result['card']['cvNumber'] = $cvv;
        }

        $payment->unsAdditionalInformation('cvv');

        return $result;
    }
}
