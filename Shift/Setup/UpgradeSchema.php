<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\Shift\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.1.9', '<')) {
            $this->createShiftTable($setup);
            $this->createShiftInOutTable($setup);
            $this->createRetailTransaction($setup);
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->updateRetailTransaction($setup);
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->updateShiftTable($setup);
        }
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->fixReportVersion($setup);
        }
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    protected function createShiftTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_shift_shift'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_shift_shift')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'outlet_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Outlet Id'
        )->addColumn(
            'register_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Register Id'
        )->addColumn(
            'user_open_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'User Open Id'
        )->addColumn(
            'user_close_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'user close id'
        )->addColumn(
            'user_open_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'User name Open'
        )->addColumn(
            'user_close_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'User name close'
        )->addColumn(
            'open_note',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'User name close'
        )->addColumn(
            'close_note',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'User name close'
        )->addColumn(
            'data',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'Note'
        )->addColumn(
            'point_earned',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Point Earn'
        )->addColumn(
            'point_spent',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Point Earn'
        )->addColumn(
            'total_adjustment',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Expected amount'
        )->addColumn(
            'total_expected_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Total Expected'
        )->addColumn(
            'total_counted_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Total Counted'
        )->addColumn(
            'total_net_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Total Counted'
        )->addColumn(
            'take_out_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Take out'
        )->addColumn(
            'start_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Take out'
        )->addColumn(
            'open_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'close_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE,],
            'Modification Time'
        )->addColumn(
            'is_open',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Active'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    protected function createShiftInOutTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_shift_shiftinout'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_shift_shiftinout')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'shift_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Shift Id'
        )->addColumn(
            'user_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'User Id'
        )->addColumn(
            'user_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'User name'
        )->addColumn(
            'note',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Note'
        )->addColumn(
            'amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Total Expected'
        )->addColumn(
            'is_in',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is In'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true, 'default' => Table::TIMESTAMP_UPDATE,],
            'Modification Time'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    protected function createRetailTransaction(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_retail_transaction'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_retail_transaction')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'payment_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Payment Id'
        )->addColumn(
            'shift_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Outlet Id'
        )->addColumn(
            'outlet_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Outlet Id'
        )->addColumn(
            'register_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Register Id'
        )->addColumn(
            'payment_title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Payment title'
        )->addColumn(
            'payment_type',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Payment type'
        )->addColumn(
            'amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Take out'
        )->addColumn(
            'is_purchase',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Active'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE,],
            'Modification Time'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     */
    protected function updateRetailTransaction(SchemaSetupInterface $setup)
    {
        $installer = $setup;

        $installer->getConnection()->dropColumn($installer->getTable('sm_retail_transaction'), 'order_id');
        $installer->getConnection()->addColumn(
            $installer->getTable('sm_retail_transaction'),
            'order_id',
            [
                'type'    => Table::TYPE_INTEGER,
                'comment' => 'Order Id',
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     */
    protected function updateShiftTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;

        $installer->getConnection()->dropColumn($installer->getTable('sm_shift_shift'), 'total_order_tax');
        $installer->getConnection()->dropColumn($installer->getTable('sm_shift_shift'), 'detail_tax');
        $installer->getConnection()->dropColumn($installer->getTable('sm_shift_shift'), 'base_total_order_tax');
        $installer->getConnection()->addColumn(
            $installer->getTable('sm_shift_shift'),
            'total_order_tax',
            [
                'type'      => Table::TYPE_DECIMAL,
                'nullable'  => false,
                'SCALE'     => 4,
                'PRECISION' => 12,
                'comment'   => 'Total order tax',
                'default'   => '0.0000'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sm_shift_shift'),
            'detail_tax',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'comment'  => 'Detail all tax in shift',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sm_shift_shift'),
            'base_total_order_tax',
            [
                'type'      => Table::TYPE_DECIMAL,
                'nullable'  => false,
                'SCALE'     => 4,
                'PRECISION' => 12,
                'comment'   => 'Total order tax',
                'default'   => '0.0000'
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    protected function fixReportVersion(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        if ($installer->getConnection()->tableColumnExists($installer->getTable('sm_retail_transaction'), 'order_id') === false) {
            $this->updateRetailTransaction($setup);
        }
        if ($installer->getConnection()->tableColumnExists($installer->getTable('sm_shift_shift'), 'total_order_tax') === false
            && $installer->getConnection()->tableColumnExists($installer->getTable('sm_shift_shift'), 'detail_tax') === false
            && $installer->getConnection()->tableColumnExists($installer->getTable('sm_shift_shift'), 'base_total_order_tax') === false) {
            $this->updateShiftTable($setup);
        }
    }
}
