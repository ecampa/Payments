<?php
namespace Payments\KlarnaFinancial\Helper;

use Payments\Core\Helper\AbstractDataBuilder;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Payments\KlarnaFinancial\Gateway\Config\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class RequestDataBuilder extends \Payments\Core\Helper\AbstractDataBuilder
{
    const PAYMENT_TYPE = 'KLI';
    const SESSION_TYPE_UPDATE = 'U';
    const SESSION_TYPE_CREATE = 'N';
    const DEFAULT_BILL_TO_COUNTRY = 'US';
    const DEFAULT_BILL_TO_STATE = 'NY';
    const DEFAULT_BILL_TO_POSTCODE = '10001';

    /**
     * @var Config
     */
    private $gatewayConfig;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $locale;

    /**
     * @var \Magento\Customer\Model\Address
     */
    private $address;

    /**
     * @var \Magento\Store\Model\Information
     */
    private $storeInformation;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    private $regionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\Information $storeInformation
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param CheckoutHelper $checkoutHelper
     * @param Config $gatewayConfig
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Locale\Resolver $locale
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\GiftMessage\Model\Message $giftMessage
     * @param \Magento\Customer\Model\Address $address
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\Information $storeInformation,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        CheckoutHelper $checkoutHelper,
        \Payments\KlarnaFinancial\Gateway\Config\Config $gatewayConfig,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Locale\Resolver $locale,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Backend\Model\Auth $auth,
        \Magento\GiftMessage\Model\Message $giftMessage,
        \Magento\Customer\Model\Address $address
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $checkoutHelper,
            $orderCollectionFactory,
            $auth,
            $giftMessage
        );
        $this->gatewayConfig = $gatewayConfig;
        $this->quoteRepository = $quoteRepository;
        $this->locale = $locale;
        $this->address = $address;
        $this->storeInformation = $storeInformation;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param bool $updateMode
     * @return \stdClass
     * @throws LocalizedException
     */
    public function buildSessionRequest($updateMode = false)
    {
        $quote = $this->checkoutSession->getQuote();
        $email = $quote->getCustomerEmail();

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->merchantReferenceCode = $quote->getReservedOrderId();

        $request = $this->buildRequestItems($quote->getAllVisibleItems(), $request);

        $request->apPaymentType = self::PAYMENT_TYPE;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $quote->getQuoteCurrencyCode();
        $purchaseTotals->grandTotalAmount = $quote->getGrandTotal();
        $request->purchaseTotals = $purchaseTotals;

        $successUrl = $this->_getUrl('checkout/onepage/success');
        $cancelOrFailureUrl = $this->_getUrl('*/*/cancel');

        $apSessionsService = new \stdClass();
        $apSessionsService->run = "true";
        $apSessionsService->cancelURL = $cancelOrFailureUrl;
        $apSessionsService->failureURL = $cancelOrFailureUrl;
        $apSessionsService->successURL = $successUrl;
        $apSessionsService->sessionsType = self::SESSION_TYPE_CREATE;

        if ($updateMode && $this->checkoutSession->getKlarnaSessionRequestId()) {
            $request->billTo = $this->buildAddress($quote->getBillingAddress(), $email);

            $request->shipTo = $quote->getIsVirtual()
                ? $this->buildAddress($quote->getBillingAddress(), $email)
                : $this->buildAddress($quote->getShippingAddress(), $email);

            $apSessionsService->sessionsRequestID = $this->checkoutSession->getKlarnaSessionRequestId();
            $apSessionsService->sessionsType = self::SESSION_TYPE_UPDATE;
            $request->apSessionsService = $apSessionsService;

            return $request;
        }

        $request->billTo = $quote->getBillingAddress()->getCountryId()
            ? (object)[
                'country' => $quote->getBillingAddress()->getCountryId(),
                'state' => $quote->getBillingAddress()->getRegionCode(),
                'postalCode' => $quote->getBillingAddress()->getPostcode(),
            ]
            : $this->buildDefaultAddress($quote->getBillingAddress());


        $request->apSessionsService = $apSessionsService;

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \stdClass
     */
    public function buildAuthorizationRequestData(\Magento\Payment\Model\InfoInterface $payment)
    {
        $quote = $this->checkoutSession->getQuote();
        $email = $quote->getCustomerEmail();

        $quote->collectTotals();

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->merchantReferenceCode = $quote->getReservedOrderId();

        $request->billTo = $this->buildAddress($quote->getBillingAddress(), $email);
        $request->shipTo = $quote->getIsVirtual()
            ? $this->buildAddress($quote->getBillingAddress(), $email)
            : $this->buildAddress($quote->getShippingAddress(), $email);

        $request = $this->buildRequestItems($quote->getAllVisibleItems(), $request);

        $request->apPaymentType = self::PAYMENT_TYPE;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $quote->getQuoteCurrencyCode();
        $purchaseTotals->grandTotalAmount = $quote->getGrandTotal();
        $request->purchaseTotals = $purchaseTotals;

        $apAuthService = new \stdClass();
        $apAuthService->run = "true";
        $apAuthService->preapprovalToken = $payment->getAdditionalInformation("authorizationToken");

        $request->apAuthService = $apAuthService;

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return \stdClass
     */
    public function buildCaptureRequestData(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->merchantReferenceCode = $order->getIncrementId();

        $request = $this->buildRequestItems($order->getAllVisibleItems(), $request);

        $request->apPaymentType = self::PAYMENT_TYPE;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $order->getOrderCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($amount);
        $request->purchaseTotals = $purchaseTotals;

        $apCaptureService = new \stdClass();
        $apCaptureService->run = "true";
        $apCaptureService->authRequestID = $payment->getCcTransId();

        $this->buildCaptureSequence($payment, $apCaptureService, $amount);

        $request->apCaptureService = $apCaptureService;

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \stdClass
     */
    public function buildVoidRequestData(\Magento\Payment\Model\InfoInterface $payment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->merchantReferenceCode = $order->getIncrementId();
        $request->apPaymentType = self::PAYMENT_TYPE;

        $apAuthReversalService = new \stdClass();
        $apAuthReversalService->run = "true";
        $apAuthReversalService->authRequestID = $payment->getCcTransId();
        $request->apAuthReversalService = $apAuthReversalService;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $order->getOrderCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($order->getGrandTotal());
        $request->purchaseTotals = $purchaseTotals;

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \stdClass
     */
    public function buildRefundRequestData(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->merchantReferenceCode = $order->getIncrementId();

        $request = $this->buildRequestItems($order->getAllVisibleItems(), $request);

        $request->apPaymentType = self::PAYMENT_TYPE;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $order->getOrderCurrencyCode();
        $purchaseTotals->grandTotalAmount = (float)$this->formatAmount($amount);
        $request->purchaseTotals = $purchaseTotals;

        $apRefundService = new \stdClass();
        $apRefundService->run = "true";
        $apRefundService->refundRequestID = $payment->getCcTransId();

        $request->apRefundService = $apRefundService;

        return $request;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param string $email
     * @return \stdClass
     * @throws LocalizedException
     */
    private function buildAddress($quoteAddress, $email)
    {
        $address = new \stdClass();

        if (! $email) {
            throw new LocalizedException(__('Email is required.'));
        }

        if (! $quoteAddress->getCountryId()) {
            return $this->buildDefaultAddress($quoteAddress, $email);
        }

        $address->email = $email;
        $address->city =  $quoteAddress->getCity();
        $address->country = $quoteAddress->getCountryId();
        $address->postalCode = $quoteAddress->getPostcode();
        $address->district = $quoteAddress->getRegionCode();
        $address->state = $quoteAddress->getRegionCode();
        $address->street1 = $quoteAddress->getStreetLine(1);
        if (strlen(trim($quoteAddress->getStreetLine(2)))) {
            $address->street2 = $quoteAddress->getStreetLine(2);
        }
        $address->firstName = $quoteAddress->getFirstname();
        $address->lastName = $quoteAddress->getLastname();
        $address->language = str_replace("_", "-", $this->locale->getLocale());

        return $address;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param string $email
     * @return \stdClass
     */
    private function buildDefaultAddress($quoteAddress, $email = null)
    {
        $address = new \stdClass();
        $address->email = $email;

        $storeInfo = $this->storeInformation->getStoreInformationObject(
            $quoteAddress->getQuote()->getStore()
        );

        $address->country = $storeInfo->getCountryId() ?: self::DEFAULT_BILL_TO_COUNTRY;
        $region = $this->regionFactory->create()->loadByName(
            $storeInfo->getRegion(),
            $address->country
        );
        $address->state = $region->getCode() ?: self::DEFAULT_BILL_TO_STATE;
        $address->postalCode = $storeInfo->getPostcode() ?: self::DEFAULT_BILL_TO_POSTCODE;

        return $address;
    }

    /**
     * @param array $items
     * @param \stdClass $request
     * @return mixed
     */
    private function buildRequestItems(array $items, \stdClass $request, $order = null)
    {
        $index = 0;
        foreach ($items as $i => $item) {
            $qty = $item->getQty();
            if (empty($qty)) {
                $qty = 1;
            }
            $amount = ($item->getPrice() - ($item->getDiscountAmount() / $qty));
            $requestItem = new \stdClass();
            $requestItem->id = $i;
            $requestItem->productName = $item->getName();
            $requestItem->productSKU = $item->getSku();
            $requestItem->quantity = (int) $qty;
            $requestItem->productCode = 'default';
            $requestItem->unitPrice = $this->formatAmount($amount);
            $requestItem->taxAmount = $this->formatAmount($item->getTaxAmount());
            $requestItem->totalAmount = $this->formatAmount(($amount * $qty) + $requestItem->taxAmount);
            $request->item[] = $requestItem;
            $index = $i;
        }

        $shippingCost = $this->checkoutSession->getQuote()->getShippingAddress()->getBaseShippingAmount();

        /** @var \Magento\Sales\Model\Order $order */
        if (!is_null($order)) {
            $shippingCost = $order->getShippingAmount();
        }

        $shippingCostItem = new \stdClass();
        $shippingCostItem->id = $index + 1;
        $shippingCostItem->productName = "shipping";
        $shippingCostItem->productSKU = "shipping";
        $shippingCostItem->quantity = (int) 1;
        $shippingCostItem->productCode = 'shipping';
        $shippingCostItem->unitPrice = $this->formatAmount($shippingCost);
        $shippingCostItem->taxAmount = $this->formatAmount(0);
        $shippingCostItem->totalAmount = $this->formatAmount($shippingCost);
        $request->item[] = $shippingCostItem;

        if (property_exists($request, 'item') && is_array($request->item)) {
            foreach ($request->item as $key => $item) {
                if ($item->unitPrice == 0) {
                    unset($request->item[$key]);
                }
            }

            $request->item = array_values($request->item);
        }

        return $request;
    }
}
