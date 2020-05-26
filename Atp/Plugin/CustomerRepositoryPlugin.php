<?php

namespace Payments\Atp\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Payments\Atp\Model\Config;
use Payments\Atp\Service\GatewaySoapApi;
use Magento\Customer\Api\Data\CustomerInterface;
use Payments\Atp\Model\DmeValidationResultFactory;
use Payments\Atp\Model\DmeResultPropagationManager;
use Payments\Atp\Model\Request\DmeValidationDataBuilder;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

class CustomerRepositoryPlugin extends \Payments\Atp\Plugin\AbstractAtpPlugin
{
    /**
     * @var DmeValidationDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @param Config $config
     * @param Registry $registry
     * @param DmeValidationDataBuilder $requestDataBuilder
     * @param DmeResultPropagationManager $eventPropagationManager
     * @param DmeValidationResultFactory $dmeResultFactory
     */
    public function __construct(
        \Payments\Atp\Model\Config $config,
        Registry $registry,
        \Payments\Atp\Service\GatewaySoapApi $soapClient,
        \Payments\Atp\Model\Request\DmeValidationDataBuilder $requestDataBuilder,
        \Payments\Atp\Model\DmeResultPropagationManager $eventPropagationManager,
        \Payments\Atp\Model\DmeValidationResultFactory $dmeResultFactory
    ) {
        parent::__construct(
            $config,
            $registry,
            $soapClient,
            $eventPropagationManager,
            $dmeResultFactory
        );
        $this->requestDataBuilder = $requestDataBuilder;
    }

    /**
     * @param CustomerRepository $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customerData
     * @param null $passwordHash
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundSave(
        CustomerRepository $subject,
        \Closure $proceed,
        CustomerInterface $customerData,
        $passwordHash = null
    ) {
        if (! $this->canProcessAtpEvent()) {
            return $proceed($customerData, $passwordHash);
        }

        $this->preventFurtherAtpProcessing();

        $type = $customerData->getId()
            ? \Payments\Atp\Service\GatewaySoapApi::ATP_EVENT_TYPE_UPDATE
            : \Payments\Atp\Service\GatewaySoapApi::ATP_EVENT_TYPE_CREATION;

        $request = $this->requestDataBuilder->build($type, $customerData);

        $response = $this->gatewayApi->call($request);

        /** @var \Payments\Atp\Model\DmeValidationResult $dmeValidationResult */
        $dmeValidationResult = $this->dmeResultFactory->create()
            ->setEventData(['object' => $customerData, 'password_hash' => $passwordHash])
            ->setDecision($response->decision)
            ->setType($type);

        $this->eventPropagationManager->propagate($dmeValidationResult);

        // challenge is processed separately, no need to save data right now
        if ($dmeValidationResult->isChallenge()) {
            throw new LocalizedException(__('Challenge was requested'));
        }

        return $proceed($customerData, $passwordHash);
    }
}
