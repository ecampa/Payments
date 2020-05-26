<?php
namespace Payments\Core\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $installer,
        ModuleContextInterface $context
    ) {
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.3.0') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('payments_payment_token'),
                'payment_method',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 10,
                    'nullable' => false,
                    'comment' => 'Payment Method'
                ]
            );
        }
        $installer->endSetup();
    }
}
