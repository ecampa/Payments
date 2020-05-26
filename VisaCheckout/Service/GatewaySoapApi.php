<?php
namespace Payments\VisaCheckout\Service;

use Payments\Core\Model\LoggerInterface;
use Payments\Core\Service\AbstractConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;

class GatewaySoapApi extends \Payments\Core\Service\AbstractConnection
{
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Payments\Core\Model\LoggerInterface $logger,
        \SoapClient $client = null
    ) {
        parent::__construct($scopeConfig, $logger);
        /**
         * Added soap client as parameter to be able to mock in unit tests.
         */
        if ($client !== null) {
            $this->setSoapClient($client);
        }
    }

    public function request(\stdClass $requestBody)
    {
        $result = null;
        try {
            $this->logger->debug([__METHOD__ => (array) $requestBody]);
            $result = $this->client->runTransaction($requestBody);
            $this->logger->debug([__METHOD__ => (array) $result]);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
