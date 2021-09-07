<?php
namespace Payments\SecureAcceptance\Plugin\Session;

/**
 * Class SidResolverPlugin
 */
class SidResolverPlugin
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
     * SidResolverPlugin constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterfaceFactory $configProviderFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Payments\SecureAcceptance\Model\SignatureManagementInterface $signatureManagement
    ) {
        $this->request = $request;
        $this->configProvider = $configProviderFactory->create();
        $this->encryptor = $encryptor;
        $this->signatureManagement = $signatureManagement;
    }

    public function afterGetSid(
        \Magento\Framework\Session\SidResolverInterface $subject,
        $result,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        if ($result !== null) {
            return $result;
        }

        if (!$this->request->isPost()) {
            return $result;
        }

        if (!$encryptedSid = $this->request->getParam(
            'req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_SID
        )) {
            return $result;
        }

        $storeId = $this->getSaReqParam(\Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_STORE_ID);

        if (!$this->signatureManagement->validateSignature($this->request->getParams(), $this->configProvider->getSecretKey($storeId))) {
            return $result;
        }

        return $this->encryptor->decrypt($encryptedSid);
    }

    private function getSaReqParam($value)
    {
        return $this->request->getParam('req_' . $value, null);
    }

}
