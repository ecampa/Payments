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

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteManagement $quoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Payments\SecureAcceptance\Service\Lock
     */
    private $lock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface
     */
    private $paymentFailureRouteProvider;

    /**
     * PlaceOrder constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $checkoutSession
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param RequestDataBuilder $requestDataBuilder
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Config $config
     * @param \Payments\SecureAcceptance\Service\Lock $lock
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Registry $registry
     * @param LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $checkoutSession,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $requestDataBuilder,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Service\Lock $lock,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Registry $registry,
        \Payments\Core\Model\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->resultPageFactory = $resultPageFactory;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->lock = $lock;
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->eventManager = $eventManager;
        $this->paymentFailureRouteProvider = $paymentFailureRouteProvider;
    }

    public function execute()
    {
        $response = $this->getRequest()->getParams();

        if (!$this->config->getIsLegacyMode()) {
            $this->_forward('TokenProcess', 'SecureAcceptance');
            return;
        }

        $this->logger->debug(
            [
                'client' => static::class,
                'response' => (array) $response
            ]
        );

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

            $quote = $this->getQuote();

            if (!$quote->getIsActive()) {
                $this->setSuccessOrder($quote);
                return $this->processResponse('checkout/onepage/success');
            }

            $quote->reserveOrderId();

            if ($this->requestDataBuilder->getCheckoutMethod($quote) === Onepage::METHOD_GUEST) {
                $quote->getBillingAddress()->setEmail($response['req_bill_to_email']);
                $this->requestDataBuilder->prepareGuestQuote($quote);
            }

            $quote->setPaymentMethod(\Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE);
            $quote->setInventoryProcessed(false);

        // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE]);

        // register response for command response handlers
            $this->registry->register(\Payments\SecureAcceptance\Gateway\Request\AbstractRequest::TRANSPARENT_RESPONSE_KEY, $response);

            $quote->collectTotals();
            $this->quoteRepository->save($quote);

            $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
            $this->checkoutSession->setLastQuoteId($quote->getId());
            $this->checkoutSession->clearHelperData();

            $order = $this->quoteManagement->submit($quote);
            $this->eventManager->dispatch(
                'payments_quote_submit_success',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );
            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($order->getStatus());

            $this->messageManager->addSuccessMessage(__('Your order has been successfully created!'));
            $resultUrl = 'checkout/onepage/success';
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
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


    /**
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getQuote()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        try {
            if (!$quote->getId() && $quoteId = $this->getRequest()->getParam('req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_QUOTE_ID)) {
                $quote = $this->quoteRepository->get($quoteId);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

        }

        if (!$quote->getId() || $quote->getId() != $this->getRequest()->getParam('req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_QUOTE_ID)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Payment could not be processed.'));
        }

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     */
    private function setSuccessOrder($quote)
    {
        $order = $this->getQuoteOrder($quote);

        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->clearHelperData();
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
        $this->messageManager->addSuccessMessage(__('Your order has been successfully created!'));
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function getQuoteOrder($quote)
    {
        $orderList = $this->orderRepository->getList(
            $this->searchCriteriaBuilder->addFilters([
                    $this->filterBuilder->setField('quote_id')->setValue($quote->getId())->create()
                ])->create()
        );

        $orders = $orderList->getItems();

        return array_shift($orders);
    }

    /**
     * @param $url
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function processResponse($url)
    {
        $html = '<html>
                    <body>
                        <script type="text/javascript">
                            window.onload = function() {
                                window.top.location.href = "'.$this->_url->getUrl($url, ['_scope' => $this->storeManager->getStore()->getId()]).'";
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
     * @param $responses
     * @return bool
     */
    private function isValidSignature($responses)
    {
        if ($this->config->isSilent()) {
            $transactionKey = $this->config->getSopAuthSecretKey();
        } else {
            $transactionKey = $this->config->getAuthSecretKey();
        }

        return $this->requestDataBuilder->validateSignature($responses, $transactionKey);
    }
}
