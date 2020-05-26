<?php
namespace Payments\PayPal\Controller\Express;

use Magento\Framework\Controller\ResultFactory;

class ReturnAction extends \Magento\Paypal\Controller\Express\AbstractExpress\ReturnAction
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = \Magento\Paypal\Model\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \Payments\PayPal\Model\Config::CODE;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = \Payments\PayPal\Model\Express\Checkout::class;

    /**
     * @var \Payments\PayPal\Model\Express\Checkout
     */
    protected $_checkout;

    /**
     * @var \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface
     */
    private $paymentFailureRouteProvider;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Session\Generic $paypalSession,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider,
        \Magento\Customer\Model\Url $customerUrl
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

        $this->paymentFailureRouteProvider = $paymentFailureRouteProvider;
    }

    /**
     * Return from PayPal and dispatch customer to order review page
     *
     * @return void|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->getRequest()->getParam('retry_authorization') == 'true'
            && is_array($this->_getCheckoutSession()->getPaypalTransactionData())
        ) {
            $this->_forward('placeOrder');
            return;
        }

        if ($ecToken = $this->getRequest()->getParam('token')) {
            $this->_initToken($ecToken);
        }

        try {
            $this->_getCheckoutSession()->unsPaypalTransactionData();
            $this->_initCheckout();
            $this->_checkout->returnFromPaypal($this->_initToken());

            if ($this->_checkout->canSkipOrderReviewStep()) {
                $this->_forward('placeOrder');
            } else {
                $this->_redirect('*/*/review');
            }
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t process Express Checkout approval.')
            );
        }

        return $resultRedirect->setPath($this->paymentFailureRouteProvider->getFailureRoutePath());
    }
}
