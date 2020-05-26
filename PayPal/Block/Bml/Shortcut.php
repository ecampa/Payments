<?php

namespace Payments\PayPal\Block\Bml;

use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use Payments\PayPal\Model\Config as PayPalConfig;

class Shortcut extends \Magento\Paypal\Block\Bml\Shortcut
{
    private $gatewayConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param ValidatorInterface $shortcutValidator
     * @param string $paymentMethodCode
     * @param string $startAction
     * @param string $alias
     * @param string $bmlMethodCode
     * @param string $shortcutTemplate
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Math\Random $mathRandom,
        ValidatorInterface $shortcutValidator,
        string $paymentMethodCode,
        string $startAction,
        string $alias,
        string $bmlMethodCode,
        string $shortcutTemplate,
        \Payments\PayPal\Model\Config $gatewayConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $paymentData,
            $mathRandom,
            $shortcutValidator,
            $paymentMethodCode,
            $startAction,
            $alias,
            $bmlMethodCode,
            $shortcutTemplate,
            $data
        );

        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->gatewayConfig->isPayPalCreditEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
