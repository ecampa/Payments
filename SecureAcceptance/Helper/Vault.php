<?php

namespace Payments\SecureAcceptance\Helper;

use Magento\Framework\Session\SessionManagerInterface;

class Vault
{

    /**
     * @var SessionManagerInterface
     */
    private $checkoutSession;
    
    /**
     * VaultHelper constructor.
     * @param SessionManagerInterface $checkoutSession
     */
    public function __construct(SessionManagerInterface $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param $vaultIsEnabled
     * @return void
     */
    public function setVaultEnabled($vaultIsEnabled)
    {
        $this->checkoutSession->unsVaultIsEnabled();
        if ($vaultIsEnabled) {
            $this->checkoutSession->setVaultIsEnabled($vaultIsEnabled);
        }
    }

    /**
     * @return bool
     */
    public function getVaultEnabled()
    {
        return $this->checkoutSession->getVaultIsEnabled();
    }

    /**
     * @return void
     */
    public function unsVaultEnabled()
    {
        $this->checkoutSession->unsVaultIsEnabled();
    }
}
