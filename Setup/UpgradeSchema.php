<?php

namespace Midtrans\Snap\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const MIDTRANS_TRX_ID = 'midtrans_trx_id';

    /**
     * Upgrades DB schema for a module, add column midtrans_trx_id
     * on sales_order
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.2.0') < 0) {
            $tableName = $setup->getTable('sales_order');
        }
        if (isset($tableName)) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $columns = [
                    self::MIDTRANS_TRX_ID => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 40,
                        'nullable' => true,
                        'comment' => 'Midtrans transaction ID',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }
        $setup->endSetup();
    }
}
