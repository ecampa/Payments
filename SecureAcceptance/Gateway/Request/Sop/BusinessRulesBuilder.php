<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;


class BusinessRulesBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    const KEY_IGNORE_AVS_RESULT = 'ignore_avs';
    const KEY_IGNORE_CV_RESULT = 'ignore_cvn';

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        $result = [];

        if ($this->config->getIgnoreAvs()) {
            $result[self::KEY_IGNORE_AVS_RESULT] = 'true';
        }

        if ($this->config->getIgnoreCvn()) {
            $result[self::KEY_IGNORE_CV_RESULT] = 'true';
        }

        return $result;
    }
}
