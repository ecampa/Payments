<?php
namespace Payments\SecureAcceptance\Plugin\Store\Api;

class StoreResolverInterfacePlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Payments\SecureAcceptance\Model\SignatureManagementInterface
     */
    private $signatureManagement;

    /**
     * @var \Payments\Core\Model\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    private $requestSubstituted = false;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterfaceFactory $configProviderFactory,
        \Payments\SecureAcceptance\Gateway\Config\ConfigFactory $configFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Payments\SecureAcceptance\Model\SignatureManagementInterface $signatureManagement,
        \Payments\Core\Model\LoggerInterface $logger,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->request = $request;
        $this->configProvider = $configProviderFactory->create();
        $this->encryptor = $encryptor;
        $this->signatureManagement = $signatureManagement;
        $this->logger = $logger;
        $this->storeRepository = $storeRepository;
    }

    public function beforeGetCurrentStoreId(\Magento\Store\Api\StoreResolverInterface $subject)
    {
        if ($this->requestSubstituted) {
            return;
        }

        if (!$this->request->isPost()) {
            return;
        }

        if (!$storeId = $this->getSaReqParam(\Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_STORE_ID)) {
            return;
        }

        if (!$this->signatureManagement->validateSignature(
            $this->request->getParams(),
            $this->configProvider->getSecretKey($storeId)
        )) {
            $this->logger->warning(static::class . ': Invalid SA signature for store ' . $storeId);
            return;
        }

        try {
            $store = $this->storeRepository->getActiveStoreById($storeId);
        } catch (\Exception $e) {
            $this->logger->warning(static::class . ': No store with found with ID ' . $storeId);
            return;
        }

        $originalParams = $this->request->getParams();

        $this->request->setParams(
            array_merge(
                $originalParams,
                [
                    \Magento\Store\Api\StoreResolverInterface::PARAM_NAME => $store->getCode(),
                ]
            )
        );

        $this->requestSubstituted = true;
    }

    private function getSaReqParam($value)
    {
        return $this->request->getParam('req_' . $value, null);
    }

}
