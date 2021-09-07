<?php
namespace Payments\WeChatPay\Gateway\Response;

use Payments\WeChatPay\Gateway\Config\Config;

class StatusResponseHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->subjectReader = $subjectReader;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $apStatus = $response['apCheckStatusReply']->paymentStatus ?? '';

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $order = $this->orderRepository->get($paymentDO->getOrder()->getId());
        $payment = $order->getPayment();

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment->setTransactionId($payment->getLastTransId());

        $normalizedStatus = strtolower($apStatus);

        $payment->setAdditionalInformation('wcpStatus', $normalizedStatus);

        switch ($normalizedStatus) {
            case \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_SETTLED:
                $payment->setIsTransactionApproved(true)->update(false);
                $this->orderRepository->save($order);
                break;

            case \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_FAILED:
            case \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_ABANDONED:
                $payment->setIsTransactionDenied(true)->update(false);
                $this->orderRepository->save($order);

                break;

            case \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_REFUNDED:
            case \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_PENDING:
            default:
                return;
        }
    }
}
