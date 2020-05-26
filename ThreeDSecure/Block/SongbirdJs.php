<?php
namespace Payments\ThreeDSecure\Block;

class SongbirdJs extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Payments\ThreeDSecure\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Payments\ThreeDSecure\Gateway\Config\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    public function isSandbox()
    {
        return $this->config->isTestMode();
    }
}
