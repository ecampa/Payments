<?php

namespace Payments\PayPal\Model;

class Credit extends \Payments\PayPal\Model\Payment
{
    /**
     * Payment method code
     * @var string
     */
    protected $_code  = \Payments\PayPal\Model\Config::CODE_CREDIT;

    /**
     * Checkout payment form
     * @var string
     */
    protected $_formBlockType = \Payments\PayPal\Block\Bml\Form::class;

    /**
     * Checkout redirect URL getter for onepage checkout
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->urlBuilder->getUrl('paymentspaypal/bml/start');
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) && $this->gatewayConfig->isPayPalCreditEnabled();
    }
}
