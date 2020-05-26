<?php

namespace Payments\SecureAcceptance\Model\Ui\Adminhtml;

use Payments\SecureAcceptance\Gateway\Config\Config;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;

/**
 * Class TokenUiComponentProvider
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;

    /**
     * Config
     */
    private $config;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param Config $config
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->componentFactory = $componentFactory;
        $this->config = $config;
    }

    /**
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $jsonDetails = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CC_VAULT_CODE,
                    'title' => $this->config->getTitle(),
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
                    'template' => 'Payments_SecureAcceptance::vault/renderer.phtml'

                ],
                'name' => \Magento\Framework\View\Element\Template::class
            ]
        );

        return $component;
    }
}
