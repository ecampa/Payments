<?php

namespace Payments\Atp\Plugin;

use Magento\Framework\Registry;
use Payments\Atp\Model\Config;
use Magento\Customer\Api\Data\AddressInterface;
use Payments\Atp\Service\GatewaySoapApi;
use Magento\Framework\Exception\LocalizedException;
use Payments\Atp\Model\DmeValidationResultFactory;
use Payments\Atp\Model\DmeResultPropagationManager;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Payments\Atp\Model\Request\DmeAddressValidationDataBuilder;

class AddressRepositoryPlugin extends \Payments\Atp\Plugin\AbstractAtpPlugin
{
    /**
     * @var DmeAddressValidationDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @param Config $config
     * @param Registry $registry
     * @param DmeAddressValidationDataBuilder $requestDataBuilder
     * @param DmeResultPropagationManager $eventPropagationManager
     * @param DmeValidationResultFactory $dmeResultFactory
     */
    public function __construct(
        \Payments\Atp\Model\Config $config,
        Registry $registry,
        \Payments\Atp\Service\GatewaySoapApi $soapClient,
        \Payments\Atp\Model\Request\DmeAddressValidationDataBuilder $requestDataBuilder,
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
     * @param AddressRepository $subject
     * @param \Closure $proceed
     * @param AddressInterface $addressData
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundSave(
        AddressRepository $subject,
        \Closure $proceed,
        AddressInterface $addressData
    ) {
        if (! $this->canProcessAtpEvent()) {
            return $proceed($addressData);
        }

        $this->preventFurtherAtpProcessing();

        $type = \Payments\Atp\Service\GatewaySoapApi::ATP_EVENT_TYPE_UPDATE;

        $request = $this->requestDataBuilder->build($type, $addressData);

        $response = $this->gatewayApi->call($request);

        /** @var \Payments\Atp\Model\DmeValidationResult $dmeValidationResult */
        $dmeValidationResult = $this->dmeResultFactory->create()
            ->setEventData(['object' => $addressData])
            ->setDecision($response->decision)
            ->setType($type);

        $this->eventPropagationManager->propagate($dmeValidationResult);

        // challenge is processed separately, no need to save data right now
        if ($dmeValidationResult->isChallenge()) {
            throw new LocalizedException(__('Challenge was requested'));
        }

        return $proceed($addressData);
    }
}
