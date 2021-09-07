<?php
namespace Payments\SecureAcceptance\Gateway\Response\Flex;


class PublicKeyHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Model\Jwt\JwtProcessorInterface
     */
    private $jwtProcessor;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Model\Jwt\JwtProcessorInterface $jwtProcessor
    ) {
        $this->subjectReader = $subjectReader;
        $this->jwtProcessor = $jwtProcessor;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDo = $this->subjectReader->readPayment($handlingSubject);

        $jwtString = $response['keyId'] ?? null;

        if (!$jwtString) {
            throw new \Magento\Payment\Gateway\Command\CommandException(__('Cannot get JWT from the gateway.'));
        }

        $publicKey = $this->jwtProcessor->getPublicKey($jwtString);

        $paymentDo->getPayment()->setAdditionalInformation('microformPublicKey', $publicKey);
    }
}
