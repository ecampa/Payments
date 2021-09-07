<?php
namespace Payments\SecureAcceptance\Controller\Index;

class Cancel extends \Payments\Core\Action\CsrfIgnoringAction
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Payments\Core\Model\Checkout\PaymentFailureRouteProvider
     */
    private $failureRouteProvider;

    /**
     * @var \Payments\Core\Service\OrderToQuoteInterface
     */
    private $orderToQuote;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * Cancel constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Session\SessionManagerInterface $checkoutSession
     * @param \Payments\SecureAcceptance\Gateway\Config\Config $config
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Payments\Core\Model\Checkout\PaymentFailureRouteProvider $failureRouteProvider
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Payments\Core\Service\OrderToQuoteInterface $orderToQuote
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Payments\Core\Model\Checkout\PaymentFailureRouteProvider $failureRouteProvider,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Payments\Core\Service\OrderToQuoteInterface $orderToQuote,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->failureRouteProvider = $failureRouteProvider;
        $this->orderRepository = $orderRepository;
        $this->orderToQuote = $orderToQuote;
        $this->messageManager = $messageManager;
        $this->cartRepository = $cartRepository;
    }

    public function execute()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid method'));
            }

            $order = $this->orderRepository->get($this->checkoutSession->getLastOrderId());

            if ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                $order->cancel();
                $this->orderRepository->save($order);

                // repopulate quote with order items
                $quote = $this->checkoutSession->getQuote();
                $quote = $this->orderToQuote->convertOrderToQuote($order->getId(), $quote);
                $this->cartRepository->save($quote);

                $this->messageManager->addNoticeMessage(__('Your payment has been cancelled.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('An error occurred.'));
        }
        return $this->_redirect($this->failureRouteProvider->getFailureRoutePath());
    }
}
