<?php
namespace Payments\BankTransfer\Cron;

use Payments\Core\Model\LoggerInterface;

class Status
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesOrderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $gatewayApi;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $_transportBuilder;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;
    
    /**
     * @var \Payments\Core\Helper\Data
     */
    private $_helper;
    
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $_transaction;

    /**
     * Status constructor.
     * @param LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Payments\Core\Helper\Data $helper
     * @param \Magento\Framework\DB\Transaction $transaction
     */
    public function __construct(
        \Payments\Core\Model\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Payments\Core\Service\GatewaySoapApi $gatewayApi,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Payments\Core\Helper\Data $helper,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        $this->_logger = $logger;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->gatewayApi = $gatewayApi;
        $this->orderRepository = $orderRepository;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_helper = $helper;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
    }
    
    
    public function execute()
    {
        if ($this->isActive()) {
            try {
                $paymentCollection = $this->paymentCollectionFactory->create();
                $paymentCollection->addFieldToFilter('main_table.method', 'payments_bank_transfer');
                $paymentCollection->addFieldToFilter('order_table.status', ['in' => ['pending_payment', 'pending']]);
                $paymentCollection->getSelect()->joinleft(
                    ['order_table' => $paymentCollection->getTable('sales_order')],
                    'main_table.parent_id = order_table.entity_id',
                    ['status', 'quote_id']
                )->order('entity_id DESC');
                $paymentCollection->load();
                foreach ($paymentCollection as $payment) {
                    if (!empty($payment->getData('last_trans_id'))) {
                        $paymentMethod = $payment->getAdditionalInformation('bank_payment_method');
                        $result = $this->gatewayApi->checkBankTransferStatus(
                            $this->_scopeConfig->getValue("payment/payments_bank_transfer/".$paymentMethod."_merchant_id", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                            $payment->getOrder()->getIncrementId(),
                            $payment->getData('last_trans_id'),
                            $paymentMethod
                        );
                        $this->updateOrder($result, $payment->getOrder());
                    }
                }
            } catch (\Exception $e) {
                $this->_logger->error("BankTransfer: " . $e->getMessage());
            }
        }
        return $this;
    }
    
    private function updateOrder($result, $order)
    {
        if (!empty($result)
                && !empty($result->apCheckStatusReply)
                && $result->apCheckStatusReply->paymentStatus == 'settled') {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $this->orderRepository->save($order);
            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->setTransactionId($result->apCheckStatusReply->reconciliationID);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->_transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
        }
    }
    
    private function isActive()
    {
        return (bool)(int)$this->_scopeConfig->getValue(
            "payment/payments_bank_transfer/bancontact_active",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
            || (bool)(int)$this->_scopeConfig->getValue(
                "payment/payments_bank_transfer/sofort_active",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            || (bool)(int)$this->_scopeConfig->getValue(
                "payment/payments_bank_transfer/ideal_active",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}