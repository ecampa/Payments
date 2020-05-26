<?php
namespace Payments\SecureAcceptance\Block\Adminhtml\Transparent;

class Form extends \Magento\Payment\Block\Adminhtml\Transparent\Form
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
        $this->config = $config;
        parent::__construct($context, $paymentConfig, $checkoutSession, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addSaveForLaterChild();
        return $this;
    }

    public function setMethod(\Magento\Payment\Model\MethodInterface $method)
    {
        /** @var \Magento\Payment\Block\Form $saveForLaterBlock */
        if ($saveForLaterBlock = $this->getChildBlock('field_save_for_later')) {
            $saveForLaterBlock->setMethod($method);
        }

        return parent::setMethod($method);
    }

    private function addSaveForLaterChild()
    {
        $this->addChild(
            'field_save_for_later',
            \Payments\SecureAcceptance\Block\Adminhtml\Transparent\Form\SaveForLater::class,
            [
                'template' => 'Payments_SecureAcceptance::payment/save_for_later.phtml',
            ]
        );
    }

}
