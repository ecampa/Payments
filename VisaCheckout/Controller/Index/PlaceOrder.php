<?php
namespace Payments\VisaCheckout\Controller\Index;

use Payments\Core\Model\LoggerInterface;
use Payments\VisaCheckout\Gateway\Validator\ResponseCodeValidator;
use Payments\VisaCheckout\Helper\ParseGetDataHelper;
use Payments\VisaCheckout\Helper\RequestDataBuilder;
use Payments\VisaCheckout\Model\Ui\ConfigProvider;
use Payments\VisaCheckout\Gateway\Http\Client\SOAPClient;
use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Payments\VisaCheckout\Gateway\Http\TransferFactory;

class PlaceOrder extends \Magento\Framework\App\Action\Action
{
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
     * @var SOAPClient
     */
    private $soapClient;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var ParseGetDataHelper
     */
    private $getDataHelper;

    /**
     * @var LoggerInterface
     */
    private $loggerModel;

    /**
     * @var AgreementsValidatorInterface
     */
    private $agreementsValidator;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;
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
     * @param SOAPClient $soapClient
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param TransferFactory $transferFactory
     * @param ParseGetDataHelper $getDataHelper
     * @param AgreementsValidatorInterface $agreementsValidator
     * @param \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $checkoutSession,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Payments\VisaCheckout\Helper\RequestDataBuilder $requestDataBuilder,
        \Payments\VisaCheckout\Gateway\Http\Client\SOAPClient $soapClient,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Payments\VisaCheckout\Gateway\Http\TransferFactory $transferFactory,
        \Payments\VisaCheckout\Helper\ParseGetDataHelper $getDataHelper,
        AgreementsValidatorInterface $agreementsValidator,
        \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Payments\Core\Model\LoggerInterface $loggerModel,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->resultPageFactory = $resultPageFactory;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->soapClient = $soapClient;
        $this->quoteRepository = $quoteRepository;
        $this->transferFactory = $transferFactory;
        $this->getDataHelper = $getDataHelper;
        $this->agreementsValidator = $agreementsValidator;
        $this->loggerModel = $loggerModel;
        $this->formKeyValidator = $formKeyValidator;
        $this->eventManager = $eventManager;
        $this->paymentFailureRouteProvider = $paymentFailureRouteProvider;
    }

    public function execute()
    {
        if (! $this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid formkey'));
            return $this->_redirect($this->paymentFailureRouteProvider->getFailureRoutePath(), ['_secure' => true]);
        }

        if (! $this->agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', [])))) {
            $e = new \Magento\Framework\Exception\LocalizedException(
                __('Please agree to all the terms and conditions before placing the order.')
            );
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            return $this->_redirect($this->paymentFailureRouteProvider->getFailureRoutePath(), ['_secure' => true]);
        }

        $callId = $this->_request->getParam('callId');

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->messageManager->addErrorMessage(__('We can\'t place order Visa Checkout.'));
            return $this->_redirect($this->paymentFailureRouteProvider->getFailureRoutePath(), ['_secure' => true]);
        }
        if (!$callId) {
            $callId = $quote->getPayment()->getAdditionalInformation("callId");
        }
        $quote->reserveOrderId();

        $quote = $this->ignoreAddressValidation($quote);

        $requestDecryptData = (array) $this->requestDataBuilder->buildVisaDecryptRequestData(
            $callId,
            $quote->getReservedOrderId()
        );

        try {
            $responseDecrypted = $this->soapClient->placeRequest(
                $this->transferFactory->create($requestDecryptData)
            );

            if ($responseDecrypted !== null && $responseDecrypted[\Payments\VisaCheckout\Gateway\Validator\ResponseCodeValidator::RESULT_CODE] == 100) {
                $billTo = $this->getDataHelper->convertVisaCheckoutAddressToAddress($responseDecrypted['billTo']);
                $billingAddress = $this->getDataHelper->parseVisaAddress($quote->getBillingAddress(), $billTo, $responseDecrypted['vcReply']);
                $quote->getBillingAddress()->unsAddressId()->setCustomerAddressId(null);
                $quote->setBillingAddress($billingAddress);
                $this->getDataHelper->setCreditCardData($responseDecrypted, $quote->getPayment());
            }

        } catch (\Exception $e) {
            $this->loggerModel->error($e->getMessage());
        }

        /**
         * Handle customer guest
         */
        if ($quote->getCustomerEmail() === null) {
            $quote = $this->prepareGuestQuote($quote);
        }

        $quote->setPaymentMethod(\Payments\VisaCheckout\Model\Ui\ConfigProvider::CODE);
        $quote->setInventoryProcessed(false);

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => \Payments\VisaCheckout\Model\Ui\ConfigProvider::CODE]);
        $quote->getPayment()->setAdditionalInformation("callId", $callId);

        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->clearHelperData();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
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

            $successValidator = $this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator');

            if (!$successValidator->isValid()) {
                return $resultRedirect->setPath($this->paymentFailureRouteProvider->getFailureRoutePath(), ['_secure' => true]);
            }

            $this->messageManager->addSuccessMessage('Your order has been successfully created!');
            return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $resultRedirect->setPath($this->paymentFailureRouteProvider->getFailureRoutePath(), ['_secure' => true]);
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @return
     */
    private function ignoreAddressValidation($quote)
    {
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$quote->getIsVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote
     */
    private function prepareGuestQuote(\Magento\Quote\Model\Quote $quote)
    {
        $quote->setCustomerId(null);
        $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
        $quote->setCustomerIsGuest(true);
        $quote->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

        return $quote;
    }
}
