<?php
namespace Payments\SecureAcceptance\Block\Adminhtml\Transparent\Form;


class SaveForLater  extends \Magento\Payment\Block\Adminhtml\Transparent\Form
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $checkoutSession, $data);
        $this->config = $config;
    }

    public function isAdminVaultEnabled($storeId = null)
    {
        return $this->config->isVaultEnabledConfiguredOption($storeId) && $this->config->isVaultEnabledAdmin($storeId);
    }
}
