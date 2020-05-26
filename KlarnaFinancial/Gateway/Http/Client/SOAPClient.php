<?php
namespace Payments\KlarnaFinancial\Gateway\Http\Client;

use Payments\Core\Service\AbstractConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Payments\Core\Model\LoggerInterface;
use SoapClient as stdSoapClient;

class SOAPClient extends \Payments\Core\Service\AbstractConnection implements ClientInterface
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param stdSoapClient|null $client
     * @throws \Exception
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Payments\Core\Model\LoggerInterface $logger,
        stdSoapClient $client = null
    ) {
        parent::__construct($scopeConfig, $logger);

        /**
         * Added soap client as parameter to be able to mock in unit tests.
         */
        if ($client !== null) {
            $this->setSoapClient($client);
        }
    }

    /**
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
            $response = $this->client->runTransaction($request);
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
