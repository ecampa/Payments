<?php

namespace Payments\PayPal\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Paypal\Helper\Data as PaypalHelper;
use Payments\PayPal\Model\Config as GatewayConfig;

class ConfigProvider implements ConfigProviderInterface
{
    const IN_CONTEXT_BUTTON_ID = 'paypal-express-in-context-button';

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var PaypalHelper
     */
    protected $paypalHelper;

    /**
     * @var string[]
     */
    protected $methodCodes = [
        \Payments\PayPal\Model\Config::CODE,
        \Payments\PayPal\Model\Config::CODE_CREDIT
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var GatewayConfig
     */
    private $gatewayConfig;

    /**
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaypalHelper $paypalHelper
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
     * @param GatewayConfig $gatewayConfig
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        PaypalHelper $paypalHelper,
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder,
        \Payments\PayPal\Model\Config $gatewayConfig
    ) {
        $this->localeResolver = $localeResolver;
        $this->gatewayConfig = $gatewayConfig;
        $this->currentCustomer = $currentCustomer;
        $this->paypalHelper = $paypalHelper;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $locale = $this->localeResolver->getLocale();

        $config = [
            'payment' => [
                \Payments\PayPal\Model\Config::CODE => [
                    'paymentAcceptanceMarkHref' => $this->gatewayConfig->getPaymentMarkWhatIsPaypalUrl(
                        $this->localeResolver
                    ),
                    'paymentAcceptanceMarkSrc' => $this->gatewayConfig->getPaymentMarkImageUrl(),
                    'isContextCheckout' => false,
                    'inContextConfig' => [],
                    'creditTitle' => $this->gatewayConfig->getCreditTitle(),
                    'isVaultEnabled' => $this->gatewayConfig->isVaultEnabled(),
                    'vaultCode' => \Payments\PayPal\Model\Config::CODE_VAULT
                ]
            ]
        ];

        if ($this->gatewayConfig->isInContext()) {
            $config['payment'][\Payments\PayPal\Model\Config::CODE]['isContextCheckout'] = true;
            $config['payment'][\Payments\PayPal\Model\Config::CODE]['inContextConfig'] = [
                'inContextId' => self::IN_CONTEXT_BUTTON_ID,
                'merchantId' => $this->gatewayConfig->getPayPalMerchantId(),
                'path' => $this->urlBuilder->getUrl('paymentspaypal/express/gettoken', ['_secure' => true]),
                'clientConfig' => [
                    'environment' => ($this->gatewayConfig->getEnvironment() == 'sandbox' ? 'sandbox' : 'production'),
                    'locale' => $locale,
                    'button' => [
                        self::IN_CONTEXT_BUTTON_ID
                    ]
                ],
            ];
        }

        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment'][\Payments\PayPal\Model\Config::CODE]['redirectUrl'][$code] = $this->getMethodRedirectUrl($code);
            }
        }

        return $config;
    }

    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getCheckoutRedirectUrl();
    }
}
