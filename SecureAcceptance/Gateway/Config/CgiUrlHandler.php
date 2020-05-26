<?php

namespace Payments\SecureAcceptance\Gateway\Config;

class CgiUrlHandler implements \Magento\Payment\Gateway\Config\ValueHandlerInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    protected $config;

    /**
     * CgiUrlHandler constructor.
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
        $uri = '/pay';

        if ($this->config->isSilent()) {
            $uri = '/silent/pay';
        }

        if (!$this->config->getIsLegacyMode()) {

            $uri = '/token/create';

            if ($this->config->getUseIFrame()) {
                $uri = '/embedded/token/create';
            }

            if ($this->config->isSilent()) {
                $uri = '/silent/embedded/token/create';
            }
        }

        return $this->getServiceUrl() . $uri;
    }

    protected function getServiceUrl()
    {
        return $this->config->getSopServiceUrl();
    }
}
