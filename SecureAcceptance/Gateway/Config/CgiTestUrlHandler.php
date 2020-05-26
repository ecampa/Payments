<?php

namespace Payments\SecureAcceptance\Gateway\Config;

class CgiTestUrlHandler extends \Payments\SecureAcceptance\Gateway\Config\CgiUrlHandler
{

    protected function getServiceUrl()
    {
        return $this->config->getSopServiceUrlTest();
    }
}
