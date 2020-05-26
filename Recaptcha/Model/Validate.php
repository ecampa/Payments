<?php

namespace Payments\Recaptcha\Model;


class Validate implements \MSP\ReCaptcha\Api\ValidateInterface
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
     * @inheritDoc
     */
    public function validate($reCaptchaResponse, $remoteIp)
    {
        if (!$reCaptchaResponse) {
            return false;
        }

        $secret = $this->config->getPrivateKey();
        $reCaptcha = new \ReCaptcha\ReCaptcha($secret);
        $res = $reCaptcha->verify($reCaptchaResponse, $remoteIp);

        return $res->isSuccess();
    }
}
