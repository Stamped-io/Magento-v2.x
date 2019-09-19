<?php


namespace Stamped\Core\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
		$installer->startSetup();

		/**
		 * Creating table stamped_core
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('stamped_rich_snippets')
		)->addColumn(
			'rich_snippet_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'product_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			[ 'unsigned' => true, 'nullable' => false],
			'Product Id'
		)->addColumn(
			'store_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			[ 'unsigned' => true, 'nullable' => false],
			'Store Id'
		)->addColumn(
			'average_score',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			[ 'unsigned' => true, 'nullable' => false],
			'Avg Score'
		)->addColumn(
			'reviews_count',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			[ 'unsigned' => true, 'nullable' => false],
			'Reviews Count'
		)->addColumn(
			'expiration_time',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => false],
			'Expiration Time'
		)->setComment(
			'Core item'
		);
		$installer->getConnection()->createTable($table);
		$installer->endSetup();
	}
}