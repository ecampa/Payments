<?php
namespace Payments\PayPal\Helper\Shortcut;

class Validator extends \Magento\Paypal\Helper\Shortcut\Validator
{
    /**
     * Checks visibility of context (cart or product page)
     *
     * @param string $paymentCode Payment method code
     * @param bool $isInCatalog
     * @return bool
     */
    public function isContextAvailable($paymentCode, $isInCatalog)
    {
        return true;
    }
}
