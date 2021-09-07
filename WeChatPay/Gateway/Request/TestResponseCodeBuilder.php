<?php
namespace Payments\WeChatPay\Gateway\Request;

class TestResponseCodeBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\WeChatPay\Gateway\Config\Config
     */
    private $config;

    /**
     * @param \Payments\WeChatPay\Gateway\Config\Config $config
     */
    public function __construct(\Payments\WeChatPay\Gateway\Config\Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        if (!$this->config->isTestMode()) {
            return [];
        }

        if (!$configValue = $this->config->getTestStatusResponseCode()) {
            return [];
        }

        return ['reconciliationID' => $configValue];
    }
}
