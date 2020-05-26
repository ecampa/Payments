<?php
namespace Payments\Core\Cron;

use Payments\Core\Model\Config;
use Payments\Core\Model\LoggerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;

class DecisionManagerReport
{
    const DM_ACCEPT = "ACCEPT";
    const PAYPAL_METHOD = 'payments_paypal';
    const VISA_CHECKOUT_METHOD = 'payments_visa';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $gatewayConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var array
     */
    private $exceptions = [];

    /**
     * @var array
     */
    private static $skipStatuses = [
        Order::STATE_PROCESSING,
        Order::STATE_CANCELED,
        Order::STATE_CLOSED,
        Order::STATE_COMPLETE,
    ];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    private $processedMerchantIds = [];

    private $gatewayApi;

    /**
     * @var \Payments\Core\DM\TransactionProcessorInterface[]
     */
    private $transactionProcessors;

    /**
     * @var \Magento\Payment\Gateway\CommandInterface
     */
    private $conversionReportCommand;

    /**
     * @var \Payments\Core\Model\DecisionManager\MailSender
     */
    private $mailSender;

    /**
     * DecisionManagerReport constructor.
     *
     * @param LoggerInterface $logger
     * @param Config $config
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param OrderRepository $orderRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Payment\Gateway\CommandInterface $conversionReportCommand
     * @param \Payments\Core\Model\DecisionManager\MailSender $mailSender
     * @param array $transactionProcessors
     */
    public function __construct(
        \Payments\Core\Model\LoggerInterface $logger,
        \Payments\Core\Model\Config $config,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Payments\Core\Service\GatewaySoapApi $gatewayApi,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Gateway\CommandInterface $conversionReportCommand,
        \Payments\Core\Model\DecisionManager\MailSender $mailSender,
        array $transactionProcessors = []
    ) {
        $this->logger = $logger;
        $this->gatewayConfig = $config;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->gatewayApi = $gatewayApi;
        $this->storeManager = $storeManager;
        $this->transactionProcessors = $transactionProcessors;
        $this->conversionReportCommand = $conversionReportCommand;
        $this->mailSender = $mailSender;

    }

    public function execute()
    {

        foreach ($this->storeManager->getStores() as $store) {

            if (!$this->gatewayConfig->isDecisionManagerCronEnabled($store->getId())) {
                continue;
            }

            $merchantId = $this->gatewayConfig->getMerchantId($store->getId());

            try {

                if ($this->isMidProcessed($merchantId)) {
                    continue;
                }

                $this->runReport($store->getId());

            } catch (\Exception $e) {
                $this->logger->error(
                    'An error occurred while running DM cron for Store Code '
                    . $store->getCode()
                    . ' Exception message: ' . $e->getMessage()
                );
            } finally {
                $this->setMidProcessed($merchantId);
            }
        }
    }

    private function isMidProcessed($merchantId)
    {
        return in_array($merchantId, $this->processedMerchantIds);
    }

    private function setMidProcessed($merchantId)
    {
        array_push($this->processedMerchantIds, $merchantId);
        return $this;
    }

    public function runReport($storeId)
    {
        $parsedXml = $this->conversionReportCommand->execute(['store_id' => $storeId])->get();

        foreach ($parsedXml as $incrementId => $value) {
            $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
            $payment = $order->getPayment();

            if ($order && $payment && $payment->getCcTransId() == $value['transaction_id'] && !in_array($order->getState(), self::$skipStatuses)) {

                try {
                    $this->updatePayment($order, $value);
                } catch (\Exception $e) {
                    $this->buildExceptionOutput($order, $e->getMessage());
                    continue;
                }
            }
        }

        if (!empty($this->exceptions)) {
            $this->logInfo(json_encode($this->exceptions));
        }
    }

    private function updatePayment(\Magento\Sales\Model\Order $order, array $parsedXml)
    {
        if ($parsedXml['new_decision'] != self::DM_ACCEPT) {
            $this->processCancel($order);
            return;
        }

        $payment = $order->getPayment();
        if ($payment instanceof \Magento\Sales\Model\Order\Payment) {
            $payment->setIsTransactionApproved(true);
            $transactionId = $payment->getLastTransId();
            $payment->setTransactionId($transactionId);

            if ($this->gatewayConfig->getValue(
                \Payments\Core\Model\AbstractGatewayConfig::KEY_ENABLED_DM_CRON_ACCEPTED_SETTLEMENT,
                $order->getStoreId()
            )
            ) {
                $this->settleDmTransaction($payment);
            }

            $payment->update(false);
        }

        $this->orderRepository->save($order);
        return;

    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return $this
     * @throws \Exception
     */
    private function settleDmTransaction($payment)
    {

        if (isset($this->transactionProcessors[$payment->getMethod()])) {
            $processor = $this->transactionProcessors[$payment->getMethod()];
            $processor->settle($payment);
            return $this;
        }

        $this->gatewayApi->setPayment($payment);
        $result = (array)$this->gatewayApi->captureOrder(
            $payment->getBaseAmountAuthorized()
        );

        if (!$result
            || !isset($result['decision'])
            || $result['decision'] != self::DM_ACCEPT
            || !isset($result['requestID'])
        ) {
            return $this;
        }

        $payment->setTransactionId($result['requestID']);
        return $this;
    }

    private function buildExceptionOutput($order, $message)
    {
        $this->exceptions[] = "Order increment_id: ".$order->getIncrementId() . " | Error Message: " .$message;
    }

    private function logInfo($message)
    {
        $this->logger->info(__CLASS__ . "|" . __METHOD__. " | " . $message);
    }

    private function processCancel(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getId()) {
            return;
        }

        $payment = $order->getPayment();

        if (!$payment instanceof \Magento\Sales\Model\Order\Payment) {
            return;
        }

        $order->getPayment()
            ->setNotificationResult(true)
            ->setIsTransactionClosed(true)
            ->deny(false);

        try {
            $storeId = $order->getStoreId();
            $paymentMethod = $payment->getMethod();

            if (isset($this->transactionProcessors[$payment->getMethod()])) {
                $processor = $this->transactionProcessors[$payment->getMethod()];
                $processor->cancel($payment);
            } elseif ($paymentMethod == self::VISA_CHECKOUT_METHOD) {
                $this->gatewayApi->reverseOrderPayment($storeId, true);
            } else {
                $this->gatewayApi->setPayment($payment);
                $this->gatewayApi->reverseOrderPayment($storeId);
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->orderRepository->save($order);
        $this->mailSender->sendFailureEmail($order, $order->getStoreId());
    }


}
