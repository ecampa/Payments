<?php
namespace Payments\ThreeDSecure\Block\Transparent;

class Iframe extends \Magento\Payment\Block\Transparent\Iframe
{

    /**
     * @var \Payments\ThreeDSecure\Gateway\Config\Config
     */
    private $threeDsConfig;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Payments\ThreeDSecure\Gateway\Config\Config $threeDsConfig,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->threeDsConfig = $threeDsConfig;
        $this->config = $config;
    }

    public function getParams()
    {
        $params = parent::getParams();

        if ($this->config->isSilent()) {
            return $params;
        }

        return array_merge($params, [
            '3ds_active' => true
        ]);
    }
}
