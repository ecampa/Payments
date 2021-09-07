<?php
namespace Payments\SecureAcceptance\Gateway\Request\Flex;


class BuilderStrategy implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface
     */
    private $microformBuilder;

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface
     */
    private $standardBuilder;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Payment\Gateway\Request\BuilderInterface $microformBuilder,
        \Magento\Payment\Gateway\Request\BuilderInterface $standardBuilder
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->microformBuilder = $microformBuilder;
        $this->standardBuilder = $standardBuilder;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {

        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();

        // always use the standard builder for vault
        if ($payment->getMethod() == \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CC_VAULT_CODE) {
            return $this->standardBuilder->build($buildSubject);
        }

        if ($this->config->isMicroform()) {
            return $this->microformBuilder->build($buildSubject);
        }

        return $this->standardBuilder->build($buildSubject);
    }
}
