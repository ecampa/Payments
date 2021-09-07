<?php
namespace Payments\SecureAcceptance\Model\Backpost;


class Detector implements \Payments\SecureAcceptance\Model\Backpost\DetectorInterface
{

    const BACKPOST_UA_STRING = 'Secure Acceptance Back Post Agent';

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $httpHeader;

    public function __construct(\Magento\Framework\HTTP\Header $httpHeader)
    {
        $this->httpHeader = $httpHeader;
    }

    public function isBackpost()
    {
        return stripos($this->httpHeader->getHttpUserAgent(), static::BACKPOST_UA_STRING) !== false;
    }
}
