<?php
namespace Payments\KlarnaFinancial\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Payments\Core\Model\LoggerInterface;
use SoapClient as stdSoapClient;
use Payments\Core\Service\MultiMidAbstractConnection;
use Payments\KlarnaFinancial\Gateway\Config\Config;

class SOAPClient extends \Payments\Core\Service\MultiMidAbstractConnection implements ClientInterface
{
	 /**
     * @var Config
     */
    private $gatewayConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param stdSoapClient|null $client
	 * @param Config $gatewayConfig
     * @throws \Exception
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Payments\Core\Model\LoggerInterface $logger,
		\Payments\KlarnaFinancial\Gateway\Config\Config $gatewayConfig,
        stdSoapClient $client = null
    ) {
		$this->gatewayConfig = $gatewayConfig;
		$storeId = $this->getCurrentStoreId();
        parent::__construct($scopeConfig, $logger, $this->gatewayConfig->getMerchantId($storeId), $this->gatewayConfig->getTransactionKey($storeId));

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
		$request->merchantID = $this->getMid();
        $dmenabled = 'false';
        $request->decisionManager = [
            'enabled' => $dmenabled,
        ];
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
