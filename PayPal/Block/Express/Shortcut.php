<?php
namespace Payments\PayPal\Block\Express;

use Payments\PayPal\Model\Source\RedirectionType;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use Payments\PayPal\Model\Config as PayPalConfig;

/**
 * Paypal express checkout shortcut link
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shortcut extends \Magento\Paypal\Block\Express\Shortcut
{
    private $gatewayConfig;

    /**
     * Shortcut constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param ValidatorInterface $shortcutValidator
     * @param string $paymentMethodCode
     * @param string $startAction
     * @param string $checkoutType
     * @param string $alias
     * @param string $shortcutTemplate
     * @param \Magento\Checkout\Model\Session|null $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        ValidatorInterface $shortcutValidator,
        \Payments\PayPal\Model\Config $gatewayConfig,
        $paymentMethodCode,
        $startAction,
        $checkoutType,
        $alias,
        $shortcutTemplate,
        \Magento\Checkout\Model\Session $checkoutSession = null,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $paypalConfigFactory,
            $checkoutFactory,
            $mathRandom,
            $localeResolver,
            $shortcutValidator,
            $paymentMethodCode,
            $startAction,
            $checkoutType,
            $alias,
            $shortcutTemplate,
            $checkoutSession,
            $data
        );

        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * @return bool
     */
    protected function shouldRender()
    {
        $isRedirect = $this->getIsInCatalogProduct() || !$this->gatewayConfig->isInContext();
        return $isRedirect && $this->_shouldRender;
    }
}
