<?php
namespace Payments\SecureAcceptance\Block\Customer;

use Payments\SecureAcceptance\Gateway\Config\Config;
use Payments\SecureAcceptance\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

class CardRenderer extends AbstractCardRenderer
{
    /**
     * @var Config
     */
    private $gatewayConfig;

    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $iconsProvider, $data);
        $this->gatewayConfig = $config;
    }

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE;
    }

    /**
     * @return string
     */
    public function getNumberLast4Digits()
    {
        return $this->getTokenDetails()['maskedCC'];
    }

    /**
     * @return string
     */
    public function getExpDate()
    {
        return $this->getTokenDetails()['expirationDate'];
    }

    /**
     * @return string
     */
    public function getIconUrl()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['url'];
    }

    /**
     * @return int
     */
    public function getIconHeight()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['height'];
    }

    /**
     * @return int
     */
    public function getIconWidth()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['width'];
    }

    public function getPaymentMethodName()
    {
        return $this->gatewayConfig->getTitle();
    }
}
