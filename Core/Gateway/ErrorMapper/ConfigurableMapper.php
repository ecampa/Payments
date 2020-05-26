<?php


namespace Payments\Core\Gateway\ErrorMapper;

class ConfigurableMapper implements \Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface
{

    /**
     * @var \Payments\Core\Model\Config
     */
    private $config;

    public function __construct(
        \Payments\Core\Model\Config $config
    ) {

        $this->config = $config;
    }


    /**
     * Returns customized error message by provided code.
     * If message not found `null` will be returned.
     *
     * @param string $code
     * @return \Magento\Framework\Phrase|null
     */
    public function getMessage(string $code)
    {

        if (!$this->config->getValue(\Payments\Core\Model\AbstractGatewayConfig::KEY_SHOW_EXACT_ERROR)) {
            return __('Transaction has been declined. Please try again later.');
        }
        return __($code);
    }
}
