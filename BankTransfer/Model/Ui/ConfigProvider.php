<?php
namespace Payments\BankTransfer\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Payments\BankTransfer\Model\Config;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const BANK_TRANSFER_CODE = 'payments_bank_transfer';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * Constructor
     *
     * @param Config $config
     */
    public function __construct(\Payments\BankTransfer\Model\Config $config, \Magento\Framework\UrlInterface $url)
    {
        $this->config = $config;
        $this->url = $url;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'payments_bank_transfer_ideal' => [
                    'active' => $this->config->isMethodActive('ideal'),
                    'title' => $this->config->getMethodTitle('ideal'),
                    'placeOrderUrl' => $this->url->getUrl('paymentsbt/index/pay'),
                ],
                'payments_bank_transfer_sofort' => [
                    'active' => $this->config->isMethodActive('sofort'),
                    'title' => $this->config->getMethodTitle('sofort'),
                    'placeOrderUrl' => $this->url->getUrl('paymentsbt/index/pay'),
                    'bankCode' => 'sofort',
                ],
                'payments_bank_transfer_bancontact' => [
                    'active' => $this->config->isMethodActive('bancontact'),
                    'title' => $this->config->getMethodTitle('bancontact'),
                    'placeOrderUrl' => $this->url->getUrl('paymentsbt/index/pay'),
                    'bankCode' => 'bancontact',
                ],
                'payments_bank_transfer_eps' => [
                    'active' => $this->config->isMethodActive('eps'),
                    'title' => $this->config->getMethodTitle('eps'),
                    'placeOrderUrl' => $this->url->getUrl('paymentsbt/index/pay'),
                    'bankCode' => 'eps',
                ],
                'payments_bank_transfer_giro' => [
                    'active' => $this->config->isMethodActive('giro'),
                    'title' => $this->config->getMethodTitle('giro'),
                    'placeOrderUrl' => $this->url->getUrl('paymentsbt/index/pay'),
                    'bankCode' => 'giro',
                ],
            ]
        ];
    }
}
