<?php
namespace Payments\PayPal\Controller\Express;

use Payments\Core\Model\LoggerInterface;
use Payments\PayPal\Model\Config as PayPalConfig;

/**
 * Abstract Express Checkout Controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractExpress extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * @var \Payments\PayPal\Model\Express\Checkout
     */
    protected $_checkout;

    public $gatewayConfig;

    /**
     * @var LoggerInterface
     */
    protected $loggerModel;

    /**
     * @var \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface
     */
    protected $paymentFailureRouteProvider;

    /**
     * AbstractExpress constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Session\Generic $paypalSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Session\Generic $paypalSession,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Url $customerUrl,
        \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider,
        \Payments\PayPal\Model\Config $gatewayConfig,
        \Payments\Core\Model\LoggerInterface $loggerModel
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $orderFactory,
            $checkoutFactory,
            $paypalSession,
            $urlHelper,
            $customerUrl
        );
        $this->gatewayConfig = $gatewayConfig;
        $this->loggerModel = $loggerModel;
        $this->paymentFailureRouteProvider = $paymentFailureRouteProvider;
    }
}
