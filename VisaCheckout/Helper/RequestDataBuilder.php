<?php
namespace Payments\VisaCheckout\Helper;

use Payments\Core\Helper\AbstractDataBuilder;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Payments\VisaCheckout\Gateway\Config\Config;

class RequestDataBuilder extends \Payments\Core\Helper\AbstractDataBuilder
{
    const PAYMENT_SOLUTION = 'visacheckout';

    /**
     * @var Config
     */
    private $gatewayConfig;

    /**
     * RequestDataBuilder constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param CheckoutHelper $checkoutHelper
     * @param Config $gatewayConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderGridCollectionFactory
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\GiftMessage\Model\Message $giftMessage
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        CheckoutHelper $checkoutHelper,
        \Payments\VisaCheckout\Gateway\Config\Config $gatewayConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderGridCollectionFactory,
        \Magento\Backend\Model\Auth $auth,
        \Magento\GiftMessage\Model\Message $giftMessage
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $checkoutHelper,
            $orderCollectionFactory,
            $orderGridCollectionFactory,
            $auth,
            $giftMessage
        );
        
        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * @param $callId
     * @param $merchantReferenceNumber
     * @return object
     */
    public function buildVisaDecryptRequestData($callId, $merchantReferenceNumber)
    {
        $request = new \stdClass();

        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->partnerSolutionID = self::PARTNER_SOLUTION_ID;
        $request->developerId = $this->gatewayConfig->getDeveloperId();
        $request->merchantReferenceCode = $merchantReferenceNumber;

        $getVisaCheckoutDataService = new \stdClass();
        $getVisaCheckoutDataService->run = "true";
        $request->getVisaCheckoutDataService =  $getVisaCheckoutDataService;

        $request->paymentSolution = self::PAYMENT_SOLUTION;

        $vc = new \stdClass();
        $vc->orderID = $callId;

        $request->vc = $vc;

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \stdClass
     */
    public function buildAuthorizationRequestData(\Magento\Payment\Model\InfoInterface $payment)
    {
        $quote = $this->checkoutSession->getQuote();

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->partnerSolutionID = self::PARTNER_SOLUTION_ID;
        $request->developerId = $this->gatewayConfig->getDeveloperId();
        $request->merchantReferenceCode = $quote->getReservedOrderId();

        $ccAuthService = new \stdClass();
        $ccAuthService->run = "true";
        $request->ccAuthService = $ccAuthService;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $quote->getQuoteCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($quote->getGrandTotal());
        $request->purchaseTotals = $purchaseTotals;

        $request = $this->buildRequestItems($quote->getAllVisibleItems(), $request);

        $request->billTo = $this->buildAddress($quote->getBillingAddress());
        $request->shipTo = $this->buildAddress($quote->getShippingAddress());

        $request->customerID = (!is_null($this->customerSession->getCustomerId())) ? $this->customerSession->getCustomerId() : 'guest';

        $request->paymentSolution = self::PAYMENT_SOLUTION;

        $request->merchantDefinedData = $this->buildDecisionManagerFields($quote);

        $vc = new \stdClass();
        $vc->orderID = $payment->getAdditionalInformation("callId");
        $request->vc = $vc;

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return \stdClass
     */
    public function buildCaptureRequestData(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $merchantReferenceCode = $payment->getAdditionalInformation('merchantReferenceCode');
        $currency = $payment->getAdditionalInformation('currency');

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->partnerSolutionID = self::PARTNER_SOLUTION_ID;
        $request->developerId = $this->gatewayConfig->getDeveloperId();
        $request->merchantReferenceCode = $merchantReferenceCode;

        $ccCaptureService = new \stdClass();
        $ccCaptureService->run = "true";
        $ccCaptureService->authRequestID = $payment->getAdditionalInformation("requestID");
        $this->buildCaptureSequence($payment, $ccCaptureService, $amount);

        $request->ccCaptureService = $ccCaptureService;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $currency;
        $purchaseTotals->grandTotalAmount = $this->formatAmount($amount);
        $request->purchaseTotals = $purchaseTotals;

        $request->paymentSolution = self::PAYMENT_SOLUTION;
        $request->orderRequestToken = $payment->getAdditionalInformation("requestToken");

        $request->customerID = (!is_null($this->customerSession->getCustomerId())) ? $this->customerSession->getCustomerId() : 'guest';

        $request = $this->buildRequestItems($payment->getOrder()->getAllItems(), $request, $payment->getOrder());

        $request->billTo = $this->buildAddress($payment->getOrder()->getBillingAddress());
        $request->shipTo = $this->buildAddress($payment->getOrder()->getShippingAddress());

        $vc = new \stdClass();
        $vc->orderID = $payment->getAdditionalInformation("callID");
        $request->vc = $vc;

        return $request;
    }

    /**
     * @return \stdClass
     */
    public function buildSettlementRequestData()
    {
        $request = new \stdClass();

        $ccCaptureService = new \stdClass();
        $ccCaptureService->run = "true";
        $request->ccCaptureService = $ccCaptureService;

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \stdClass
     */
    public function buildVoidRequestData(\Magento\Payment\Model\InfoInterface $payment)
    {
        $merchantReferenceCode = $payment->getAdditionalInformation('merchantReferenceCode');

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->partnerSolutionID = self::PARTNER_SOLUTION_ID;
        $request->developerId = $this->gatewayConfig->getDeveloperId();
        $request->merchantReferenceCode = $merchantReferenceCode;

        $voidService = new \stdClass();
        $voidService->run = "true";
        $voidService->voidRequestID = $payment->getAdditionalInformation("requestID");
        $request->voidService = $voidService;

        return $request;
    }


    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \stdClass
     */
    public function buildAuthorizeReversalRequestData(\Magento\Payment\Model\InfoInterface $payment)
    {
        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->merchantReferenceCode = $payment->getOrder()->getIncrementId();

        $ccAuthReversalService = new \stdClass();
        $ccAuthReversalService->run = "true";
        $ccAuthReversalService->authRequestID = $payment->getAdditionalInformation("requestID");
        $request->ccAuthReversalService = $ccAuthReversalService;

        $request->paymentSolution = self::PAYMENT_SOLUTION;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getOrderCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatAmount($payment->getOrder()->getGrandTotal());
        $request->purchaseTotals = $purchaseTotals;

        $vc = new \stdClass();
        $vc->orderID = $payment->getAdditionalInformation("callID");
        $request->vc = $vc;

        return $request;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return \stdClass
     */
    public function buildRefundRequestData(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $merchantReferenceCode = $payment->getAdditionalInformation('merchantReferenceCode');
        $currency = $payment->getAdditionalInformation('currency');

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->partnerSolutionID = self::PARTNER_SOLUTION_ID;
        $request->developerId = $this->gatewayConfig->getDeveloperId();
        $request->merchantReferenceCode = $merchantReferenceCode;

        $ccCreditService = new \stdClass();
        $ccCreditService->run = "true";
        $ccCreditService->captureRequestID = $payment->getCcTransId();
        $request->ccCreditService = $ccCreditService;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $currency;
        $purchaseTotals->grandTotalAmount = $this->formatAmount($amount);
        $request->purchaseTotals = $purchaseTotals;

        $request->paymentSolution = self::PAYMENT_SOLUTION;

        $vc = new \stdClass();
        $vc->orderID = $payment->getAdditionalInformation("callID");
        $request->vc = $vc;

        $order = $payment->getOrder();

        $request = $this->buildRequestItems($payment->getOrder()->getAllItems(), $request, $order);

        $request->billTo = $this->buildAddress($order->getBillingAddress());
//        $request->card = $this->buildCard($payment); // apparently card information should not be passed.

        return $request;
    }

    /**
     * @param $quoteAddress
     * @return \stdClass
     */
    private function buildAddress($quoteAddress)
    {
        $address = new \stdClass();
        $address->city =  $quoteAddress->getData('city');
        $address->country = $quoteAddress->getData('country_id');
        $address->postalCode = $quoteAddress->getData('postcode');
        $address->state = $quoteAddress->getRegionCode();
        $address->street1 = $quoteAddress->getStreetLine(1);
        $address->email = $quoteAddress->getEmail();
        $address->firstName = $quoteAddress->getFirstname();
        $address->lastName = $quoteAddress->getLastname();

        if ($quoteAddress->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_BILLING) {
            $address->ipAddress = $this->_remoteAddress->getRemoteAddress();
        }

        return $address;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \stdClass
     */
    private function buildCard(\Magento\Payment\Model\InfoInterface $payment)
    {
        $card = new \stdClass();
        $card->accountNumber = $payment->getAdditionalInformation("accountNumber");
        $card->cardType = $payment->getAdditionalInformation("cardType");
        $card->expirationMonth = $payment->getAdditionalInformation("expirationMonth");
        $card->expirationYear = $payment->getAdditionalInformation("expirationYear");

        return $card;
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
        $shippingCostItem->productCode = 'shipping_and_handling';
        $shippingCostItem->unitPrice = $this->formatAmount($shippingCost);
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
