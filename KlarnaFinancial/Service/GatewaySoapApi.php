<?php
namespace Payments\KlarnaFinancial\Service;

use Payments\KlarnaFinancial\Helper\RequestDataBuilder;
use Magento\Framework\Session\SessionManagerInterface;
use Payments\Core\Model\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Payments\Core\Service\MultiMidAbstractConnection;
use Payments\KlarnaFinancial\Gateway\Config\Config;

class GatewaySoapApi extends \Payments\Core\Service\MultiMidAbstractConnection
{
    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
	
	 /**
     * @var Config
     */
    private $gatewayConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param RequestDataBuilder $dataBuilder
     * @param SessionManagerInterface $checkoutSession
     * @param \SoapClient|null $client
     * @param Config $gatewayConfig
     * @throws \Exception
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Payments\Core\Model\LoggerInterface $logger,
        \Payments\KlarnaFinancial\Helper\RequestDataBuilder $dataBuilder,
        SessionManagerInterface $checkoutSession,
		\Payments\KlarnaFinancial\Gateway\Config\Config $gatewayConfig,
        \SoapClient $client = null
    ) {
		$this->gatewayConfig = $gatewayConfig;
		$storeId = $this->getCurrentStoreId();
        parent::__construct($scopeConfig, $logger, $this->gatewayConfig->getMerchantId($storeId), $this->gatewayConfig->getTransactionKey($storeId));

        $this->requestDataBuilder = $dataBuilder;
        $this->checkoutSession = $checkoutSession;

        /**
         * Added soap client as parameter to be able to mock in unit tests.
         */
        if ($client !== null) {
            $this->setSoapClient($client);
        }
    }

    public function placeRequest($request)
    {
        $result = null;

        try {
			$request->merchantID = $this->getMid();
            $dmenabled = 'false';
            $request->decisionManager = [
                'enabled' => $dmenabled,
            ];
            $this->logger->debug([__METHOD__ => (array) $request]);
            $result = $this->client->runTransaction($request);
            $this->logger->debug([__METHOD__ => (array) $result]);

            if ($result->reasonCode == 100) {
                $this->checkoutSession->setKlarnaSessionRequestId($result->requestID);
                return $result->apSessionsReply->processorToken ?? null;
            }

            $this->logger->error("Unable to initialize Klarna Session. Error code: " . $result->reasonCode);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        return null;
    }
}
