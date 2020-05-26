<?php

namespace Payments\SecureAcceptance\Helper;

class MethodForm
{


    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->config = $config;
    }

    public function getCCVaultTemplateName()
    {
        return 'Magento_Vault::form/vault.phtml';
    }

    public function getCCTemplateName()
    {

        if ($this->config->isSilent()) {
            return 'Payments_SecureAcceptance::payment/sop.phtml';
        }

        if ($this->config->getUseIFrame()) {
            return 'Payments_SecureAcceptance::payment/wm-iframe.phtml';
        }

        return 'Payments_SecureAcceptance::payment/wm.phtml';
    }
}
