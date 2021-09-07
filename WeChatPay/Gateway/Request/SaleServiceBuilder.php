<?php
namespace Payments\WeChatPay\Gateway\Request;

class SaleServiceBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Payments\WeChatPay\Gateway\Config\Config
     */
    private $config;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Payments\WeChatPay\Gateway\Config\Config $config
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Payments\WeChatPay\Gateway\Config\Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $successUrl = $this->config->getWeChatSuccessUrl();
        $successUrl = strpos($successUrl, 'http') !== 0
            ? $this->storeManager->getStore()->getUrl($successUrl)
            : $successUrl;

        return [
            'successURL' => $successUrl,
            'transactionTimeout' => $this->config->getQrExpirationTime()
        ];
    }
}
