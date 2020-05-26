<?php
namespace Payments\SecureAcceptance\Gateway\Command;

class TokenHandleResponseCommand implements \Magento\Payment\Gateway\CommandInterface
{

    const COMMAND_NAME = 'process_token';

    /**
     * @var \Magento\Payment\Gateway\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * @var \Magento\Payment\Gateway\Response\HandlerInterface
     */
    private $handler;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\Core\Model\LoggerInterface
     */
    private $logger;

    /**
     * @var \Payments\Core\Gateway\ErrorMapper\ConfigurableMapper
     */
    private $errorMessageMapper;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ValidatorInterface $validator,
        \Magento\Payment\Gateway\Response\HandlerInterface $handler,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\Core\Gateway\ErrorMapper\ConfigurableMapper $errorMessageMapper,
        \Payments\Core\Model\LoggerInterface $logger
    ) {
        $this->validator = $validator;
        $this->handler = $handler;
        $this->subjectReader = $subjectReader;
        $this->errorMessageMapper = $errorMessageMapper;
        $this->logger = $logger;
    }

    /**
     * Handles Token creation Response
     *
     * @param array $commandSubject
     * @return void
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {
        $response = $this->subjectReader->readResponse($commandSubject);

        $this->logger->debug(
            [
                'client' => static::class,
                'token_response' => $response
            ]
        );

        $validationResult = $this->validator->validate($commandSubject);

        if (!$validationResult->isValid()) {

            $messages = [];
            foreach ($validationResult->getFailsDescription() as $error) {
                $messages[] = $error->getText();
                $this->logger->critical($error);
            }

            $exceptionMessage = !empty($messages)
                ? __(implode(PHP_EOL, $this->getErrorsDescription($messages)))
                : __('Error while handling Token response');

            throw new \Magento\Payment\Gateway\Command\CommandException($exceptionMessage);
        }

        $this->handler->handle($commandSubject, $response);
    }

    public function getErrorsDescription($errors)
    {
        $result = [];

        foreach ($errors as $error) {
            $result[] = $this->errorMessageMapper->getMessage($error);
        }

        return $result;
    }
}
