<?php

namespace Payments\SecureAcceptance\Gateway\Request\Soap;

class DecisionManagerMddBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $giftMessageHelper;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\GiftMessage\Helper\Message $giftMessageHelper,
        \Magento\Backend\Model\Auth $auth
    ) {
        $this->subjectReader = $subjectReader;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->giftMessageHelper = $giftMessageHelper;
        $this->auth = $auth;
    }

    /**
     * Builds DecisionManager MDD fields
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $quote = $this->getQuote();

        $request = [];
        $result = [];

        $result['field1'] = (int)$this->customerSession->isLoggedIn();// Registered or Guest Account

        $orders = $this->getOrders();

        if ($this->customerSession->isLoggedIn()) {
            $result['field2'] = $this->getAccountCreationDate(); // Account Creation Date

            $result['field3'] = $orders->getSize(); // Purchase History Count

            if ($orders->getSize() > 0) {
                $result['field4'] = $orders->getFirstItem()->getCreatedAt(); // Last Order Date
            }

            $result['field5'] = $this->getAccountAge();// Member Account Age (Days)
        }

        $result['field6'] = (int)($orders->getSize() > 0); // Repeat Customer
        $result['field20'] = $quote->getCouponCode(); //Coupon Code

        $result['field21'] = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount(); // Discount

        $result['field22'] = $this->getGiftMessage(); // Gift Message

        $result['field23'] = ($this->auth->isLoggedIn()) ? 'call center' : 'web'; //order source

        if (!$quote->getIsVirtual()) {
            if ($shippingAddress = $quote->getShippingAddress()) {
                $result['field31'] = $quote->getShippingAddress()->getShippingMethod();
                $result['field32'] = $quote->getShippingAddress()->getShippingDescription();
            }
        }

        foreach ($result as $key => $value) {
            if ($value !== null && !empty($value) && $value !== "" && $value !== null) {
                $request['merchantDefinedData'][$key] = $value;
            }
        }

        if ($fingerPrintId = $this->checkoutSession->getFingerprintId()) {
            $request['deviceFingerprintID'] = $fingerPrintId;

        }

        $request['billTo']['customerID'] = $order->getCustomerId();
        $request['billTo']['ipAddress'] = $order->getRemoteIp();

        return $request;
    }

    private function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    private function getOrders()
    {
        $field = 'customer_email';
        $value = $this->getQuote()->getCustomerEmail();
        if ($this->customerSession->isLoggedIn()) {
            $field = 'customer_id';
            $value = $this->customerSession->getCustomerId();
        }
        return $this->orderCollectionFactory->create()
            ->addFieldToFilter($field, $value)
            ->setOrder('created_at', 'desc');
    }

    private function getAccountCreationDate()
    {
        return $this->customerSession->getCustomerData()->getCreatedAt();
    }

    private function getAccountAge()
    {
        return round((time() - strtotime($this->customerSession->getCustomerData()->getCreatedAt())) / (3600 * 24));
    }

    private function getGiftMessage()
    {
        $message = $this->giftMessageHelper->getGiftMessage($this->getQuote()->getGiftMessageId());
        return $message->getMessage() ? $message->getMessage() : '';
    }
}
