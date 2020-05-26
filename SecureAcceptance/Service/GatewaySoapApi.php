<?php
namespace Payments\SecureAcceptance\Service;

use Payments\Core\Service\AbstractConnection;
use Magento\Framework\Exception\LocalizedException;

class GatewaySoapApi extends \Payments\Core\Service\AbstractConnection
{
    /**
     * @param $request
     * @return null
     * @throws \Exception
     */
    public function run($request)
    {
        $log = [
            'request' => (array) $request,
            'client' => static::class
        ];

        $response = null;
        try {
            $this->initSoapClient();
            $request->merchantID = $this->merchantId;
            $response = $this->client->runTransaction($request);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        } finally {
            $log['response'] = (array) $response;
            $this->logger->debug($log);
        }

        return $response;
    }
}
