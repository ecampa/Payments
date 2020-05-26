<?php
namespace Payments\SecureAcceptance\Plugin\Session;


class SidResolverPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

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
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Payments\SecureAcceptance\Model\SignatureManagementInterface $signatureManagement
    ) {
        $this->request = $request;
        $this->config = $config;
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

        if (!$this->signatureManagement->validateSignature($this->request->getParams(), $this->getSecretKey())) {
            return $result;
        }

        return $this->encryptor->decrypt($encryptedSid);
    }


    private function getSecretKey()
    {
        if ($this->config->isSilent()) {
            return $this->config->getSopSecretKey();
        }
        return $this->config->getAuthSecretKey();
    }

}
