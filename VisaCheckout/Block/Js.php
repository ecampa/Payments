<?php
namespace Payments\VisaCheckout\Block;

use Magento\Framework\View\Element\Template;

class Js extends \Magento\Framework\View\Element\Template
{
    protected $config;

    /**
     * Js Constructor
     *
     * @param Template\Context $context
     * @param \Payments\VisaCheckout\Gateway\Config\Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Payments\VisaCheckout\Gateway\Config\Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }
    
    /**
     * Check Is sandbox enable or not
     *
     * @return boolean
     */
    public function isSandbox()
    {
        return $this->config->isTest();
    }
}
