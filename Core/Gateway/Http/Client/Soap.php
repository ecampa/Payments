<?php
namespace Payments\Core\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class Soap extends \Payments\Core\Service\MultiMidAbstractConnection implements ClientInterface
{
    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws LocalizedException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = [];

        $request = (object) $transferObject->getBody();
        $log = [
            'request' => (array) $request,
            'client' => static::class
        ];

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
