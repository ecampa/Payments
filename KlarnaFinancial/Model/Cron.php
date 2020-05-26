<?php
namespace Payments\KlarnaFinancial\Model;


class Cron
{

    const COMMAND_CODE_CHECK_STATUS = 'check_status';

    /**
     * @var \Payments\KlarnaFinancial\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory
     */
    private $paymentCollectionFactory;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerPoolInterface
     */
    private $commandManagerPool;

    /**
     * @var \Payments\Core\Model\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Payments\KlarnaFinancial\Gateway\Config\Config $config,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Payment\Gateway\Command\CommandManagerPoolInterface $commandManagerPool,
        \Payments\Core\Model\LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->commandManagerPool = $commandManagerPool;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->config->isActive()) {
            return;
        }

        foreach ($this->getPendingPayments() as $payment) {
            try {
                $this->commandManagerPool
                    ->get(\Payments\KlarnaFinancial\Model\Ui\ConfigProvider::CODE)
                    ->executeByCode(static::COMMAND_CODE_CHECK_STATUS, $payment);
            } catch (\Exception $e) {
                $this->logger->error('An occurred error while running Klarna cron: ' . $e->getMessage());
            }
        }
    }

    private function getPendingPayments()
    {

        $paymentCollection = $this->paymentCollectionFactory->create();
        $paymentCollection
            ->addFieldToFilter(
                'main_table.method',
                \Payments\KlarnaFinancial\Model\Ui\ConfigProvider::CODE
            )
            ->addFieldToFilter(
                'order_table.state',
                [
                    'in' => ['payment_review']
                ]
            );

        $paymentCollection->getSelect()
            ->joinleft(
                ['order_table' => $paymentCollection->getTable('sales_order')],
                'main_table.parent_id = order_table.entity_id',
                ['status', 'quote_id']
            )->order('entity_id DESC');

        return $paymentCollection;
    }

}
