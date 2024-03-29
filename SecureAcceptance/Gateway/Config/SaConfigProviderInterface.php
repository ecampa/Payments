<?php
namespace Payments\SecureAcceptance\Gateway\Config;

/**
 * Interface SaConfigProviderInterface
 *
 * Provides Secure Acceptance credentials depending on the current configuration settings (SOP/WM, PA/non-PA)
 */
interface SaConfigProviderInterface
{

    public function getProfileId($storeId = null);

    public function getAccessKey($storeId = null);

    public function getSecretKey($storeId = null);

}
