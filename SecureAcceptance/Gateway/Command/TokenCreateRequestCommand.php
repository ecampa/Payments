<?php
namespace Payments\SecureAcceptance\Gateway\Command;

class TokenCreateRequestCommand implements \Magento\Payment\Gateway\CommandInterface
{

    const COMMAND_CODE = 'create_token';

    /**
     * @var \Magento\Payment\Gateway\Command\Result\ArrayResultFactory
     */
    private $arrayResultFactory;

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var \Payments\Core\Model\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Payment\Gateway\Command\Result\ArrayResultFactory $arrayResultFactory,
        \Magento\Payment\Gateway\Request\BuilderInterface $requestBuilder,
        \Payments\Core\Model\LoggerInterface $logger
    ) {
        $this->arrayResultFactory = $arrayResultFactory;
        $this->requestBuilder = $requestBuilder;
        $this->logger = $logger;
    }

    /**
     * Builds create Token Request
     *
     * @param array $commandSubject
     * @return \Magento\Payment\Gateway\Command\ResultInterface
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {

        $request = $this->requestBuilder->build($commandSubject);

        $this->logger->debug(
            [
                'client' => static::class,
                'token_request' => $request
            ]
        );

        return $this->arrayResultFactory->create(['array' => $request]);
    }
}
