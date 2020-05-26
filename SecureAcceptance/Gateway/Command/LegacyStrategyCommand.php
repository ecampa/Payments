<?php

namespace Payments\SecureAcceptance\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Payments\SecureAcceptance\Gateway\Helper\SubjectReader;
use Payments\SecureAcceptance\Gateway\Config\Config;

class LegacyStrategyCommand implements CommandInterface
{
    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var String
     */
    private $command;

    /**
     * @var String
     */
    private $legacyCommand;

    /**
     * CaptureStrategyCommand constructor.
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     * @param Config $config
     * @param string|null $command
     * @param string|null $legacyCommand
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        string $command = null,
        string $legacyCommand = null
    ) {
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->command = $command;
        $this->legacyCommand = $legacyCommand;
    }

    public function execute(array $commandSubject)
    {
        if (!$this->config->getIsLegacyMode()) {
            $this->commandPool->get($this->command)->execute($commandSubject);
            return;
        }

        $this->commandPool->get($this->legacyCommand)->execute($commandSubject);
    }
}
