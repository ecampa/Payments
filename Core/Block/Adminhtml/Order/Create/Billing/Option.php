<?php
namespace Payments\Core\Block\Adminhtml\Order\Create\Billing;

use Payments\Core\Helper\Data;

/**
 * Show tokens for admin order
 */
class Option extends \Magento\Sales\Block\Adminhtml\Order\Create\Billing\Method\Form
{

    /** @var Data */
    private $helper;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Payments\Core\Helper\Data $helper,
        array $data
    ) {
        parent::__construct($context, $paymentHelper, $methodSpecificationFactory, $sessionQuote, $data);
        $this->helper = $helper;
    }
}
