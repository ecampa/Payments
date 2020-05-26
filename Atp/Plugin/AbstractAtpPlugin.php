<?php

namespace Payments\Atp\Plugin;

use Magento\Framework\Registry;
use Payments\Atp\Model\Config;
use Payments\Atp\Service\GatewaySoapApi;
use Payments\Atp\Model\DmeValidationResultFactory;
use Payments\Atp\Model\DmeResultPropagationManager;

abstract class AbstractAtpPlugin
{
    const KEY_REGISTRY_ATP_IN_PROGRESS = 'atp_action_in_progress';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Registry
     */
    protected $registry;

    protected $gatewayApi;

    /**
     * @var DmeResultPropagationManager
     */
    protected $eventPropagationManager;

    /**
     * @var DmeValidationResultFactory
     */
    protected $dmeResultFactory;

    /**
     * @param Config $config
     * @param Registry $registry
     * @param DmeResultPropagationManager $eventPropagationManager
     * @param DmeValidationResultFactory $dmeResultFactory
     */
    public function __construct(
        \Payments\Atp\Model\Config $config,
        Registry $registry,
        \Payments\Atp\Service\GatewaySoapApi $soapClient,
        \Payments\Atp\Model\DmeResultPropagationManager $eventPropagationManager,
        \Payments\Atp\Model\DmeValidationResultFactory $dmeResultFactory
    ) {
        $this->config = $config;
        $this->registry = $registry;
        $this->gatewayApi = $soapClient;
        $this->eventPropagationManager = $eventPropagationManager;
        $this->dmeResultFactory = $dmeResultFactory;
    }

    /**
     * @return bool
     */
    protected function canProcessAtpEvent()
    {
        if (! $this->config->isActive()) {
            return false;
        }

        if ($this->registry->registry(self::KEY_REGISTRY_ATP_IN_PROGRESS)) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    protected function preventFurtherAtpProcessing()
    {
        $this->registry->register(self::KEY_REGISTRY_ATP_IN_PROGRESS, true, true);
    }
}
