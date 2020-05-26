<?php
namespace Payments\VisaCheckout\Controller\Index;

class SaveShippingMethod extends \Magento\Paypal\Controller\Express\AbstractExpress\SaveShippingMethod
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = \Payments\VisaCheckout\Gateway\Config\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \Payments\VisaCheckout\Gateway\Config\Config::CODE;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = \Payments\VisaCheckout\Model\Checkout::class;
}
