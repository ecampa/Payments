<?php
namespace Payments\VisaCheckout\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Payments\VisaCheckout\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'payments_visa';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * Constructor
     *
     * @param Config $config
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        \Payments\VisaCheckout\Gateway\Config\Config $config,
        ResolverInterface $localeResolver,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->url = $url;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $isVisaCheckoutActive = $this->config->isActive();
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $isVisaCheckoutActive,
                    'title' => $this->config->getTitle(),
                    'api_key' => $this->config->getApiKey(),
                    'isDeveloperMode' => $this->config->isDeveloperMode(),
                    'placeOrderUrl' => $this->url->getUrl('paymentsvisa/index/placeorder'),
                    'buttonUrl' =>
                        $this->config->isTest()
                            ? 'https://sandbox.secure.checkout.visa.com/wallet-services-web/xo/button.png'
                            : 'https://secure.checkout.visa.com/wallet-services-web/xo/button.png',
                ]
            ]
        ];
    }
}
