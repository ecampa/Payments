<?php

namespace Payments\PayPal\Gateway\Http\Client;

use Payments\Core\Model\LoggerInterface;
use Payments\Core\Service\AbstractConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class SOAPClient extends \Payments\Core\Service\AbstractConnection implements ClientInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @throws \Exception
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Payments\Core\Model\LoggerInterface $logger
    ) {
        parent::__construct($scopeConfig, $logger);
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws \Exception
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = (object) $transferObject->getBody();

        $log = [
            'request' => (array) $request,
            'client' => static::class
        ];

        $response = [];

        try {
            $response = $this->client->runTransaction($request);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        } finally {
            $log['response'] = (array) $response;
            $this->logger->debug($log);
        }

        return (array) $response;
    }
}
