<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;


class SidBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->session = $session;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {

        if (!$this->session->getSessionId()) {
            return [];
        }

        return [
            \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_SID => $this->encryptor->encrypt($this->session->getSessionId()),
        ];
    }
}
