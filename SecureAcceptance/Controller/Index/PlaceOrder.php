<?php
namespace Payments\SecureAcceptance\Controller\Index;

use Payments\Core\Model\LoggerInterface;
use Payments\SecureAcceptance\Gateway\Config\Config;
use Payments\SecureAcceptance\Gateway\Request\AbstractRequest;
use Payments\SecureAcceptance\Helper\RequestDataBuilder;
use Payments\SecureAcceptance\Model\Ui\ConfigProvider;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderRepository;

class PlaceOrder extends \Payments\Core\Action\CsrfIgnoringAction
{
    const LOCK_PREFIX = 'lock_req_';
    const COMMAND_CODE = 'sop_handle_response';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Payments\SecureAcceptance\Service\Lock
     */
    private $lock;

    /**
     * @var \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface
     */
    private $paymentFailureRouteProvider;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerInterface
     */
    private $commandManager;

    /**
     * @var \Payments\SecureAcceptance\Model\SignatureManagementInterface
     */
    private $signatureManagement;

    /**
     * @var \Magento\Sales\Model\Order\StatusResolver
     */
    private $statusResolver;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    private $orderSender;

    /**
     * PlaceOrder constructor.
     *
     * @param Context $context
     * @param \Payments\SecureAcceptance\Model\SignatureManagementInterface $signatureManagement
     * @param \Magento\Payment\Gateway\Command\CommandManagerInterface $commandManager
     * @param Config $config
     * @param \Payments\SecureAcceptance\Service\Lock $lock
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider
     */
    public function __construct(
        Context $context,
        \Payments\SecureAcceptance\Model\SignatureManagementInterface $signatureManagement,
        \Magento\Payment\Gateway\Command\CommandManagerInterface $commandManager,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Service\Lock $lock,
        OrderRepositoryInterface $orderRepository,
        \Payments\Core\Model\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\StatusResolver $statusResolver,
        \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        parent::__construct($context);

        $this->signatureManagement = $signatureManagement;
        $this->commandManager = $commandManager;
        $this->config = $config;
        $this->lock = $lock;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->paymentFailureRouteProvider = $paymentFailureRouteProvider;
        $this->statusResolver = $statusResolver;
        $this->orderSender = $orderSender;
    }

    public function execute()
    {
        $response = $this->getRequest()->getParams();

        if (!$this->config->getIsLegacyMode()) {
            $this->_forward('TokenProcess', 'SecureAcceptance');
            return;
        }

        $resultUrl = $this->paymentFailureRouteProvider->getFailureRoutePath();

        if (!$this->isValidSignature($response)) {
            $this->messageManager->addErrorMessage(__('Payment could not be processed.'));

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($resultUrl, ['_secure' => true]);
        }

        if (isset($response['req_merchant_defined_data24'])
            && $response['req_merchant_defined_data24'] == 'token_payment'
        ) {
            $this->logger->debug('Token payment, ignoring');
            return $this->processResponse($resultUrl);
        }

        try {

            if (!$this->lock->acquireLock($this->getLockName())) {
                return $this->processResponse($resultUrl);
            }

            $order = $this->getOrder();

            $payment = $order->getPayment();

            $this->commandManager->executeByCode(
                static::COMMAND_CODE,
                $payment,
                ['response' => $this->getRequest()->getParams()]
            );

            if ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                $this->registerNotification($payment);
                $this->orderRepository->save($order);
                $this->orderSender->send($order);
            }

            $this->messageManager->addSuccessMessage(__('Your order has been successfully created!'));
            $resultUrl = 'checkout/onepage/success';
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());

            if ($order ?? null) {
                $this->updateFailedState($order);
                $this->orderRepository->save($order);
            }

        } finally {
            $this->lock->releaseLock($this->getLockName());
        }

        if ($this->config->getUseIFrame()) {
            return $this->processResponse($resultUrl);
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($resultUrl, ['_secure' => true]);
    }

    private function getOrder()
    {
        $orderId = $this->getRequest()->getParam('req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_ORDER_ID);
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param $url
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function processResponse($url)
    {
        $html = '<html>
                    <body>
                        <script type="text/javascript">
                            window.onload = function() {
                                window.top.location.href = "' . $this->_url->getUrl($url,
                ['_scope' => $this->storeManager->getStore()->getId()]) . '";
                            };
                        </script>
                    </body>
                </html>';

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRedirect->setContents($html);
        return $resultRedirect;
    }

    public function getLockName()
    {
        return self::LOCK_PREFIX . $this->getRequest()->getParam('req_transaction_uuid');
    }

    /**
     *
     * Validates signature of request
     *
     * @param $response
     *
     * @return bool
     */
    private function isValidSignature($response)
    {
        if ($this->config->isSilent()) {
            $transactionKey = $this->config->getSopAuthSecretKey();
        } else {
            $transactionKey = $this->config->getAuthSecretKey();
        }

        return $this->signatureManagement->validateSignature($response, $transactionKey);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function registerNotification(\Magento\Sales\Api\Data\OrderPaymentInterface $payment)
    {
        $amount = $this->getRequest()->getParam('req_amount');

        $payment->registerAuthorizationNotification($amount);

        if (
            $payment->getMethodInstance()->getConfigPaymentAction()
            != \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE
        ) {
            return;
        }

        $payment->registerCaptureNotification($amount);
    }

    public function updateFailedState(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $order->cancel();
    }
}
