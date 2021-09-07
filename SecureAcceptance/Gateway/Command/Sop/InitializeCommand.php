<?php
namespace Payments\SecureAcceptance\Gateway\Command\Sop;


class InitializeCommand implements \Magento\Payment\Gateway\CommandInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(\Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $commandSubject)
    {
        $stateObject = $this->subjectReader->readStateObject($commandSubject);

        $stateObject
            ->setData('state', \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
            ->setData('is_notified', false);

        $order = $this->subjectReader->readPayment($commandSubject)->getPayment()->getOrder();

        $order
            ->setCustomerNoteNotify(false)
            ->setCanSendNewEmailFlag(false);

    }
}
