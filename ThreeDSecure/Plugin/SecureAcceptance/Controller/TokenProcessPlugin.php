<?php
namespace Payments\ThreeDSecure\Plugin\SecureAcceptance\Controller;


class TokenProcessPlugin
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\ThreeDSecure\Gateway\Config\Config
     */
    private $paConfig;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\ThreeDSecure\Gateway\Config\Config $paConfig
    ) {
        $this->config = $config;
        $this->paConfig = $paConfig;
    }


    public function afterExecute(\Payments\SecureAcceptance\Controller\SecureAcceptance\TokenProcess $subject, $result)
    {

        if (!$this->paConfig->isEnabled()) {
            return $result;
        }

        if ($result instanceof \Magento\Framework\View\Result\Page) {
            $result->getLayout()->getUpdate()->addHandle(['payments_iframe_payment_response_redirect_3ds']);
        }

        if ($result instanceof \Magento\Framework\View\Result\Layout && !$this->config->isSilent() && $this->config->getUseIFrame()) {
            $result->getLayout()->getUpdate()->addHandle('payments_iframe_payment_response_hosted_iframe_3ds');
        }

        return $result;
    }


}
