<?php

namespace Payments\SecureAcceptance\Gateway\Command\Flex;

use Exception;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

class GenerateKeyCommand implements CommandInterface
{
    /**
     * @var TransferFactoryInterface
     */
    private $transferFactory;

    /**
     * @var ArrayResultFactory
     */
    private $arrayResultFactory;

    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param TransferFactoryInterface $transferFactory
     * @param ArrayResultFactory $arrayResultFactory
     * @param BuilderInterface $requestBuilder
     * @param ValidatorInterface $validator
     * @param ClientInterface $client
     */
    public function __construct(
        TransferFactoryInterface $transferFactory,
        ArrayResultFactory $arrayResultFactory,
        BuilderInterface $requestBuilder,
        ValidatorInterface $validator,
        ClientInterface $client
    ) {
        $this->transferFactory = $transferFactory;
        $this->arrayResultFactory = $arrayResultFactory;
        $this->requestBuilder = $requestBuilder;
        $this->validator = $validator;
        $this->client = $client;
    }

    /**
     * @param array $commandSubject
     * @return ResultInterface
     * @throws ClientException
     * @throws ConverterException
     * @throws Exception
     */
    public function execute(array $commandSubject)
    {
        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->client->placeRequest($transferO);

        $validationResult = $this->validator->validate(
            array_merge($commandSubject, ['response' => $response])
        );

        if (! $validationResult->isValid()) {
            throw new CommandException(__('Failed to generate flex key.'));
        }

        return $this->arrayResultFactory->create(['array' => $response]);
    }
}