<?php
namespace Payments\VisaCheckout\Block;

class Review extends \Magento\Paypal\Block\Express\Review
{
    /**
     * VisaCheckout controller path
     *
     * @var string
     */
    protected $_controllerPath = 'paymentsvisa/index';

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        return \Payments\VisaCheckout\Block\Shortcut::SHORTCUT_IMAGE;
    }

    public function canEditShippingMethod()
    {
        return true;
    }
}
