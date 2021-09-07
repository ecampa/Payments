<?php
namespace Payments\SecureAcceptance\Gateway\Request\Soap;


class MicroformSubscriptionStrategy implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface
     */
    private $subscriptionCreateBuilder;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Payment\Gateway\Request\BuilderInterface $subscriptionCreateBuilder,
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->subscriptionCreateBuilder = $subscriptionCreateBuilder;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {

        if (!$this->config->isMicroform()) {
            return [];
        }

        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();

        if (!$payment->getAdditionalInformation(\Magento\Vault\Model\Ui\VaultConfigProvider::IS_ACTIVE_CODE)) {
            return [];
        }

        return $this->subscriptionCreateBuilder->build($buildSubject);
    }
}
