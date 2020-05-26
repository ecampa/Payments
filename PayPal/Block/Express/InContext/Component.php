<?php

namespace Payments\PayPal\Block\Express\InContext;

use Payments\PayPal\Model\Source\RedirectionType;
use Magento\Paypal\Model\Config;
use Payments\PayPal\Model\Config as PayPalConfig;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Component
 *
 * @api
 * @since 100.1.0
 */
class Component extends Template
{
    const IS_BUTTON_CONTEXT_INDEX = 'is_button_context';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    private $gatewayConfig;

    /**
     * Component constructor.
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param ConfigFactory $configFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        \Payments\PayPal\Model\Config $gatewayConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->config->setMethod(\Payments\PayPal\Model\Config::CODE);
        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    protected function _toHtml()
    {
        if (!$this->isInContext()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    private function isInContext()
    {
        $redirectType = $this->gatewayConfig->getPayPalRedirectType();

        if ($redirectType == \Payments\PayPal\Model\Source\RedirectionType::IN_CONTEXT) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     * @since 100.1.0
     */
    public function getEnvironment()
    {
        return (int) $this->gatewayConfig->isTestMode() ? 'sandbox' : 'production';
    }

    /**
     * @return string
     * @since 100.1.0
     */
    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return string
     * @since 100.1.0
     */
    public function getMerchantId()
    {
        return $this->gatewayConfig->getMerchantId();
    }

    /**
     * @return bool
     * @since 100.1.0
     */
    public function isButtonContext()
    {
        return true;
    }
}
