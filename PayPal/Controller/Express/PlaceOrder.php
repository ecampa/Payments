<?php
namespace Payments\PayPal\Controller\Express;

class PlaceOrder extends \Payments\PayPal\Controller\Express\AbstractExpress\PlaceOrder
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = \Magento\Paypal\Model\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \Payments\PayPal\Model\Config::CODE;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = \Payments\PayPal\Model\Express\Checkout::class;
}
