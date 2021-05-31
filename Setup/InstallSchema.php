<?php

namespace Droppa\DroppaShipping\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Setup\Exception;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        try {

            $installCustomTable = $setup->getConnection()->newTable(

                $setup->getTable('droppa_booking_object')

            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Auto incremented Primary Key'
            )->addColumn(
                'booking_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Booking Object ID'
            )->setComment('Saves only the OID of every booking made');

            $setup->getConnection()->createTable($installCustomTable);
            $setup->endSetup();
        } catch (Exception  $error) {
            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($error->getMessage());
        }
    }
}