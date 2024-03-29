<?php
namespace Payments\KlarnaFinancial\Controller\Index;

use Magento\Framework\App\Action\Context;
use Payments\KlarnaFinancial\Helper\RequestDataBuilder;
use Payments\KlarnaFinancial\Service\GatewaySoapApi;
use Magento\Framework\Controller\Result\JsonFactory;

class Session extends \Magento\Framework\App\Action\Action
{

    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    private $gatewayApi;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * LoadInfo constructor.
     *
     * @param Context $context
     * @param RequestDataBuilder $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Payments\KlarnaFinancial\Helper\RequestDataBuilder $helper,
        \Payments\KlarnaFinancial\Service\GatewaySoapApi $gatewayApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        JsonFactory $resultJsonFactory
    ) {
        $this->requestDataBuilder = $helper;
        $this->gatewayApi = $gatewayApi;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->cartRepository = $cartRepository;
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $updateMode = (bool) $this->_request->getParam('updateToken');
        $guestEmail = $this->_request->getParam('guestEmail');

        $quote = $this->checkoutSession->getQuote();

        $quote->collectTotals();
        $quote->reserveOrderId();
        $this->cartRepository->save($quote);

        if (! $quote->getCustomerId()) {
            $quote->setCustomerEmail($guestEmail);
            $quote->getBillingAddress()->setEmail($guestEmail);
        }

        $data = [];
        try {
            $request = $this->requestDataBuilder->buildSessionRequest($updateMode);
            $response = $this->gatewayApi->placeRequest($request);
            $data['processorToken'] = $response;
        } catch (\Exception $e) {
            $data['message'] = __("Unable to initialize Klarna.");
        }

        return $this->resultJsonFactory->create()->setData($data);
    }
}
