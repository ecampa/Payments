<?php

namespace Payments\ThreeDSecure\Gateway\Request\Cca;

class PayerAuthValidateBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    const COMMAND_PROCESS_TOKEN = 'processToken';

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    private $commandPool;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
    ) {
        $this->subjectReader = $subjectReader;
        $this->commandPool = $commandPool;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $commandResult = $this->commandPool->get(self::COMMAND_PROCESS_TOKEN)->execute($buildSubject);

        $resultArray = $commandResult->get();

        /** @var \Lcobucci\JWT\Token $parsedToken */
        $parsedToken = $resultArray['parsedToken'];
        $payload = $parsedToken->claims()->get('Payload');
        $processorTransactionId = $payload->Payment->ProcessorTransactionId;
        return [
            'payerAuthValidateService' => [
                'run' => 'true',
                'authenticationTransactionID'=> $processorTransactionId,
            ]
        ];
    }
}
