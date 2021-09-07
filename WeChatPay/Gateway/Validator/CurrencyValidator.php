<?php
namespace Payments\WeChatPay\Gateway\Validator;

class CurrencyValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    /**
     * @var \Payments\WeChatPay\Gateway\Config\Config
     */
    private $config;

    /**
     * @param \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory
     * @param \Payments\WeChatPay\Gateway\Config\Config $config
     */
    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Payments\WeChatPay\Gateway\Config\Config $config
    ) {
        parent::__construct($resultFactory);
        $this->config = $config;
    }

    /**
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (in_array($validationSubject['currency'], $this->getSupportedCurrencyList())) {
            return $this->createResult(true, []);
        }

        return $this->createResult(false, [__('The currency is not supported by WeChat Pay.')]);
    }

    /**
     * @return string[]
     */
    private function getSupportedCurrencyList()
    {
        return explode(',', $this->config->getValue('currency'));
    }
}
