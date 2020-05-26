<?php

namespace Payments\Atp\Model;

use Magento\Framework\Event\ManagerInterface;

class DmeResultPropagationManager
{
    const EVENT_PREFIX = 'payments_atp_';

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param ManagerInterface $manager
     */
    public function __construct(ManagerInterface $manager)
    {
        $this->eventManager = $manager;
    }

    /**
     * @param \Payments\Atp\Model\DmeValidationResult $result
     */
    public function propagate(\Payments\Atp\Model\DmeValidationResult $result)
    {
        $type = $result->getType();
        $decision = $result->getDecision();
        $eventData = $result->getEventData();

        $eventData = array_merge($eventData, ['type' => $type, 'decision' => $decision]);

        $this->eventManager->dispatch(self::EVENT_PREFIX . $type, $eventData);

        $this->eventManager->dispatch(self::EVENT_PREFIX . strtolower($decision), $eventData);

        $this->eventManager->dispatch(self::EVENT_PREFIX . $type . '_' . strtolower($decision), $eventData);
    }
}
