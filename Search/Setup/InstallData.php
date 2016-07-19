<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Klevu\Search\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{


    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

            $entity_type = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Eav\Model\Entity\Type')->loadByCode("catalog_product"); 	 
			$entity_typeid = $entity_type->getId();
			$attributecollection = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Eav\Model\Entity\Attribute')->getCollection()->addFieldToFilter("entity_type_id", $entity_typeid)->addFieldToFilter("attribute_code", "rating");
			if (!count($attributecollection)) {
				
				$attribute = $attributecollection->getFirstItem();
				$data = array();
				$data['id'] = null;
				$data['entity_type_id'] = $entity_typeid;
				$data['attribute_code'] = "rating";
				$data['backend_type'] = "varchar";
				$data['frontend_input'] = "text";
				$data['frontend_label'] = 'Rating';
				$data['default_value_text'] = '0';
				$data['is_global'] = '0';
				$data['is_user_defined'] = '1';
				$data['group'] = 'Product Details';
				$attribute->setData($data);
				$attribute->save();
				$resource = $setup;
				$read = $setup->getConnection('core_read');
				$write = $setup->getConnection('core_write');
				$select = $read->select()->from($resource->getTable("eav_attribute_set") , array(
					'attribute_set_id'
				))->where("entity_type_id=?", $entity_typeid);
				$attribute_sets = $read->fetchAll($select);
				foreach($attribute_sets as $attribute_set) {
					$attribute_set_id = $attribute_set['attribute_set_id'];
					$select = $read->select()->from($resource->getTable("eav_attribute") , array(
						'attribute_id'
					))->where("entity_type_id=?", $entity_typeid)->where("attribute_code=?", "rating");
					$attribute = $read->fetchRow($select);
					$attribute_id = $attribute['attribute_id'];
					$select = $read->select()->from($resource->getTable("eav_attribute_group") , array(
						'attribute_group_id'
					))->where("attribute_set_id=?", $attribute_set_id)->where("attribute_group_code=?", 'product-details');
					$attribute_group = $read->fetchRow($select);
					$attribute_group_id = $attribute_group['attribute_group_id'];
					$write->beginTransaction();
					$write->insert($resource->getTable("eav_entity_attribute") , array(
						"entity_type_id" => $entity_typeid,
						"attribute_set_id" => $attribute_set_id,
						"attribute_group_id" => $attribute_group_id,
						"attribute_id" => $attribute_id,
						"sort_order" => 5
					));
					$write->commit();
				}
			}
			\Magento\Framework\App\ObjectManager::getInstance()->get('\Klevu\Search\Helper\Config')->saveRatingUpgradeFlag(0);
	
    }
}
