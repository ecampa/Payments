<?php
namespace Payments\WeChatPay\Block\Order\Info;

class Qr extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Payments\WeChatPay\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\WeChatPay\Model\CurrentOrderResolver
     */
    private $currentOrderResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Payments\WeChatPay\Gateway\Config\Config $config
     * @param \Payments\WeChatPay\Model\CurrentOrderResolver $currentOrderResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Payments\WeChatPay\Gateway\Config\Config $config,
        \Payments\WeChatPay\Model\CurrentOrderResolver $currentOrderResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->currentOrderResolver = $currentOrderResolver;
        $this->setTemplate('order/info/buttons/qr.phtml');
    }

    /**
     * @return string
     */
    public function isApplicable()
    {
        return $this->getOrder()
            && $this->getOrder()->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
            && $this->getOrder()->getPayment()->getMethod() == \Payments\WeChatPay\Model\Ui\ConfigProvider::CODE;
    }

    /**
     * @return int
     */
    public function getPopupMessageDelay()
    {
        return $this->config->getPopupMessageDelay();
    }

    /**
     * @return int
     */
    public function getCheckStatusFrequency()
    {
        return $this->config->getCheckStatusFrequency();
    }

    /**
     * @return int
     */
    public function getMaxStatusRequests()
    {
        return $this->config->getMaxStatusRequests();
    }

    /**
     * @return bool|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->currentOrderResolver->get();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Pay with WeChat QR Code');
    }
}
