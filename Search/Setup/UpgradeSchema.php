<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Klevu\Search\Setup;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		

        $installer = $setup;

        $installer->startSetup();
		
		if(!$context->getVersion()) {
            //no previous version found, installation, InstallSchema was just executed
            //be careful, since everything below is true for installation !
        }
		
		if (version_compare($context->getVersion(), '2.0.2') < 0) {
            //code to upgrade to 2.0.2
			$order_sync_table = $installer->getTable('klevu_order_sync');
			$installer->run("ALTER TABLE `{$order_sync_table}` ADD `klevu_session_id` VARCHAR(255) NOT NULL , ADD `ip_address` VARCHAR(255) NOT NULL , ADD `date` DATETIME NOT NULL");
        }
		
		
		if (version_compare($context->getVersion(), '2.0.10') < 0) {
            //code to upgrade to 2.0.2
			$klevu_sync_table = $installer->getTable('klevu_product_sync');
			$installer->run("ALTER TABLE `{$klevu_sync_table}` ADD `error_flag` INT(11) NOT NULL DEFAULT '0' AFTER `type`");
        }
		
		$installer->endSetup();

	
	}

}
