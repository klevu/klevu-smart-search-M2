<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Klevu\Search\Setup;

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
		
		
		$notifications_table = $installer->getTable('klevu_notification');

		$installer->run("DROP TABLE IF EXISTS `{$notifications_table}`");

		$installer->run("
		CREATE TABLE `{$notifications_table}` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `type` varchar(32) NOT NULL,
		  `message` text NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		// Add a notification to setup cron and a cron job to clear
		// the notification the next time cron runs
		$installer->getConnection()->insert($notifications_table, array(
			"type" => "cron_check",
			"message" => __('Klevu Search relies on cron for normal operations. Please check that you have Magento cron set up correctly. You can find instructions on how to set up Magento Cron <a target="_blank" href="http://support.klevu.com/knowledgebase/setup-a-cron/">here</a>.')
		));

		$now = date_create("now")->format("Y-m-d H:i:00");

		$product_sync_table = $installer->getTable('klevu_product_sync');

		// Pre-existing sync data is of no use, so drop the existing
		// table before recreating it
		$installer->run("DROP TABLE IF EXISTS `{$product_sync_table}`");

		$installer->run("
			CREATE TABLE IF NOT EXISTS `{$product_sync_table}` (
			  `product_id` int(10) unsigned NOT NULL,
			  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `store_id` smallint(5) unsigned NOT NULL,
			  `test_mode` int(1) NOT NULL DEFAULT '0',
			  `last_synced_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `type` varchar(255) NOT NULL DEFAULT 'products'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	    $installer->run("
			ALTER TABLE `{$product_sync_table}` ADD PRIMARY KEY (`product_id`,`parent_id`,`store_id`,`test_mode`,`type`);
		");

		$order_sync_table = $installer->getTable('klevu_order_sync');

		$installer->run("DROP TABLE IF EXISTS `{$order_sync_table}`");

		$installer->run("
		CREATE TABLE `{$order_sync_table}` (
		  `order_item_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`order_item_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$installer->endSetup();

	
	}

}
