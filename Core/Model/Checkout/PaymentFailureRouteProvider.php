<?php
namespace Payments\Core\Model\Checkout;


class PaymentFailureRouteProvider implements \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface
{

    /**
     * @var \Payments\Core\Model\Config
     */
    private $config;

    /**
     * @var string
     */
    private $defaultPaymentFailureRoute;

    /**
     * PaymentFailureRouteProvider constructor.
     *
     * @param \Payments\Core\Model\Config $config
     * @param string $defaultPaymentFailureRoute
     */
    public function __construct(
        \Payments\Core\Model\Config $config,
        $defaultPaymentFailureRoute = 'checkout/cart'
    ) {
        $this->defaultPaymentFailureRoute = $defaultPaymentFailureRoute;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getFailureRoutePath()
    {

        $overriddenRoute = trim($this->config->getOverrideErrorPageRoute());

        return $overriddenRoute ?: $this->defaultPaymentFailureRoute;

    }
}
