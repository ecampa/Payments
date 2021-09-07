<?php
namespace Payments\SecureAcceptance\Gateway\Response\Soap;


class CleanSessionHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;

    public function __construct(\Magento\Framework\Session\SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        // second param true cleans the data
        $this->sessionManager->getData('cvv', true);
    }
}
