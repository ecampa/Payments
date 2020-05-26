<?php
namespace Payments\ECheck\Gateway\Http\Client;

use Payments\Core\Service\AbstractConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class Client extends \Payments\Core\Service\AbstractConnection implements ClientInterface
{
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws LocalizedException
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
            $response = $this->getSoapClient()->runTransaction($request);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(__('Unable to retrieve payment information'));
        } finally {
            $log['response'] = (array) $response;
            $this->logger->debug($log);
        }

        return (array) $response;
    }
}
