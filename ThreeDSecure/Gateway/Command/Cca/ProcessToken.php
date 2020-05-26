<?php
namespace Payments\ThreeDSecure\Gateway\Command\Cca;

class ProcessToken implements \Magento\Payment\Gateway\CommandInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Lcobucci\JWT\Parser
     */
    private $JwtParser;

    /**
     * @var \Magento\Payment\Gateway\Validator\ValidatorInterface
     */
    private $validator;
    /**
     * @var \Magento\Payment\Gateway\Command\Result\ArrayResultFactory
     */
    private $arrayResultFactory;

    /**
     * @var \Payments\Core\Model\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Payment\Gateway\Validator\ValidatorInterface $validator,
        \Magento\Payment\Gateway\Command\Result\ArrayResultFactory $arrayResultFactory,
        \Lcobucci\JWT\Parser $JwtParser,
        \Payments\Core\Model\LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->JwtParser = $JwtParser;
        $this->validator = $validator;
        $this->arrayResultFactory = $arrayResultFactory;
        $this->logger = $logger;
    }

    /**
     * Decodes and validates response jwt
     *
     * @param array $commandSubject
     * @return null|\Magento\Payment\Gateway\Command\ResultInterface
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {

        $paymentDo = $this->subjectReader->readPayment($commandSubject);

        /** @var \Magento\Quote\Api\Data\PaymentInterface $payment */
        $payment = $paymentDo->getPayment();

        if (!$payment->getExtensionAttributes() || !$payment->getExtensionAttributes()->getCcaResponse()) {
            throw new \InvalidArgumentException('Token must be provided');
        }

        $ccaResponse = $payment->getExtensionAttributes()->getCcaResponse();
        $parsedToken = $this->JwtParser->parse($ccaResponse);

        if ($this->validator !== null) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $parsedToken])
            );
            if (!$result->isValid()) {
                $this->processErrors($result);
            }
        }

        $this->logger->debug('Received JWT: ' . $ccaResponse);
        $this->logger->debug('JWT Payload:' . var_export((array)$parsedToken->getClaim('Payload'), true));

        return $this->arrayResultFactory->create([
                'array' => [
                    'token' => $ccaResponse,
                    'parsedToken' => $parsedToken
                ]
            ]);
    }

    /**
     * @param \Magento\Payment\Gateway\Validator\ResultInterface $result
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    private function processErrors(\Magento\Payment\Gateway\Validator\ResultInterface $result)
    {

        $messages = [];

        foreach ($result->getFailsDescription() as $message) {
            $messages[] = $message;
        }

        throw new \Magento\Payment\Gateway\Command\CommandException(
            (empty($messages)) ? __('Invalid CCA response') : $this->formatMessages($messages)
        );
    }

    private function formatMessages(array $messages)
    {
        return __(
            implode(
                \PHP_EOL,
                array_map(
                    function ($text) {
                        return __($text);
                    },
                    $messages
                )
            )
        );
    }
}
