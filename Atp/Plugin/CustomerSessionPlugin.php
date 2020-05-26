<?php

namespace Payments\Atp\Plugin;

use Magento\Framework\Registry;
use Payments\Atp\Model\Config;
use Magento\Customer\Model\Session;
use Payments\Atp\Service\GatewaySoapApi;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Payments\Atp\Model\DmeValidationResultFactory;
use Payments\Atp\Model\DmeResultPropagationManager;
use Payments\Atp\Model\Request\DmeValidationDataBuilder;

class CustomerSessionPlugin extends \Payments\Atp\Plugin\AbstractAtpPlugin
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
     * @param Session $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customerData
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundSetCustomerDataAsLoggedIn(
        Session $subject,
        \Closure $proceed,
        CustomerInterface $customerData
    ) {

        if (! $this->canProcessAtpEvent()) {
            return $proceed($customerData);
        }

        $this->preventFurtherAtpProcessing();

        $type = \Payments\Atp\Service\GatewaySoapApi::ATP_EVENT_TYPE_LOGIN;

        $request = $this->requestDataBuilder->build($type, $customerData);

        $response = $this->gatewayApi->call($request);

        /** @var \Payments\Atp\Model\DmeValidationResult $dmeValidationResult */
        $dmeValidationResult = $this->dmeResultFactory->create()
            ->setEventData(['object' => $customerData])
            ->setDecision($response->decision)
            ->setType($type);

        $this->eventPropagationManager->propagate($dmeValidationResult);

        // challenge is processed separately, no need to login customer right now
        if ($dmeValidationResult->isChallenge()) {
            throw new LocalizedException(__('Challenge was requested'));
        }

        return $proceed($customerData);
    }
}
