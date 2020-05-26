<?php

namespace Payments\SecureAcceptance\Gateway\Http\Client;

use Payments\SecureAcceptance\Gateway\Request\AbstractRequest;

class TransparentResponsePlugin
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    public function aroundPlaceRequest(
        \Magento\Payment\Gateway\Http\ClientInterface $subject,
        callable $proceed,
        \Magento\Payment\Gateway\Http\TransferInterface $transferObject
    ) {

        if ($response = $this->coreRegistry->registry(\Payments\SecureAcceptance\Gateway\Request\AbstractRequest::TRANSPARENT_RESPONSE_KEY)) {
            $this->coreRegistry->unregister(\Payments\SecureAcceptance\Gateway\Request\AbstractRequest::TRANSPARENT_RESPONSE_KEY);
            return $response;
        }

        return $proceed($transferObject);
    }
}
