<?php

namespace Payments\SecureAcceptance\Gateway\Config;

class CcTypesHandler implements \Magento\Payment\Gateway\Config\ValueHandlerInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    protected $config;

    /**
     * CcTypesHandler constructor.
     * @param \Payments\SecureAcceptance\Gateway\Config\Config $config
     */
    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $subject, $storeId = null)
    {
        return $this->config->getCcTypes();
    }
}
