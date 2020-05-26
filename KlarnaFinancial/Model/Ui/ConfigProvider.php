<?php
namespace Payments\KlarnaFinancial\Model\Ui;

use Payments\KlarnaFinancial\Service\GatewaySoapApi;
use Magento\Checkout\Model\ConfigProviderInterface;
use Payments\KlarnaFinancial\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'payments_klarna';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    private $gatewayApi;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param ResolverInterface $localeResolver
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Payments\KlarnaFinancial\Gateway\Config\Config $config,
        ResolverInterface $localeResolver,
        \Payments\KlarnaFinancial\Service\GatewaySoapApi $gatewayApi,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->gatewayApi = $gatewayApi;
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
                    'isDeveloperMode' => $this->config->isDeveloperMode(),
                    'placeOrderUrl' => $this->url->getUrl('paymentsklarna/index/placeorder'),
                ]
            ]
        ];
    }
}
