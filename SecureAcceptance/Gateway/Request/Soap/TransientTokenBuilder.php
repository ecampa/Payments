<?php
namespace Payments\SecureAcceptance\Gateway\Request\Soap;


class TransientTokenBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        if (!$this->config->isMicroform()) {
            return [];
        }

        if (!$transientToken = $paymentDO->getPayment()->getAdditionalInformation('transientToken')) {
            return [];
        }

        return [
            'tokenSource' => ['transientToken' => $transientToken],
        ];
    }
}
