<?php
namespace Payments\Recaptcha\Observer;

class IsCheckRequired implements \MSP\ReCaptcha\Model\IsCheckRequiredInterface
{

    /**
     * @var \Payments\Recaptcha\Model\Config
     */
    private $config;

    public function __construct(
        \Payments\Recaptcha\Model\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Return true if check is required
     * @return bool
     */
    public function execute()
    {
        return $this->config->isEnabled();
    }
}
