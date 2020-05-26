<?php
namespace Payments\Core\Model\Source\Status;

/**
 * Order Statuses source model
 */
class Pending extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT
    ];
}
