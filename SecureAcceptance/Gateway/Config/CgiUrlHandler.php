<?php

namespace Payments\SecureAcceptance\Gateway\Config;

class CgiUrlHandler implements \Magento\Payment\Gateway\Config\ValueHandlerInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    protected $config;

    /**
     * @var false
     */
    private $isAdmin;

    /**
     * CgiUrlHandler constructor.
     * @param \Payments\SecureAcceptance\Gateway\Config\Config $config
     */
    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        $isAdmin = false
    ) {
        $this->config = $config;
        $this->isAdmin = $isAdmin;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $subject, $storeId = null)
    {
        $uri = '/pay';

        if ($this->config->getUseIFrame()) {
            $uri = '/embedded/pay';
        }

        if ($this->config->isSilent()) {
            $uri = '/silent/pay';
        }

        if (!$this->config->getIsLegacyMode() && !$this->isAdmin) {

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
