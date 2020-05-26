<?php
namespace Payments\SecureAcceptance\Gateway\Http\Client;

use Payments\Core\Model\LoggerInterface;
use Payments\Core\Service\AbstractConnection;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class SOAPClient extends \Payments\Core\Service\AbstractConnection implements ClientInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Payments\Core\Model\LoggerInterface $logger,
        \Magento\Framework\App\State $state,
        \SoapClient $client = null
    ) {
        parent::__construct($scopeConfig, $logger);

        /**
         * Added soap client as parameter to be able to mock in unit tests.
         */
        if ($client !== null) {
            $this->setSoapClient($client);
        }
        $this->state = $state;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws \Exception
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = (object) $transferObject->getBody();

        if (isset($request->storeId)) {
            $this->setCredentialsByStore($request->storeId);
            $this->initSoapClient();
            unset($request->storeId);
        }

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

            if ($this->state->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                $this->logger->debug(
                    [
                        'raw_request' => $this->client->__getLastRequest(),
                        'raw_response' => $this->client->__getLastResponse(),
                    ]
                );
            }

        }

        return (array) $response;
    }
}
