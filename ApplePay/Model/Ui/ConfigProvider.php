<?php
namespace Payments\ApplePay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Payments\ApplePay\Gateway\Config\Config;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const APPLEPAY_CODE = 'payments_applepay';

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param Config $config
     */
    public function __construct(\Payments\ApplePay\Gateway\Config\Config $config)
    {
        $this->config = $config;
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
                self::APPLEPAY_CODE => [
                    'active' => $this->config->isActive(),
                    'title' => $this->config->getTitle()
                ],
            ]
        ];
    }
}
