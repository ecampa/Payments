<?php
namespace Payments\PayPal\Helper;

use Payments\Core\Helper\AbstractDataBuilder;
use Payments\Core\Model\Config as CoreConfig;
use Payments\PayPal\Model\Config;
use Payments\PayPal\Model\Express\Checkout;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Vault\Model\Ui\VaultConfigProvider;

class RequestDataBuilder extends \Payments\Core\Helper\AbstractDataBuilder
{
    const AP_PAYMENT_TYPE = 'PPL';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Quote\Api\Data\ShippingAssignmentInterface
     */
    private $shippingAssignment;

    /**
     * @var \Magento\Quote\Model\Shipping
     */
    private $quoteShipping;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total
     */
    private $total;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * RequestDataBuilder constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param Session $checkoutSession
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param Quote\Address\Total $total
     * @param \Magento\Quote\Model\Shipping $quoteShipping
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Helper\Data $data
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderGridCollectionFactory
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\GiftMessage\Model\Message $giftMessage
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Payments\PayPal\Model\Config $config,
        Session $checkoutSession,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total,
        \Magento\Quote\Model\Shipping $quoteShipping,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $data,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderGridCollectionFactory,
        \Magento\Backend\Model\Auth $auth,
        \Magento\GiftMessage\Model\Message $giftMessage
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $data,
            $orderCollectionFactory,
            $orderGridCollectionFactory,
            $auth,
            $giftMessage
        );

        $this->storeManager = $storeManager;
        $this->shippingAssignment = $shippingAssignment;
        $this->total = $total;
        $this->quoteShipping = $quoteShipping;
        $this->quoteRepository = $quoteRepository;
        $quote = $checkoutSession->getQuote();
        $this->config = $config;
        $this->config->setStoreId($quote->getStoreId());

        $this->setUpCredentials($config->getPayPalMerchantId(), $config->getTransactionKey());
    }

    /**
     * @param Quote $quote
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param bool $isPayPalCredit
     * @param bool $excludeShipping
     * @return \stdClass
     */
    public function buildSessionService(Quote $quote, $returnUrl, $cancelUrl, $isPayPalCredit = false, $excludeShipping = false)
    {
        $request = $this->buildBaseRequest($quote->getStoreId());

        $request->merchantReferenceCode = $quote->getReservedOrderId();

        $apSessionsService = new \stdClass();
        $apSessionsService->run = "true";
        $apSessionsService->successURL = $returnUrl;
        $apSessionsService->cancelURL = $cancelUrl;

        if ($isPayPalCredit) {
            $apSessionsService->paymentOptionID = 'Credit';
        }

        $request->apSessionsService =  $apSessionsService;

        $request = $this->buildRequestItems($quote->getAllVisibleItems(), $request);

        if ($customerId = $this->customerSession->getCustomerId()) {
            $request->customerID = $customerId;
        }

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $quote->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($quote->getBaseGrandTotal());
        $purchaseTotals->taxAmount = $this->aggregateTaxAmountFromItems($quote->getAllVisibleItems());
        $purchaseTotals->shippingAmount = $this->formatAmount($quote->getShippingAddress()->getBaseShippingInclTax());
        $purchaseTotals->subTotalAmount = $this->formatAmount($quote->getBaseSubtotal());
        $request->purchaseTotals = $purchaseTotals;

        $isBillingAgreement = $quote->getPayment()->getAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE);

        $request->ap = new \stdClass();
        $request->ap->billingAgreementIndicator = $isBillingAgreement ? 'true' : 'false';

        if ($excludeShipping) {
            return $request;
        }

        $shipTo = new \stdClass();
        $shippingAddress = $quote->getShippingAddress();

        $shipTo->firstName = $shippingAddress->getFirstname();
        $shipTo->lastName = $shippingAddress->getLastname();
        $shipTo->street1 = $shippingAddress->getStreetLine(1);
        if ($shippingAddress->getStreetLine(2)) {
            $shipTo->street2 = $shippingAddress->getStreetLine(2);
        }

        $shipTo->city = $shippingAddress->getCity();
        $shipTo->state = $shippingAddress->getRegion();
        $shipTo->postalCode = $shippingAddress->getPostcode();
        $shipTo->country = $shippingAddress->getCountryId();
        $shipTo->phoneNumber = $shippingAddress->getTelephone();

        $request->shipTo = $shipTo;

        return $request;
    }

    /**
     * @param array $setServiceResponse
     * @return \stdClass
     */
    public function buildCheckStatusService($setServiceResponse, $quote)
    {
        $request = $this->buildBaseRequest($quote->getStoreId());

        $request->merchantReferenceCode = $setServiceResponse['merchantReferenceCode'];

        if ($customerId = $this->customerSession->getCustomerId()) {
            $request->customerID = $customerId;
        };

        $apCheckStatusService = new \stdClass();
        $apCheckStatusService->run = "true";
        $apCheckStatusService->sessionsRequestID = $setServiceResponse['requestID'];

        $request->apCheckStatusService = $apCheckStatusService;

        return $request;
    }

    /**
     * @param $getDetailsResponse
     * @param Quote $quote
     * @return mixed|\stdClass
     */
    public function buildOrderSetupService($getDetailsResponse, $quote)
    {
        $request = $this->buildBaseRequest($quote->getStoreId());

        $request->merchantReferenceCode = $getDetailsResponse['merchantReferenceCode'];

        if ($customerId = $this->customerSession->getCustomerId()) {
            $request->customerID = $customerId;
        };

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $quote->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($quote->getBaseGrandTotal());
        $purchaseTotals->taxAmount = $this->aggregateTaxAmountFromItems($quote->getAllVisibleItems());
        $purchaseTotals->shippingAmount = $this->formatAmount($quote->getShippingAddress()->getBaseShippingInclTax());
        $purchaseTotals->subTotalAmount = $this->formatAmount($quote->getBaseSubtotal());
        $request->purchaseTotals = $purchaseTotals;

        $request->billTo = $this->buildAddress($quote->getBillingAddress(), $getDetailsResponse);
        $request->shipTo = $this->buildAddress($quote->getShippingAddress(), $getDetailsResponse);

        $ap = new \stdClass();
        $ap->payerID = $getDetailsResponse['paypalPayerId'];
        $request->ap = $ap;

        $request = $this->buildRequestItems($quote->getAllVisibleItems(), $request);

        $apOrderService = new \stdClass();
        $apOrderService->run = "true";
        $apOrderService->sessionsRequestID = $getDetailsResponse['paypalEcSetRequestID'];

        $request->apOrderService = $apOrderService;

        return $request;
    }

    /**
     * @param Quote $quote
     * @param string $orderSetupRequestId
     * @return \stdClass
     */
    public function buildAuthorizationService($quote, $orderSetupRequestId)
    {
        $request = $this->buildBaseRequest($quote->getStoreId());

        $request->merchantReferenceCode = $quote->getReservedOrderId();

        if ($customerId = $this->customerSession->getCustomerId()) {
            $request->customerID = $customerId;
        };

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $quote->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($quote->getBaseGrandTotal());
        $request->purchaseTotals = $purchaseTotals;

        $request = $this->buildRequestItems($quote->getAllVisibleItems(), $request);

        $apAuthService = new \stdClass();
        $apAuthService->run = "true";
        $apAuthService->orderRequestID = $orderSetupRequestId;

        $request->billTo = $this->buildAddress($quote->getBillingAddress());
        $request->shipTo = $this->buildAddress($quote->getShippingAddress());

        $request->merchantDefinedData = $this->buildDecisionManagerFields($quote);

        $request->apAuthService = $apAuthService;

        return $request;
    }

    /**
     * @param $quote
     * @param $orderSetupRequestId
     * @return \stdClass
     */
    public function buildSaleService($quote, $orderSetupRequestId)
    {
        $request = $this->buildAuthorizationService($quote, $orderSetupRequestId);

        $request->apSaleService = $request->apAuthService;
        unset($request->apAuthService);

        return $request;
    }


    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @param string $requestId
     * @return \stdClass
     */
    public function buildCaptureService(\Magento\Payment\Model\InfoInterface $payment, $amount, $requestId)
    {
        $request = $this->buildBaseRequest($payment->getOrder()->getStoreId());

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $request->merchantReferenceCode = $order->getIncrementId();
        $request->customerID = $payment->getOrder()->getCustomerId();

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $order->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($amount);
        $request->purchaseTotals = $purchaseTotals;

        $apCaptureService = new \stdClass();
        $apCaptureService->run = "true";
        $apCaptureService->authRequestID = $requestId;
        $this->buildCaptureSequence($payment, $apCaptureService, $amount);

        $request->apCaptureService = $apCaptureService;

        $invoice = $payment->getInvoice();
        if (!$invoice) {
            $invoice = $payment->getCreatedInvoice();
        }

        $invoicedItems = [];
        if ($invoice) {
            /** @var \Magento\Sales\Model\Order\Invoice\Item $invoiceItem */
            foreach ($invoice->getAllItems() as $invoiceItem) {
                if ($invoiceItem->getQty() >= 1) {
                    $invoicedItems[] = $invoiceItem;
                }
            }
        }

        $request = $this->buildRequestItems($invoicedItems, $request);

        $request->shipTo = $this->buildAddress($payment->getOrder()->getShippingAddress());
        $request->billTo = $this->buildAddress($payment->getOrder()->getBillingAddress());

        $request->billTo->ipAddress = $payment->getOrder()->getRemoteIp();

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @param string $requestID
     * @return \stdClass
     */
    public function buildRefundService(\Magento\Payment\Model\InfoInterface $payment, $amount, $requestID)
    {
        $request = $this->buildBaseRequest($payment->getOrder()->getStoreId());

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $request->merchantReferenceCode = $order->getIncrementId();
        $request->customerID = $payment->getOrder()->getCustomerId();

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $order->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($amount);
        $request->purchaseTotals = $purchaseTotals;

        $apRefundService = new \stdClass();
        $apRefundService->run = "true";
        $apRefundService->refundRequestID = $requestID;

        $request->apRefundService = $apRefundService;

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $request->shipTo = $this->buildAddress($payment->getOrder()->getShippingAddress());
        $request->billTo = $this->buildAddress($payment->getOrder()->getBillingAddress());
        $request->billTo->ipAddress = $payment->getOrder()->getRemoteIp();

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $request = $this->buildRequestItems($payment->getCreditmemo()->getAllItems(), $request);

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \stdClass
     */
    public function buildAuthorizeReversal(\Magento\Payment\Model\InfoInterface $payment)
    {
        $request = $this->buildBaseRequest($payment->getOrder()->getStoreId());

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $requestId = $payment->getCcTransId();

        $request->merchantReferenceCode = $order->getIncrementId();
        $request->customerID = $payment->getOrder()->getCustomerId();

        $apAuthReversalService = new \stdClass();
        $apAuthReversalService->run = "true";
        $apAuthReversalService->authRequestID = $requestId;

        $request->apAuthReversalService = $apAuthReversalService;

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $request->shipTo = $this->buildAddress($payment->getOrder()->getShippingAddress());
        $request->billTo = $this->buildAddress($payment->getOrder()->getBillingAddress());
        $request->billTo->ipAddress = $payment->getOrder()->getRemoteIp();

        return $request;
    }

    /**
     * @param string $sessionRequestId
     * @param Quote $quote
     * @return \stdClass
     */
    public function buildBillingAgreementService($sessionRequestId, $quote)
    {
        $request = $this->buildBaseRequest($quote->getStoreId());

        $request->merchantReferenceCode = $quote->getReservedOrderId();

        $apBillingAgreementService = new \stdClass();
        $apBillingAgreementService->run = "true";
        $apBillingAgreementService->sessionsRequestID = $sessionRequestId;

        $request->apBillingAgreementService = $apBillingAgreementService;

        return $request;
    }

    /**
     * @param string $billingAgreementId
     * @param float $amount
     * @param string $merchantReferenceCode
     * @param string $currency
     * @return \stdClass
     */
    public function buildVaultSaleService($billingAgreementId, $amount, $merchantReferenceCode, $currency = 'USD')
    {
        $request = $this->buildBaseRequest();

        $request->merchantReferenceCode = $merchantReferenceCode;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $currency;
        $purchaseTotals->grandTotalAmount = $this->formatAmount($amount);
        $request->purchaseTotals = $purchaseTotals;

        $apSaleService = new \stdClass();
        $apSaleService->run = "true";
        $request->apSaleService = $apSaleService;

        $ap = new \stdClass();
        $ap->billingAgreementID = $billingAgreementId;
        $request->ap = $ap;

        return $request;
    }

    /**
     * @param string $billingAgreementId
     * @return \stdClass
     */
    public function buildCancelBillingAgreementService($billingAgreementId)
    {
        $request = $this->buildBaseRequest();

        $request->merchantReferenceCode = $billingAgreementId;

        $apCancelService = new \stdClass();
        $apCancelService->run = "true";
        $request->apCancelService = $apCancelService;

        $ap = new \stdClass();
        $ap->billingAgreementID = $billingAgreementId;
        $request->ap = $ap;

        return $request;
    }

    /**
     * @param Quote\Address $quoteAddress
     * @param array $getDetailsResponse
     * @return \stdClass
     */
    private function buildAddress($quoteAddress, $getDetailsResponse = null)
    {
        /** @var \Magento\Quote\Model\Quote\Address $quoteAddress */
        $address = new \stdClass();
        $address->city =  $quoteAddress->getData('city');
        $address->country = $quoteAddress->getData('country_id');
        $address->postalCode = $quoteAddress->getData('postcode');
        $address->state = $quoteAddress->getRegionCode();
        $address->street1 = $quoteAddress->getStreetLine(1);
        if (!is_null($getDetailsResponse)) {
            $quoteAddress->setEmail($getDetailsResponse['paypalCustomerEmail']);
        }
        $address->email = $quoteAddress->getEmail();
        $address->firstName = $quoteAddress->getFirstname();
        $address->lastName = $quoteAddress->getLastname();

        if ($quoteAddress->getAddressType() == Quote\Address::TYPE_BILLING) {
            $address->ipAddress = $this->_remoteAddress->getRemoteAddress();
            $address->phoneNumber = $quoteAddress->getTelephone();
        }

        return $address;
    }

    /**
     * @param Quote\Item[] $items
     * @param \stdClass $request
     * @return \stdClass
     */
    private function buildRequestItems(array $items, \stdClass $request)
    {
        $index = 0;
        foreach ($items as $i => $item) {

            /** @var \Magento\Sales\Model\Order\Item $item */
            $qty = (!empty($item->getQty()) ? $item->getQty() : $item->getQtyOrdered());

            if (!empty($qty) && $qty == 0) {
                continue;
            }
            else if(empty($qty)){
                $qty = 1;
            }
            
            $amount = $item->getBasePrice() - ($item->getBaseDiscountAmount() / $qty);
            if($amount < 0)
                $amount = 0;

            $requestItem = new \stdClass();
            $requestItem->id = $i;
            $requestItem->productName = $item->getName();
            $requestItem->productSKU = $item->getSku();
            $requestItem->quantity = (int) $qty;
            $requestItem->productCode = 'default';
            $requestItem->unitPrice = $this->formatAmount($amount);
            $requestItem->taxAmount = $this->formatAmount($item->getBaseTaxAmount());
            $request->item[] = $requestItem;
            $index = $i;
        }

        $quoteShippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();
        $shippingCostItem = new \stdClass();
        $shippingCostItem->id = $index + 1;
        $shippingCostItem->productCode = 'shipping_and_handling';
        $shippingCostItem->unitPrice = $this->formatAmount($quoteShippingAddress->getBaseShippingAmount());
        $shippingCostItem->taxAmount = $this->formatAmount($quoteShippingAddress->getBaseShippingTaxAmount());
        $request->item[] = $shippingCostItem;

        if (property_exists($request, 'item') && is_array($request->item)) {
            foreach ($request->item as $key => $item) {
                if ($item->unitPrice == 0 && $item->productCode != 'shipping_and_handling') {
                    unset($request->item[$key]);
                }
            }

            $request->item = array_values($request->item);
        }

        return $request;
    }

    /**
     * @return \stdClass
     */
    private function buildBaseRequest($storeId = null)
    {
        $request = new \stdClass();

        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $request->apPaymentType = self::AP_PAYMENT_TYPE;
        $request->merchantID = $this->config->getValue(\Payments\PayPal\Model\Config::KEY_MERCHANT_ID, $storeId);
        $request->deviceFingerprintID = $this->checkoutSession->getData('fingerprint_id');
        if ($developerId = $this->config->getDeveloperId()) {
            $request->developerId = $developerId;
        }

        if ($storeId) {
            $request->storeId = $storeId;
        }

        return $request;
    }

    /**
     * @param Quote\Item[] $items
     * @return string
     */
    private function aggregateTaxAmountFromItems($items)
    {
        $taxAmount = 0;
        foreach ($items as $item) {
            $taxAmount += (float) $item->getBaseTaxAmount();
        }

        return $this->formatAmount($taxAmount);
    }
}
