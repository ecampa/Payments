<?php

namespace Payments\SecureAcceptance\Gateway\Response\Soap;


class DataCleanHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var array
     */
    private $cleanKeys = [
        'microformPublicKey',
        'signature',
        'signedFields'
    ];

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        $additionalKeys = []
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->cleanKeys = array_merge($this->cleanKeys, $additionalKeys);
    }

    /**
     * Cleans unnecessary data from additional information field
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDo = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDo->getPayment();

        foreach ($this->cleanKeys as $key) {
            $payment->unsAdditionalInformation($key);
        }
    }
}
