<?php
namespace Payments\PayPal\Block\Express\InContext\Minicart;

use Magento\Checkout\Model\Session;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Model\ConfigFactory;
use Payments\PayPal\Model\Config as PayPalConfig;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;

class Button extends \Magento\Paypal\Block\Express\InContext\Minicart\Button
{
    private $isInContextAllowed = true;

    private $gatewayConfig;

    /**
     * @var MethodInterface
     */
    private $payment;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param ConfigFactory $configFactory
     * @param MethodInterface $payment
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        Session $session,
        MethodInterface $payment,
        \Payments\PayPal\Model\Config $gatewayConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $localeResolver,
            $configFactory,
            $session,
            $payment,
            $data
        );

        $this->gatewayConfig = $gatewayConfig;
        $this->payment = $payment;
        $this->session = $session;
    }

    /**
     * @return bool
     */
    protected function shouldRender()
    {
        return $this->payment->isAvailable($this->session->getQuote())
            && $this->gatewayConfig->isInContext()
            && $this->isInContextAllowed;
    }

    /**
     * @param bool $isInCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isInCatalog)
    {
        $this->isInContextAllowed = !$isInCatalog;
        return $this;
    }
}
