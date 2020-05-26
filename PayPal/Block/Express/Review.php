<?php

namespace Payments\PayPal\Block\Express;

class Review extends \Magento\Paypal\Block\Express\Review
{
    /**
     * Paypal controller path
     *
     * @var string
     */
    protected $_controllerPath = 'paymentspaypal/express';

    public function canEditShippingMethod()
    {
        return true;
    }
}
