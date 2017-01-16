<?php

/**
 * Class \Klevu\Search\Model\Product\Sync
 * @method \Magento\Framework\Db\Adapter\Interface getConnection()
 * @method \Magento\Store\Model\Store getStore()
 * @method string getSessionId()
 */
namespace Klevu\Search\Model\Product;

use \Magento\Framework\Db\Adapter\AdapterInterface;
use \Magento\Catalog\Model\ResourceModel\Category\Collection;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\Event\ManagerInterface; 
use \Klevu\Search\Helper\Config;
use \Klevu\Search\Model\Session;
use \Klevu\Search\Helper\Data;
use \Magento\Cron\Model\Schedule;
use \Psr\Log\LoggerInterface;
use \Klevu\Search\Model\Api\Action\Startsession;
use \Klevu\Search\Model\Api\Action\Deleterecords;
use \Klevu\Search\Model\Api\Action\Updaterecords;
use \Klevu\Search\Helper\Compat;
use \Klevu\Search\Model\Api\Action\Addrecords;
use \Magento\Catalog\Model\Product;
use \Magento\Framework\Filesystem;
use \Magento\Customer\Model\Group;
use \Magento\Tax\Model\Calculation;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Eav\Model\Entity\Type;
use \Magento\Eav\Model\Entity\Attribute;
use \Magento\Catalog\Model\Product\Action;
use \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use \Magento\CatalogRule\Model\Rule;
class Sync extends \Klevu\Search\Model\Sync {
    /**
     * @var \Magento\Framework\Model\Resource
     */
    protected $_frameworkModelResource;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_frameworkEventManagerInterface;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Magento\Cron\Model\Schedule
     */
    protected $_cronModelSchedule;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_psrLogLoggerInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavModelConfig;

    /**
     * @var \Klevu\Search\Model\Api\Action\Startsession
     */
    protected $_apiActionStartsession;

    /**
     * @var \Klevu\Search\Model\Api\Action\Deleterecords
     */
    protected $_apiActionDeleterecords;

    /**
     * @var \Klevu\Search\Model\Api\Action\Updaterecords
     */
    protected $_apiActionUpdaterecords;

    /**
     * @var \Klevu\Search\Helper\Compat
     */
    protected $_searchHelperCompat;

    /**
     * @var \Klevu\Search\Model\Api\Action\Addrecords
     */
    protected $_apiActionAddrecords;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogModelProduct;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_productMediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_magentoFrameworkFilesystem;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogHelperProduct;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\Collection
     */
    protected $_resourceCategoryCollection;

    /**
     * @var \Magento\Customer\Model\Group
     */
    protected $_customerModelGroup;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    protected $_productAttributeCollection;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_taxModelCalculation;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelperData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * @var \Magento\CatalogInventory\Helper\Data
     */
    protected $_catalogInventoryHelperData;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxModelConfig;


    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_frameworkModelDate;

    /**
     * @var \Magento\Framework\App\Config\Value
     */
    protected $_modelConfigData;

    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_modelEntityType;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_modelEntityAttribute;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $_modelProductAction;
    
    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $_imageFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;
    
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_catalogModelCategory;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_frameworkAppRequestInterface;
    
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_frameworkModelStore;

    protected $_klevu_features_response;
    
    protected $_klevu_enabled_feature_response;
    
	protected $_entity_value;
    /**
     * @var \Klevu\Search\Model\Api\Action\Features
     */
    protected $_apiActionFeatures;

    public function __construct(\Magento\Framework\App\ResourceConnection $frameworkModelResource, 
        \Magento\Framework\Event\ManagerInterface $frameworkEventManagerInterface, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Magento\Backend\Model\Session $searchModelSession,        
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Magento\Cron\Model\Schedule $cronModelSchedule, 
        \Psr\Log\LoggerInterface $psrLogLoggerInterface, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Magento\Eav\Model\Config $eavModelConfig, 
        \Klevu\Search\Model\Api\Action\Startsession $apiActionStartsession, 
        \Klevu\Search\Model\Api\Action\Deleterecords $apiActionDeleterecords, 
        \Klevu\Search\Model\Api\Action\Updaterecords $apiActionUpdaterecords, 
        \Klevu\Search\Helper\Compat $searchHelperCompat, 
        \Klevu\Search\Model\Api\Action\Addrecords $apiActionAddrecords, 
        \Magento\Catalog\Model\Product $catalogModelProduct, 
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig, 
        \Magento\Framework\Filesystem $magentoFrameworkFilesystem, 
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $catalogHelperProduct, 
        \Magento\Catalog\Model\ResourceModel\Category\Collection $resourceCategoryCollection, 
        \Magento\Customer\Model\Group $customerModelGroup, 
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributeCollection, 
        \Magento\Tax\Model\Calculation $taxModelCalculation, 
        \Magento\Catalog\Helper\Data $taxHelperData, 
        \Magento\Framework\App\Config\ScopeConfigInterface $appConfigScopeConfigInterface, 
        \Magento\CatalogInventory\Helper\Data $catalogInventoryHelperData, 
        \Magento\Tax\Model\Config $taxModelConfig, 
        \Magento\Framework\Stdlib\DateTime\DateTime $frameworkModelDate, 
        \Magento\Framework\App\Config\Value $modelConfigData, 
        \Magento\Eav\Model\Entity\Type $modelEntityType, 
        \Magento\Eav\Model\Entity\Attribute $modelEntityAttribute, 
        \Magento\Catalog\Model\Product\Action $modelProductAction,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\CatalogRule\Observer\RulePricesStorage $rulePricesStorage,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Category $catalogModelCategory,
        \Magento\Framework\App\RequestInterface $frameworkAppRequestInterface,
        \Magento\Store\Model\Store $frameworkModelStore,
        \Klevu\Search\Model\Api\Action\Features $apiActionFeatures,
		\Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
        )
    {
        $this->_apiActionFeatures = $apiActionFeatures;
        $this->_frameworkModelStore = $frameworkModelStore;
        $this->_frameworkAppRequestInterface = $frameworkAppRequestInterface;
        $this->imageFactory = $imageFactory;
        $this->_frameworkModelResource = $frameworkModelResource;
        $this->_frameworkEventManagerInterface = $frameworkEventManagerInterface;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchModelSession = $searchModelSession;
        $this->_searchHelperData = $searchHelperData;
        $this->_cronModelSchedule = $cronModelSchedule;
        $this->_psrLogLoggerInterface = $psrLogLoggerInterface;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_eavModelConfig = $eavModelConfig;
        $this->_apiActionStartsession = $apiActionStartsession;
        $this->_apiActionDeleterecords = $apiActionDeleterecords;
        $this->_apiActionUpdaterecords = $apiActionUpdaterecords;
        $this->_searchHelperCompat = $searchHelperCompat;
        $this->_apiActionAddrecords = $apiActionAddrecords;
        $this->_catalogModelProduct = $catalogModelProduct;
        $this->_productMediaConfig = $productMediaConfig;
        $this->_magentoFrameworkFilesystem = $magentoFrameworkFilesystem;
        $this->_catalogHelperProduct = $catalogHelperProduct;
        $this->_resourceCategoryCollection = $resourceCategoryCollection;
        $this->_customerModelGroup = $customerModelGroup;
        $this->_productAttributeCollection = $productAttributeCollection;
        $this->_taxModelCalculation = $taxModelCalculation;
        $this->_taxHelperData = $taxHelperData;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_catalogInventoryHelperData = $catalogInventoryHelperData;
        $this->_taxModelConfig = $taxModelConfig;
        $this->_frameworkModelDate = $frameworkModelDate;
        $this->_modelConfigData = $modelConfigData;
        $this->_modelEntityType = $modelEntityType;
        $this->_modelEntityAttribute = $modelEntityAttribute;
        $this->_modelProductAction = $modelProductAction;
        $this->rulePricesStorage = $rulePricesStorage;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->localeDate = $localeDate;
        $this->_catalogModelCategory = $catalogModelCategory;
		$this->_ProductMetadataInterface = $productMetadataInterface;
		if($this->_ProductMetadataInterface->getEdition() == "Enterprise" && version_compare($this->_ProductMetadataInterface->getVersion(), '2.0.8', '>')===true) {
			$this->_entity_value = "row_id";
		} else {
			$this->_entity_value = "entity_id";
		}
    }


    /**
     * It has been determined during development that Product Sync uses around
     * 120kB of memory for each product it syncs, or around 10MB of memory for
     * each 100 product page.
     */
    const RECORDS_PER_PAGE = 100;

    const NOTIFICATION_GLOBAL_TYPE = "product_sync";
    const NOTIFICATION_STORE_TYPE_PREFIX = "product_sync_store_";

    public function getJobCode() {
        return "klevu_search_product_sync";
    }

    /**
     * Perform Product Sync on any configured stores, adding new products, updating modified and
     * deleting removed products since last sync.
     */
    public function run() {
        try {
            
            /* mark for update special price product */
            $this->markProductForUpdate();
            
            // Sync Data only for selected store from config wizard
            $firstSync = $this->_searchModelSession->getFirstSync();

            if(!empty($firstSync)){
                /** @var \Magento\Store\Model\Store $store */
                $this->reset();
                $onestore = $this->_storeModelStoreManagerInterface->getStore($firstSync);
                if (!$this->setupSession($onestore)) {
                    return;
                }
                
                $this->syncData($onestore);
                $this->runCategory($onestore);

                return;
            }
            
            if ($this->isRunning(2)) {
                // Stop if another copy is already running
                $this->log(\Zend\Log\Logger::INFO, "Stopping because another copy is already running.");
                return;
            }
            
            $stores = $this->_storeModelStoreManagerInterface->getStores();
            $config = $this->_searchHelperConfig;
            
            foreach ($stores as $store) {
                $this->reset();
                if (!$this->setupSession($store)) {
                    continue;
                }
                $this->syncData($store);
                $this->runCategory($store);

            }
            
            // update rating flag after all store view sync
            $rating_upgrade_flag = $config->getRatingUpgradeFlag();
            if($rating_upgrade_flag==0) {
                $config->saveRatingUpgradeFlag(1);
            }
        } catch (\Exception $e) {
            // Catch the exception that was thrown, log it, then throw a new exception to be caught the Magento cron.
            $this->_searchHelperData->log(\Zend\Log\Logger::CRIT, sprintf("Exception thrown in %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage()));
            throw $e;
        }
    }
    
    
    public function syncData($store){
                
                if ($this->rescheduleIfOutOfMemory()) {
                    return;
                }
                
                $config = $this->_searchHelperConfig;
                $session = $this->_searchModelSession;
                $firstSync = $session->getFirstSync();

                try {
                    $rating_upgrade_flag = $config->getRatingUpgradeFlag();
                    if(!empty($firstSync) || $rating_upgrade_flag==0) {
                        
                        $this->updateProductsRating($store);
                    }
                } catch(\Exception $e) {
                    $this->_searchHelperData->log(\Zend\Log\Logger::WARN, sprintf("Unable to update rating attribute %s", $store->getName()));
                }
                
                //set current store so will get proper bundle price 
                $this->_storeModelStoreManagerInterface->setCurrentStore($store->getId());
                
                $this->log(\Zend\Log\Logger::INFO, sprintf("Starting sync for %s (%s).", $store->getWebsite()->getName(), $store->getName()));
                $resource = $this->_frameworkModelResource;
				if($this->_ProductMetadataInterface->getEdition() == "Enterprise" && version_compare($this->_ProductMetadataInterface->getVersion(), '2.0.8', '>')===true) {
					
					$actions = array(
								'delete' => 
								$resource->getConnection()
									->select()
									->union(array(
										$resource->getConnection()
										->select()
										/*
										 * Select synced products in the current store/mode that are no longer enabled
										 * (don't exist in the products table, or have status disabled for the current
										 * store, or have status disabled for the default store) or are not visible
										 * (in the case of configurable products, check the parent visibility instead).
										 */
										->from(
											array('k' => $resource->getTableName("klevu_product_sync")),
											array('product_id' => "k.product_id", 'parent_id' => "k.parent_id")
										)
										->joinLeft(
											array('v' => $resource->getTableName("catalog_category_product_index")),
											"v.product_id = k.product_id AND v.store_id = :store_id",
											""
										)
										->joinLeft(
											array('p' => $resource->getTableName("catalog_product_entity")),
											"p.entity_id = k.product_id",
											""
										)
										->joinLeft(
											array('ss' => $this->getProductStatusAttribute()->getBackendTable()),
											"ss.attribute_id = :status_attribute_id AND ss.".$this->_entity_value." = p.".$this->_entity_value." AND ss.store_id = :store_id",
											""
										)
										->joinLeft(
											array('sd' => $this->getProductStatusAttribute()->getBackendTable()),
											"sd.attribute_id = :status_attribute_id AND sd.".$this->_entity_value." = p.".$this->_entity_value." AND sd.store_id = :default_store_id",
											""
										)
										->where("(k.store_id = :store_id) AND (k.type = :type) AND (k.test_mode = :test_mode) AND ((p.entity_id IS NULL) OR (CASE WHEN ss.value_id > 0 THEN ss.value ELSE sd.value END != :status_enabled) OR (CASE WHEN k.parent_id = 0 THEN k.product_id ELSE k.parent_id END NOT IN (?)) )",
											$resource->getConnection()
												->select()
												->from(
													array('i' => $resource->getTableName("catalog_category_product_index")),
													array('id' => "i.product_id")
												)
												->where("(i.store_id = :store_id) AND (i.visibility IN (:visible_both, :visible_search))")
											
										),
										$resource->getConnection()
											->select()
											/*
											 * Select products which are not associated with parent 
											 * but still parent exits in klevu product sync table with parent id
											 * 
											 */
											->from(
												array('ks' => $resource->getTableName("klevu_product_sync")),
												array('product_id' => "ks.product_id","parent_id" => 'ks.parent_id')
											)
											->where("(ks.parent_id !=0 AND ks.product_id NOT IN (?) AND ks.store_id = :store_id)",
												$resource->getConnection()
												->select()
												/*
												 * Select products from catalog super link table
												 */
												->from(
													array('s' => $resource->getTableName("catalog_product_super_link")),
													array('product_id' => "s.product_id")
												)
											)
										)
									)
									->group(array('k.product_id', 'k.parent_id'))
									->bind(array(
										'type'          => "products",
										'store_id'       => $store->getId(),
										'default_store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
										'test_mode'      => $this->isTestModeEnabled(),
										'status_attribute_id' => $this->getProductStatusAttribute()->getId(),
										'status_enabled' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
										'visible_both'   => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
										'visible_search' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
									)),

								'update' => $this->_frameworkModelResource->getConnection("core_write")
									->select()
									->union(array(
										// Select products without parents that need to be updated
										$this->_frameworkModelResource->getConnection("core_write")
											->select()
											/*
											 * Select synced non-configurable products for the current store/mode
											 * that are visible (using the category product index) and have been
											 * updated since last sync.
											 */
											->from(
												array('k' => $resource->getTableName("klevu_product_sync")),
												array('product_id' => "k.product_id", 'parent_id' => "k.parent_id")
											)
											->join(
												array('p' => $resource->getTableName("catalog_product_entity")),
												"p.entity_id = k.product_id",
												""
											)
											->join(
												array('i' => $resource->getTableName("catalog_category_product_index")),
												"i.product_id = k.product_id AND k.store_id = i.store_id AND i.visibility IN (:visible_both, :visible_search)",
												""
											)
											->where("(k.store_id = :store_id) AND (k.type = :type) AND (k.test_mode = :test_mode) AND (p.type_id != :configurable) AND (p.updated_at > k.last_synced_at)"),
										// Select products with parents (configurable) that need to be updated
							$this->_frameworkModelResource->getConnection("core_write")
											->select()
											/*
											 * Select configurable product children that are enabled (for the current
											 * store or for the default store), have visible parents (using the category
											 * product index) and have not been synced yet for the current store with
											 * the current parent.
											 */
											->from(
												array('e1' => $resource->getTableName("catalog_product_entity")),
												array('product_id' => "s1.product_id", 'parent_id' => "e1.entity_id")
											)
											->join(
												array('s1' => $resource->getTableName("catalog_product_super_link")),
												"e1.row_id= s1.parent_id",
												""
											)
											->join(
												array('i' => $resource->getTableName("catalog_category_product_index")),
												"i.product_id= e1.entity_id AND i.store_id = :store_id AND i.visibility IN (:visible_both, :visible_search)",
												""
											)
											->join(
												array('e2' => $resource->getTableName("catalog_product_entity")),
												"e2.entity_id = s1.product_id",
												""
											)
											->joinLeft(
												array('k' => $resource->getTableName("klevu_product_sync")),
												"e1.entity_id = k.parent_id AND s1.product_id = k.product_id AND k.store_id = :store_id AND k.test_mode = :test_mode AND k.type = :type",
												""
											)
											->joinLeft(
												array('ss' => $this->getProductStatusAttribute()->getBackendTable()),
												"ss.attribute_id = :status_attribute_id AND e2.row_id = ss.row_id AND ss.store_id = :default_store_id",
												""
											)
											->joinLeft(
												array('sd' => $this->getProductStatusAttribute()->getBackendTable()),
												"sd.attribute_id = :status_attribute_id AND sd.row_id = e2.row_id AND sd.store_id = :store_id",
												""
											)
											->where("(CASE WHEN sd.value_id > 1 THEN sd.value ELSE ss.value END = :status_enabled) AND ((e1.updated_at > k.last_synced_at) OR (e2.updated_at > k.last_synced_at))")
									))
									->group(array('k.product_id', 'k.parent_id'))
									->bind(array(
										'type'          => "products",
										'store_id' => $store->getId(),
										'default_store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
										'test_mode' => $this->isTestModeEnabled(),
										'configurable' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
										'visible_both' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
										'visible_search' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
										'status_attribute_id' => $this->getProductStatusAttribute()->getId(),
										'status_enabled' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
									)),

								'add' => $this->_frameworkModelResource->getConnection("core_write")
									->select()
									->union(array(
										// Select non-configurable products that need to be added
										$this->_frameworkModelResource->getConnection("core_write")
											->select()
											/*
											 * Select non-configurable products that are visible in the current
											 * store (using the category product index), but have not been synced
											 * for this store yet.
											 */
											->from(
												array('p' => $resource->getTableName("catalog_product_entity")),
												array('product_id' => "p.entity_id", 'parent_id' => "k.parent_id")
											)
											->join(
												array('i' => $resource->getTableName("catalog_category_product_index")),
												"p.entity_id = i.product_id AND i.store_id = :store_id AND i.visibility IN (:visible_both, :visible_search)",
												""
											)
											->joinLeft(
												array('k' => $resource->getTableName("klevu_product_sync")),
												"p.entity_id = k.product_id AND k.parent_id = 0 AND i.store_id = k.store_id AND k.test_mode = :test_mode AND k.type = :type",
												""
											)
											->where("(p.type_id != :configurable) AND (k.product_id IS NULL)"),
										// Select configurable parent & product pairs that need to be added
										$this->_frameworkModelResource->getConnection("core_write")
											->select()
											/*
											 * Select configurable product children that are enabled (for the current
											 * store or for the default store), have visible parents (using the category
											 * product index) and have not been synced yet for the current store with
											 * the current parent.
											 */
											->from(
												array('e1' => $resource->getTableName("catalog_product_entity")),
												array('product_id' => "s1.product_id", 'parent_id' => "e1.entity_id")
											)
											->join(
												array('s1' => $resource->getTableName("catalog_product_super_link")),
												"e1.row_id= s1.parent_id",
												""
											)
											->join(
												array('i' => $resource->getTableName("catalog_category_product_index")),
												"i.product_id= e1.entity_id AND i.store_id = :store_id AND i.visibility IN (:visible_both, :visible_search)",
												""
											)
											->join(
												array('e2' => $resource->getTableName("catalog_product_entity")),
												"e2.entity_id = s1.product_id",
												""
											)
											->joinLeft(
												array('k' => $resource->getTableName("klevu_product_sync")),
												"e1.entity_id = k.parent_id AND s1.product_id = k.product_id AND k.store_id = :store_id AND k.test_mode = :test_mode AND k.type = :type",
												""
											)
											->joinLeft(
												array('ss' => $this->getProductStatusAttribute()->getBackendTable()),
												"ss.attribute_id = :status_attribute_id AND e2.row_id = ss.row_id AND ss.store_id = :default_store_id",
												""
											)
											->joinLeft(
												array('sd' => $this->getProductStatusAttribute()->getBackendTable()),
												"sd.attribute_id = :status_attribute_id AND sd.row_id = e2.row_id AND sd.store_id = :store_id",
												""
											)
											->where("(CASE WHEN sd.value_id > 1 THEN sd.value ELSE ss.value END = :status_enabled) AND (k.product_id IS NULL)")
									))
									->group(array('product_id', 'parent_id'))
									->bind(array(
										'type' => "products",
										'store_id' => $store->getId(),
										'default_store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
										'test_mode' => $this->isTestModeEnabled(),
										'configurable' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
										'visible_both' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
										'visible_search' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
										'status_attribute_id' => $this->getProductStatusAttribute()->getId(),
										'status_enabled' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
									))
							);


				} else {
					$actions = array(
						'delete' => 
						$resource->getConnection()
							->select()
							->union(array(
								$resource->getConnection()
								->select()
								/*
								 * Select synced products in the current store/mode that are no longer enabled
								 * (don't exist in the products table, or have status disabled for the current
								 * store, or have status disabled for the default store) or are not visible
								 * (in the case of configurable products, check the parent visibility instead).
								 */
								->from(
									array('k' => $resource->getTableName("klevu_product_sync")),
									array('product_id' => "k.product_id", 'parent_id' => "k.parent_id")
								)
								->joinLeft(
									array('v' => $resource->getTableName("catalog_category_product_index")),
									"v.product_id = k.product_id AND v.store_id = :store_id",
									""
								)
								->joinLeft(
									array('p' => $resource->getTableName("catalog_product_entity")),
									"p.entity_id = k.product_id",
									""
								)
								->joinLeft(
									array('ss' => $this->getProductStatusAttribute()->getBackendTable()),
									"ss.attribute_id = :status_attribute_id AND ss.".$this->_entity_value." = k.product_id AND ss.store_id = :store_id",
									""
								)
								->joinLeft(
									array('sd' => $this->getProductStatusAttribute()->getBackendTable()),
									"sd.attribute_id = :status_attribute_id AND sd.".$this->_entity_value." = k.product_id AND sd.store_id = :default_store_id",
									""
								)
								->where("(k.store_id = :store_id) AND (k.type = :type) AND (k.test_mode = :test_mode) AND ((p.entity_id IS NULL) OR (CASE WHEN ss.value_id > 0 THEN ss.value ELSE sd.value END != :status_enabled) OR (CASE WHEN k.parent_id = 0 THEN k.product_id ELSE k.parent_id END NOT IN (?)) )",
									$resource->getConnection()
										->select()
										->from(
											array('i' => $resource->getTableName("catalog_category_product_index")),
											array('id' => "i.product_id")
										)
										->where("(i.store_id = :store_id) AND (i.visibility IN (:visible_both, :visible_search))")
									
								),
								$resource->getConnection()
									->select()
									/*
									 * Select products which are not associated with parent 
									 * but still parent exits in klevu product sync table with parent id
									 * 
									 */
									->from(
										array('ks' => $resource->getTableName("klevu_product_sync")),
										array('product_id' => "ks.product_id","parent_id" => 'ks.parent_id')
									)
									->where("(ks.parent_id !=0 AND ks.product_id NOT IN (?) AND ks.store_id = :store_id)",
										$resource->getConnection()
										->select()
										/*
										 * Select products from catalog super link table
										 */
										->from(
											array('s' => $resource->getTableName("catalog_product_super_link")),
											array('product_id' => "s.product_id")
										)
									)
								)
							)
							->group(array('k.product_id', 'k.parent_id'))
							->bind(array(
								'type'          => "products",
								'store_id'       => $store->getId(),
								'default_store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
								'test_mode'      => $this->isTestModeEnabled(),
								'status_attribute_id' => $this->getProductStatusAttribute()->getId(),
								'status_enabled' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
								'visible_both'   => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
								'visible_search' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
							)),

						'update' => $this->_frameworkModelResource->getConnection("core_write")
							->select()
							->union(array(
								// Select products without parents that need to be updated
								$this->_frameworkModelResource->getConnection("core_write")
									->select()
									/*
									 * Select synced non-configurable products for the current store/mode
									 * that are visible (using the category product index) and have been
									 * updated since last sync.
									 */
									->from(
										array('k' => $resource->getTableName("klevu_product_sync")),
										array('product_id' => "k.product_id", 'parent_id' => "k.parent_id")
									)
									->join(
										array('p' => $resource->getTableName("catalog_product_entity")),
										"p.entity_id = k.product_id",
										""
									)
									->join(
										array('i' => $resource->getTableName("catalog_category_product_index")),
										"i.product_id = k.product_id AND k.store_id = i.store_id AND i.visibility IN (:visible_both, :visible_search)",
										""
									)
									->where("(k.store_id = :store_id) AND (k.type = :type) AND (k.test_mode = :test_mode) AND (p.type_id != :configurable) AND (p.updated_at > k.last_synced_at)"),
								// Select products with parents (configurable) that need to be updated
								$this->_frameworkModelResource->getConnection("core_write")
									->select()
									/*
									 * Select synced products for the current store/mode that are configurable
									 * children (have entries in the super link table), are enabled for the current
									 * store (or the default store), have visible parents (using the category product
									 * index) and, either the product or the parent, have been updated since last sync.
									 */
									->from(
										array('k' => $resource->getTableName("klevu_product_sync")),
										array('product_id' => "k.product_id", 'parent_id' => "k.parent_id")
									)
									->join(
										array('s' => $resource->getTableName("catalog_product_super_link")),
										"k.parent_id = s.parent_id AND k.product_id = s.product_id",
										""
									)
									->join(
										array('i' => $resource->getTableName("catalog_category_product_index")),
										"k.parent_id = i.product_id AND k.store_id = i.store_id AND i.visibility IN (:visible_both, :visible_search)",
										""
									)
									->join(
										array('p1' => $resource->getTableName("catalog_product_entity")),
										"k.product_id = p1.entity_id",
										""
									)
									->join(
										array('p2' => $resource->getTableName("catalog_product_entity")),
										"k.parent_id = p2.entity_id",
										""
									)
									->joinLeft(
										array('ss' => $this->getProductStatusAttribute()->getBackendTable()),
										"ss.attribute_id = :status_attribute_id AND ss.".$this->_entity_value." = k.product_id AND ss.store_id = :store_id",
										""
									)
									->joinLeft(
										array('sd' => $this->getProductStatusAttribute()->getBackendTable()),
										"sd.attribute_id = :status_attribute_id AND sd.".$this->_entity_value." = k.product_id AND sd.store_id = :default_store_id",
										""
									)
									->where("(k.store_id = :store_id) AND (k.type = :type) AND (k.test_mode = :test_mode) AND (CASE WHEN ss.value_id > 0 OR ss.value = NULL THEN ss.value ELSE sd.value END = :status_enabled) AND ((p1.updated_at > k.last_synced_at) OR (p2.updated_at > k.last_synced_at))")
							))
							->group(array('k.product_id', 'k.parent_id'))
							->bind(array(
								'type'          => "products",
								'store_id' => $store->getId(),
								'default_store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
								'test_mode' => $this->isTestModeEnabled(),
								'configurable' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
								'visible_both' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
								'visible_search' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
								'status_attribute_id' => $this->getProductStatusAttribute()->getId(),
								'status_enabled' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
							)),

						'add' => $this->_frameworkModelResource->getConnection("core_write")
							->select()
							->union(array(
								// Select non-configurable products that need to be added
								$this->_frameworkModelResource->getConnection("core_write")
									->select()
									/*
									 * Select non-configurable products that are visible in the current
									 * store (using the category product index), but have not been synced
									 * for this store yet.
									 */
									->from(
										array('p' => $resource->getTableName("catalog_product_entity")),
										array('product_id' => "p.entity_id", 'parent_id' => "k.parent_id")
									)
									->join(
										array('i' => $resource->getTableName("catalog_category_product_index")),
										"p.entity_id = i.product_id AND i.store_id = :store_id AND i.visibility IN (:visible_both, :visible_search)",
										""
									)
									->joinLeft(
										array('k' => $resource->getTableName("klevu_product_sync")),
										"p.entity_id = k.product_id AND k.parent_id = 0 AND i.store_id = k.store_id AND k.test_mode = :test_mode AND k.type = :type",
										""
									)
									->where("(p.type_id != :configurable) AND (k.product_id IS NULL)"),
								// Select configurable parent & product pairs that need to be added
								$this->_frameworkModelResource->getConnection("core_write")
									->select()
									/*
									 * Select configurable product children that are enabled (for the current
									 * store or for the default store), have visible parents (using the category
									 * product index) and have not been synced yet for the current store with
									 * the current parent.
									 */
									->from(
										array('s' => $resource->getTableName("catalog_product_super_link")),
										array('product_id' => "s.product_id", 'parent_id' => "s.parent_id")
									)
									->join(
										array('i' => $resource->getTableName("catalog_category_product_index")),
										"s.parent_id = i.product_id AND i.store_id = :store_id AND i.visibility IN (:visible_both, :visible_search)",
										""
									)
									->joinLeft(
										array('ss' => $this->getProductStatusAttribute()->getBackendTable()),
										"ss.attribute_id = :status_attribute_id AND ss.".$this->_entity_value." = s.product_id AND ss.store_id = :store_id",
										""
									)
									->joinLeft(
										array('sd' => $this->getProductStatusAttribute()->getBackendTable()),
										"sd.attribute_id = :status_attribute_id AND sd.".$this->_entity_value." = s.product_id AND sd.store_id = :default_store_id",
										""
									)
									->joinLeft(
										array('k' => $resource->getTableName("klevu_product_sync")),
										"s.parent_id = k.parent_id AND s.product_id = k.product_id AND k.store_id = :store_id AND k.test_mode = :test_mode AND k.type = :type",
										""
									)
									->where("(CASE WHEN ss.value_id > 0 THEN ss.value ELSE sd.value END = :status_enabled) AND (k.product_id IS NULL)")
							))
							->group(array('k.product_id', 'k.parent_id'))
							->bind(array(
								'type' => "products",
								'store_id' => $store->getId(),
								'default_store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
								'test_mode' => $this->isTestModeEnabled(),
								'configurable' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
								'visible_both' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
								'visible_search' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
								'status_attribute_id' => $this->getProductStatusAttribute()->getId(),
								'status_enabled' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
							))
					);
				}

                $errors = 0;

                foreach ($actions as $action => $statement) {
						if ($this->rescheduleIfOutOfMemory()) {
							return;
						}
						
						$method = $action . "Products";
						$products = $this->_frameworkModelResource->getConnection()->fetchAll($statement, $statement->getBind());
						$total = count($products);
						$this->log(\Zend\Log\Logger::INFO, sprintf("Found %d products to %s.", $total, $action));
						$pages = ceil($total / static::RECORDS_PER_PAGE);
						for ($page = 1; $page <= $pages; $page++) {
							if ($this->rescheduleIfOutOfMemory()) {
								return;
							}

							$offset = ($page - 1) * static::RECORDS_PER_PAGE;
							$result = $this->$method(array_slice($products, $offset, static::RECORDS_PER_PAGE));

							if ($result !== true) {
								$errors++;
								$this->log(\Zend\Log\Logger::ERR, sprintf("Errors occurred while attempting to %s products %d - %d: %s",
									$action,
									$offset + 1,
									($offset + static::RECORDS_PER_PAGE <= $total) ? $offset + static::RECORDS_PER_PAGE : $total,
									$result
								));
							}
						}
                }

                $this->log(\Zend\Log\Logger::INFO, sprintf("Finished sync for %s (%s).", $store->getWebsite()->getName(), $store->getName()));
                
                
                if (!$config->isExtensionEnabled($store)) {
                    // Enable Klevu Search after the first sync
                    if(!empty($firstSync)) {
                        $config->setExtensionEnabledFlag(true, $store);
                        $this->log(\Zend\Log\Logger::INFO, sprintf("Automatically enabled Klevu Search on Frontend for %s (%s).",
                            $store->getWebsite()->getName(),
                            $store->getName()
                        ));
                    }
                    
                }
                $config->setLastProductSyncRun("now", $store);

                if ($errors == 0) {
                    // If Product Sync finished without any errors, notifications are not relevant anymore
                }
    
    }

    /**
     * Run the product sync manually, creating a cron schedule entry
     * to prevent other syncs from running.
     */
    public function runManually() {

        $time = date_create("now")->format("Y-m-d H:i:s");
        $schedule = $this->_cronModelSchedule;
        $schedule
            ->setJobCode($this->getJobCode())
            ->setCreatedAt($time)
            ->setScheduledAt($time)
            ->setExecutedAt($time)
            ->setStatus(\Magento\Cron\Model\Schedule::STATUS_RUNNING)
            ->save();

        try {

            $this->run();

        } catch (\Exception $e) {
            $this->_psrLogLoggerInterface->error($e);

            $schedule
                ->setMessages($e->getMessage())
                ->setStatus(\Magento\Cron\Model\Schedule::STATUS_ERROR)
                ->save();

            return;
        }

        $time = date_create("now")->format("Y-m-d H:i:s");
        $schedule
            ->setFinishedAt($time)
            ->setStatus(\Magento\Cron\Model\Schedule::STATUS_SUCCESS)
            ->save();

        return;
    }

    /**
     * Mark all products to be updated the next time Product Sync runs.
     *
     * @param \Magento\Store\Model\Store|int $store If passed, will only update products for the given store.
     *
     * @return $this
     */
    public function markAllProductsForUpdate($store = null) {
        $where = "";
        if ($store !== null) {
            $store = $this->_storeModelStoreManagerInterface->getStore($store);

            $where = $this->_frameworkModelResource->getConnection("core_write")->quoteInto("store_id =  ?", $store->getId());
        }

        $this->_frameworkModelResource->getConnection("core_write")->update(
            $this->_frameworkModelResource->getTableName('klevu_product_sync'),
            array('last_synced_at' => '0'),
            $where
        );

        return $this;
    }

    /**
     * Forget the sync status of all the products for the given Store and test mode.
     * If no store or test mode status is given, clear products for all stores and modes respectively.
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @param bool|null $test_mode
     *
     * @return int
     */
    public function clearAllProducts($store = null, $test_mode = null) {
        $select = $this->_frameworkModelResource->getConnection("core_write")
            ->select()
            ->from(
                array("k" => $this->_frameworkModelResource->getTableName("klevu_product_sync"))
            );

        if ($store) {
            $store = $this->_storeModelStoreManagerInterface->getStore($store);

            $select->where("k.store_id = ?", $store->getId());
        }

        if ($test_mode !== null) {
            $test_mode = ($test_mode) ? 1 : 0;

            $select->where("k.test_mode = ?", $test_mode);
        }

        $result = $this->_frameworkModelResource->getConnection("core_write")->query($select->deleteFromSelect("k"));
        return $result->rowCount();
    }

    /**
     * Return the product status attribute model.
     *
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
     */
    protected function getProductStatusAttribute() {
        if (!$this->hasData("status_attribute")) {
            $this->setData("status_attribute", $this->_eavModelConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status'));
        }

        return $this->getData("status_attribute");
    }

    /**
     * Return the product visibility attribute model.
     *
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
     */
    protected function getProductVisibilityAttribute() {
        if (!$this->hasData("visibility_attribute")) {
            $this->setData("visibility_attribute", $this->_eavModelConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'visibility'));
        }

        return $this->getData("visibility_attribute");
    }

    /**
     * Setup an API session for the given store. Sets the store and session ID on self. Returns
     * true on success or false if Product Sync is disabled, store is not configured or the
     * session API call fails.
     *
     * @param \Magento\Store\Model\Store $store
     *
     * @return bool
     */
    protected function setupSession(\Magento\Store\Model\Store\Interceptor $store) {
        $config = $this->_searchHelperConfig;
		if (!$config->isProductSyncEnabled($store->getId())) {
            $this->log(\Zend\Log\Logger::INFO, sprintf("Disabled for %s (%s).", $store->getWebsite()->getName(), $store->getName()));
            return null;
        }

        $api_key = $config->getRestApiKey($store->getId());
        if (!$api_key) {
            $this->log(\Zend\Log\Logger::INFO, sprintf("No API key found for %s (%s).", $store->getWebsite()->getName(), $store->getName()));
            return null;
        }
     
        $response = $this->_apiActionStartsession->execute(array(
            'api_key' => $api_key,
            'store' => $store,
        ));
        
        if ($response->isSuccess()) {
            $this->addData(array(
                'store'      => $store,
                'session_id' => $response->getSessionId()
            ));
            return true;
        } else {
            $this->log(\Zend\Log\Logger::ERR, sprintf("Failed to start a session for %s (%s): %s",
                $store->getWebsite()->getName(),
                $store->getName(),
                $response->getMessage()
            ));

            if ($response instanceof \Klevu\Search\Model\Api\Response\Rempty) {
                
                $this->log(\Zend\Log\Logger::ERR, sprintf("Product Sync failed for %s (%s): Could not contact Klevu.",
                $store->getWebsite()->getName(),
                $store->getName()
                ));
              
            } else {
                
                $this->log(\Zend\Log\Logger::ERR, sprintf("Product Sync failed for %s (%s): %s",
                $store->getWebsite()->getName(),
                $store->getName(),
                $response->getMessage()
                ));
            }

            return false;
        }
    }

    /**
     * Delete the given products from Klevu Search. Returns true if the operation was
     * successful, or the error message if the operation failed.
     *
     * @param array $data List of products to delete. Each element should be an array
     *                    containing an element with "product_id" as the key and product id as
     *                    the value and an optional "parent_id" element with the parent id.
     *
     * @return bool|string
     */
    protected function deleteProducts(array $data) {
        $total = count($data);

        $response = $this->_apiActionDeleterecords
            ->setStore($this->_storeModelStoreManagerInterface->getStore())
            ->execute(array(
            'sessionId' => $this->getSessionId(),
            'records'   => array_map(function ($v) {
                return array('id' => $this->_searchHelperData->getKlevuProductId($v['product_id'], $v['parent_id']));
            }, $data)
        ));

        if ($response->isSuccess()) {
            $resource = $this->_frameworkModelResource;
            $connection = $resource->getConnection("core_write");

            $select = $connection
                ->select()
                ->from(array('k' => $resource->getTableName("klevu_product_sync")))
                ->where("k.store_id = ?", $this->_storeModelStoreManagerInterface->getStore()->getId())
                ->where("k.type = ?","products")
                ->where("k.test_mode = ?", $this->isTestModeEnabled());

            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }

            $or_where = array();
            for ($i = 0; $i < count($data); $i++) {
                if (isset($skipped_record_ids[$i])) {
                    continue;
                }
                $or_where[] = sprintf("(%s AND %s)",
                    $connection->quoteInto("k.product_id = ?", $data[$i]['product_id']),
                    $connection->quoteInto("k.parent_id = ?", $data[$i]['parent_id']),
                    $connection->quoteInto("k.type = ?", "products")
                );
            }
            $select->where(implode(" OR ", $or_where));

            $connection->query($select->deleteFromSelect("k"));

            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d product%s failed (%s)",
                    $skipped_count,
                    ($skipped_count > 1) ? "s" : "",
                    implode(", ", $skipped_records["messages"])
                );
            } else {
                return true;
            }
        } else {
            return sprintf("%d product%s failed (%s)",
                $total,
                ($total > 1) ? "s" : "",
                $response->getMessage()
            );
        }
    }

    /**
     * Update the given products on Klevu Search. Returns true if the operation was successful,
     * or the error message if it failed.
     *
     * @param array $data List of products to update. Each element should be an array
     *                    containing an element with "product_id" as the key and product id as
     *                    the value and an optional "parent_id" element with the parent id.
     *
     * @return bool|string
     */
    protected function updateProducts(array $data) {
		
        $total = count($data);
       
        $dataToSend = $this->addProductSyncData($data);
		if(!empty($dataToSend) && is_numeric($dataToSend)){
		    $data = array_slice($data, 0, $dataToSend);
		}
		

        $response = $this->_apiActionUpdaterecords
            ->setStore($this->_storeModelStoreManagerInterface->getStore())
            ->execute(array(
            'sessionId' => $this->getSessionId(),
            'records'   => $data
        ));

        if ($response->isSuccess()) {
            $helper = $this->_searchHelperData;
            $connection = $this->_frameworkModelResource->getConnection("core_write");

            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }

            $where = array();
            for ($i = 0; $i < count($data); $i++) {
                if (isset($skipped_record_ids[$i])) {
                    continue;
                }
                
				if(isset($data[$i]['id'])) {
					$ids = $helper->getMagentoProductId($data[$i]['id']);
					if(!empty($ids)) {
						$where[] = sprintf("(%s AND %s AND %s)",
							$connection->quoteInto("product_id = ?", $ids['product_id']),
							$connection->quoteInto("parent_id = ?", $ids['parent_id']),
							$connection->quoteInto("type = ?", "products")
						);
				    }
				}
            }
            
			if(!empty($where)) {
				$where = sprintf("(%s) AND (%s) AND (%s)",
					$connection->quoteInto("store_id = ?", $this->_storeModelStoreManagerInterface->getStore()->getId()),
					$connection->quoteInto("test_mode = ?", $this->isTestModeEnabled()),
					implode(" OR ", $where)
				);

				$this->_frameworkModelResource->getConnection("core_write")->update(
					$this->_frameworkModelResource->getTableName('klevu_product_sync'),
					array('last_synced_at' => $this->_searchHelperCompat->now()),
					$where
				);
			}

            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d product%s failed (%s)",
                    $skipped_count,
                    ($skipped_count > 1) ? "s" : "",
                    implode(", ", $skipped_records["messages"])
                );
            } else {
                return true;
            }
        } else {
            return sprintf("%d product%s failed (%s)",
                $total,
                ($total > 1) ? "s" : "",
                $response->getMessage()
            );
        }
    }

    /**
     * Add the given products to Klevu Search. Returns true if the operation was successful,
     * or the error message if it failed.
     *
     * @param array $data List of products to add. Each element should be an array
     *                    containing an element with "product_id" as the key and product id as
     *                    the value and an optional "parent_id" element with the parent id.
     *
     * @return bool|string
     */
    protected function addProducts(array $data) {
        $total = count($data);

        $dataToSend = $this->addProductSyncData($data);
		if(!empty($dataToSend) && is_numeric($dataToSend)){
		    $data = array_slice($data, 0, $dataToSend);
		}
        $response = $this->_apiActionAddrecords
            ->setStore($this->_storeModelStoreManagerInterface->getStore())
            ->execute(array(
            'sessionId' => $this->getSessionId(),
            'records'   => $data
        ));

        if ($response->isSuccess()) {
              
            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }

            $sync_time = $this->_searchHelperCompat->now();

            foreach($data as $i => &$record) {
                if (isset($skipped_record_ids[$i])) {
                    unset($data[$i]);
                    continue;
                }

                $ids = $this->_searchHelperData->getMagentoProductId($data[$i]['id']);

                $record = array(
                    $ids["product_id"],
                    $ids["parent_id"],
                    $this->_storeModelStoreManagerInterface->getStore()->getId(),
                    $this->isTestModeEnabled(),
                    $sync_time,
                    "products"
                );
            }
            
			if(!empty($data)) {
				foreach($data as $key => $value){
					$write =  $this->_frameworkModelResource->getConnection("core_write");
					$query = "replace into ".$this->_frameworkModelResource->getTableName('klevu_product_sync')
						   . "(product_id, parent_id, store_id, test_mode, last_synced_at, type) values "
						   . "(:product_id, :parent_id, :store_id, :test_mode, :last_synced_at, :type)";

					$binds = array(
						'product_id' => $value[0],
						'parent_id' => $value[1],
						'store_id' => $value[2],
						'test_mode' => $value[3],
						'last_synced_at'  => $value[4],
						'type' => $value[5]
					);
					$write->query($query, $binds);
					
				}
			}

            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d product%s failed (%s)",
                    $skipped_count,
                    ($skipped_count > 1) ? "s" : "",
                    implode(", ", $skipped_records["messages"])
                );
            } else {
                return true;
            }
        } else {
            return sprintf("%d product%s failed (%s)",
                $total,
                ($total > 1) ? "s" : "",
                $response->getMessage()
            );
        }
    }

    /**
     * Add the Product Sync data to each product in the given list. Updates the given
     * list directly to save memory.
     *
     * @param array $products An array of products. Each element should be an array with
     *                        containing an element with "id" as the key and the product
     *                        ID as the value.
     *
     * @return $this
     */
    protected function addProductSyncData(&$products) {
        
        $product_ids = array();
        $parent_ids = array();
        foreach ($products as $product) {
            $product_ids[] = $product['product_id'];
            if ($product['parent_id'] != 0) {
                $product_ids[] = $product['parent_id'];
                $parent_ids[] = $product['parent_id'];
            }
        }
        $product_ids = array_unique($product_ids);
        $parent_ids = array_unique($parent_ids);
		$config = $this->_searchHelperConfig;
		
		if($config->isCollectionMethodEnabled()) {
			$data = $this->_catalogModelProduct->getCollection()
				->addIdFilter($product_ids)
				->setStore($this->_storeModelStoreManagerInterface->getStore())
				->addStoreFilter()
				->addAttributeToSelect($this->getUsedMagentoAttributes());
		 
			$data->load()
				->addCategoryIds();
		}
		
		$check_root_magento = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Backend\Block\Page\RequireJs')->getViewFileUrl('requirejs/require.js');
		$check_pub = explode('/',$check_root_magento);
		
		
		$dir = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Filesystem\DirectoryList');  
		$mediadir = $dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
		
		if(!in_array('pub',$check_pub)){
			$mediadir = str_replace('/pub/','/',$mediadir);
		}
		
		// Get the stock,url,visibity of product from database
        $url_rewrite_data = $this->getUrlRewriteData($product_ids);
        //$visibility_data = $this->getVisibilityData($product_ids);
        $stock_data = $this->getStockData($product_ids);
        $attribute_map = $this->getAttributeMap();
        if($config->isSecureUrlEnabled($this->_storeModelStoreManagerInterface->getStore()->getId())) {
            $base_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK,true);
            $media_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA,true);
          
       }else {
            $base_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
            $media_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        }
        $currency = $this->_storeModelStoreManagerInterface->getStore()->getDefaultCurrencyCode();
        //$media_url .= $this->_productMediaConfig->getBaseMediaUrlAddition();
        
		if(!in_array('pub',$check_pub)){
			$media_url = str_replace('/pub','/',$media_url);
		}
		
        $rc = 0; 
        foreach ($products as $index => &$product) {
			try {
				if($rc % 5 == 0) {
					if ($this->rescheduleIfOutOfMemory()) {
						return $rc;
					}
				}
				
				if($config->isCollectionMethodEnabled()) {
					$item = $data->getItemById($product['product_id']);
					$parent = ($product['parent_id'] != 0) ?  $data->getItemById($product['parent_id']) : null;
					
					$this->log(\Zend\Log\Logger::DEBUG, sprintf("Load by collection method for product ID %d", $product['product_id']));
				} else {
					$item = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product')->load($product['product_id']);
					$item->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
					
					$parent = ($product['parent_id'] != 0) ?  \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product')->load($product['parent_id'])->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID): null;
				}
				
				if (!$item) {
					// Product data query did not return any data for this product
					// Remove it from the list to skip syncing it
					$this->log(\Zend\Log\Logger::WARN, sprintf("Failed to retrieve data for product ID %d", $product['product_id']));
					unset($products[$index]);
					continue;
				}
				
				/* Use event to add any external module data to product */
				$this->_frameworkEventManagerInterface->dispatch('add_external_data_to_sync', array(
					'parent' => $parent,
					'product'=> &$product,
					'store' => $this->_storeModelStoreManagerInterface->getStore()
				));

				// Add data from mapped attributes

				foreach ($attribute_map as $key => $attributes) {
					$product[$key] = null;

					switch ($key) {
						case "boostingAttribute":
							foreach ($attributes as $attribute) {
								if ($parent && $parent->getData($attribute)) {
									$product[$key] = $parent->getData($attribute);
									break;
								} else {
									$product[$key] = $item->getData($attribute);
									break;
								}
							}
							break;
						case "rating":
							foreach ($attributes as $attribute) {
								if ($parent && $parent->getData($attribute)) {
									$product[$key] = $this->convertToRatingStar($parent->getData($attribute));
									break;
								} else {
									$product[$key] = $this->convertToRatingStar($item->getData($attribute));
									break;
								}
							}
							break;                        
						case "otherAttributeToIndex":
						case "other":
							$product[$key] = array();
							foreach ($attributes as $attribute) {
								if ($item) {
									$product[$key][$attribute] = $this->getAttributeData($attribute, $item->getData($attribute));
								} else if ($parent) {
									$product[$key][$attribute] = $this->getAttributeData($attribute, $parent->getData($attribute));
								}
							}
							break;
						 case "sku":
							foreach ($attributes as $attribute) {
								if ($parent && $parent->getData($attribute)) {
									$product[$key] = $this->_searchHelperData->getKlevuProductSku($item->getData($attribute), $parent->getData($attribute));
									break;
								} else {
									$product[$key] = $item->getData($attribute);
									break;
								}
							}
							break;
						case "name":
							foreach ($attributes as $attribute) {
								if ($parent && $parent->getData($attribute)) {
									$product[$key] = $parent->getData($attribute);
									break;
								}else if ($item->getData($attribute)) {
									$product[$key] = $item->getData($attribute);
									break;
								}
							}
							break;
						case "image":
							foreach ($attributes as $attribute) {
								if ($item->getData($attribute) && $item->getData($attribute) != "no_selection") {
									$product[$key] = $item->getData($attribute);
									break;
								} else if ($parent && $parent->getData($attribute) && $parent->getData($attribute) != "no_selection") {
									$product[$key] = $parent->getData($attribute);
									break;
								}
							}
							if ($product[$key] != "" && strpos($product[$key], "http") !== 0) {
								// Prepend media base url for relative image locations
								//generate thumbnail image for each products
								$this->thumbImage($product[$key],$mediadir);
								$imageResized = $mediadir.DIRECTORY_SEPARATOR."klevu_images".$product[$key];
									if (file_exists($imageResized)) {
										$product[$key] =  $media_url."klevu_images".$product[$key];
										
									}else{
										$product[$key] = $media_url . $product[$key];
									}
							}
							break;
						case "salePrice":

							// Default to 0 if price can't be determined
							$product['salePrice'] = 0;

							if ($parent && $parent->getData("type_id") == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
								// Calculate configurable product price based on option values
								$ruleprice = $this->calculateFinalPriceFront($parent,\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,$parent->getId(),$this->_storeModelStoreManagerInterface->getStore());
								if(!empty($ruleprice)){
									$fprice = min($ruleprice,$parent->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());    
								} else {
									$fprice = $parent->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
								}

								$price = (isset($fprice)) ? $fprice: $parent->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

								// show low price for config products
								$product['startPrice'] = $this->processPrice($price , $parent);
								
								// also send sale price for sorting and filters for klevu 
								$product['salePrice'] = $this->processPrice($price , $parent);
							} else {
								// Use price index prices to set the product price and start/end prices if available
								// Falling back to product price attribute if not
								if ($item) {
									$ruleprice = $this->calculateFinalPriceFront($item,\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,$item->getId(),$this->_storeModelStoreManagerInterface->getStore());
									if ($item->getData('type_id') == "grouped") {
										$this->_searchHelperData->getGroupProductMinPrice($item,$this->_storeModelStoreManagerInterface->getStore());
										if(!empty($ruleprice)){
											$sPrice = min($ruleprice,$item->getFinalPrice());    
										}else{
											$sPrice = $item->getFinalPrice();    
										}
										$product['startPrice'] = $this->processPrice($sPrice,$item);
										$product["salePrice"] = $this->processPrice($sPrice,$item);
									}else if ($item->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
										list($minimalPrice, $maximalPrice) = $this->_searchHelperData->getBundleProductPrices($item,$this->_storeModelStoreManagerInterface->getStore());
										
										$minPrice = $this->processPrice($this->convertPrice($minimalPrice,$this->_storeModelStoreManagerInterface->getStore()), $item);
										
										$maxPrice = $this->processPrice($this->convertPrice($maximalPrice,$this->_storeModelStoreManagerInterface->getStore()), $item);
										
										$product["salePrice"] = $minPrice;
										$product['startPrice'] = $minPrice;
										$product['toPrice'] = $maxPrice;
									}else{
										// Always use minimum price as the sale price as it's the most accurate
										if(!empty($ruleprice)){
											$sPrice = min($ruleprice,$item->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());    
										} else{
											$sPrice = $item->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
										}
										$product['salePrice'] = $this->processPrice($sPrice , $item);
									}
									
								} else {
									if ($item->getData("price") !== null) {
										$product["salePrice"] = $this->processPrice($item->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), $item);
									}
								}
							}
							break;
						case "price":
								// Default to 0 if price can't be determined
								$product['price'] = 0;
								if ($parent && $parent->getData("type_id") == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
									 
									// Calculate configurable product price based on option values
									$orgPrice = $parent->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
									$price = (isset($orgPrice)) ? $orgPrice: $this->convertPrice($parent->getData("price"),$this->_storeModelStoreManagerInterface->getStore());

									// also send sale price for sorting and filters for klevu 
									$product['price'] = $this->processPrice($price , $parent);
									
								} else {
								  // Use price index prices to set the product price and start/end prices if available
								  // Falling back to product price attribute if not
									if ($item) {
										if ($item->getData('type_id') == "grouped") {
											// Get the group product original price 
											$this->_searchHelperData->getGroupProductOriginalPrice($item,$this->getStore());
											$sPrice = $item->getPrice();
											$product["price"] = $this->processPrice($sPrice,$item);
										}else if ($item->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
											// product detail page always shows final price as price so we also taken final price as original price only for bundle product 
											list($minimalPrice, $maximalPrice) = $this->_searchHelperData->getBundleProductPrices($item,$this->getStore());
											
											$product["price"] = $this->processPrice($this->convertPrice($minimalPrice,$this->_storeModelStoreManagerInterface->getStore()), $item);
											
										}else {
											// Always use minimum price as the sale price as it's the most accurate
											$product['price'] = $this->processPrice($item->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), $item);
										}
									  
									} else {
										if ($item->getData("price") !== null) {
											$product["price"] = $this->processPrice($item->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),$item);
										} 
									}
								}
							break;
						default:
						
							foreach ($attributes as $attribute) {
								if ($item->getData($attribute)) {
									$product[$key] = $this->getAttributeData($attribute, $item->getData($attribute));
									break;
								} else if ($parent && $parent->getData($attribute)) {
									$product[$key] = $this->getAttributeData($attribute, $parent->getData($attribute));
									break;
								}
							}
					}
				}

				// Add non-attribute data
				$product['currency'] = $currency;

				if ($parent) {
					$product['category'] = $this->getLongestPathCategoryName($parent->getCategoryIds());
					$product['listCategory'] = $this->getCategoryNames($parent->getCategoryIds());
				} else if ($item->getCategoryIds()) {
					$product['category'] = $this->getLongestPathCategoryName($item->getCategoryIds());
					$product['listCategory'] = $this->getCategoryNames($item->getCategoryIds());
				} else {
					$product['category'] = "";
					$product['listCategory'] = "KLEVU_PRODUCT";
				}
				
				
				if ($parent) {
					//Get the price based on customer group
					$product['groupPrices'] = $this->getGroupPrices($parent);
				} else if($item) {
					$product['groupPrices'] = $this->getGroupPrices($item);
				} else {
					$product['groupPrices'] = "";
				}
				
				

				// Use the parent URL if the product is invisible (and has a parent) and
				// use a URL rewrite if one exists, falling back to catalog/product/view
				
				if($parent) {
					 $product['url'] = $base_url . (
						  (isset($url_rewrite_data[$product['parent_id']])) ?
							  $url_rewrite_data[$product['parent_id']] :
							  "catalog/product/view/id/" . $product['parent_id']
						  );                
				} else {
					 $product['url'] = $base_url . (
						(isset($url_rewrite_data[$product['product_id']])) ?
							$url_rewrite_data[$product['product_id']] :
							"catalog/product/view/id/" . $product['product_id']
					);
				}
				

				// Add stock data
				   if(isset($stock_data[$product['product_id']])) {
					$product['inStock'] = ($stock_data[$product['product_id']]) ? "yes" : "no";
				}

				// Configurable product relation
				if ($product['parent_id'] != 0) {
					$product['itemGroupId'] = $product['parent_id'];
				}

				// Set ID data
				$product['id'] = $this->_searchHelperData->getKlevuProductId($product['product_id'], $product['parent_id']);
				unset($product['product_id']);
				unset($product['parent_id']);
				
				if($item) {
					$item->clearInstance();
					$item = null;
				}
				if($parent) {
					if(!$config->isCollectionMethodEnabled()) {
						$parent->clearInstance();
					}
					$parent = null;
				}
				gc_collect_cycles();
				
			} catch(\Exception $e) {
				$this->_searchHelperData->log(\Zend\Log\Logger::CRIT, sprintf("Exception thrown in %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage()));
				$markAsSync = array();
				$markAsSync[] = array($product['product_id'],$product['parent_id'],$this->getStore()->getId(),0,$this->_searchHelperCompat->now(),"products");
				$write =  $this->_frameworkModelResource->getConnection("core_write");
				$query = "replace into ".$this->_frameworkModelResource->getTableName('klevu_product_sync')
					   . "(product_id, parent_id, store_id, test_mode, last_synced_at, type,error_flag) values "
					   . "(:product_id, :parent_id, :store_id, :test_mode, :last_synced_at, :type,:error_flag)";
				$binds = array(
					'product_id' => $markAsSync[0][0],
					'parent_id' => $markAsSync[0][1],
					'store_id' => $markAsSync[0][2],
					'test_mode' => $markAsSync[0][3],
					'last_synced_at'  => $markAsSync[0][4],
					'type' => $markAsSync[0][5],
					'error_flag' => 1
				);
				$write->query($query, $binds);
				//unset($products[$index]);
				continue;
			}
        }
   
        return $this;
    }

    /**
     * Return the URL rewrite data for the given products for the current store.
     *
     * @param array $product_ids A list of product IDs.
     *
     * @return array A list with product IDs as keys and request paths as values.
     */
    protected function getUrlRewriteData($product_ids) {
        $stmt = $this->_frameworkModelResource->getConnection("core_write")->query(
            $this->_searchHelperCompat->getProductUrlRewriteSelect($product_ids, 0, $this->_storeModelStoreManagerInterface->getStore()->getId())
        );

        $data = array();
        
        while ($row = $stmt->fetch()) {
            if (!isset($data[$row['entity_id']])) {
                $data[$row['entity_id']] = $row['request_path'];
            }
        }

        return $data;
    }

    /**
     * Return the visibility data for the given products for the current store.
     *
     * @param array $product_ids A list of product IDs.
     *
     * @return array A list with product IDs as keys and boolean visibility values.
     */
    protected function getVisibilityData($product_ids) {
        $stmt = $this->_frameworkModelResource->getConnection("core_write")->query(
            $this->_frameworkModelResource->getConnection("core_write")
                ->select()
                ->from(
                    array('p' => $this->_frameworkModelResource->getTableName("catalog_product_entity")),
                    array(
                        'product_id' => "p.entity_id"
                    )
                )
                ->joinLeft(
                    array('vs' => $this->getProductVisibilityAttribute()->getBackendTable()),
                    "vs.attribute_id = :visibility_attribute_id AND vs.".$this->_entity_value." = p.entity_id AND vs.store_id = :store_id",
                    ""
                )
                ->joinLeft(
                    array('vd' => $this->getProductVisibilityAttribute()->getBackendTable()),
                    "vd.attribute_id = :visibility_attribute_id AND vs.".$this->_entity_value." = p.entity_id AND vd.store_id = :default_store_id",
                    array(
                        "visibility" => "IF(vs.value IS NOT NULL, vs.value, vd.value)"
                    )
                )
                ->where("p.entity_id IN (?)", $product_ids),
            array(
                "visibility_attribute_id" => $this->getProductVisibilityAttribute()->getId(),
                "store_id"                => $this->_storeModelStoreManagerInterface->getStore()->getId(),
                "default_store_id"        => \Magento\Store\Model\Store::DEFAULT_STORE_ID
            )
        );

        $data = array();
        while ($row = $stmt->fetch()) {
            $data[$row['product_id']] = ($row['visibility'] != \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) ? true : false;
        }

        return $data;
    }

    /**
     * Return the "Is in stock?" flags for the given products.
     * Considers if the stock is managed on the product or per store when deciding if a product
     * is in stock.
     *
     * @param array $product_ids A list of product IDs.
     *
     * @return array A list with product IDs as keys and "Is in stock?" booleans as values.
     */
    protected function getStockData($product_ids) {
        $stmt = $this->_frameworkModelResource->getConnection("core_write")->query(
            $this->_frameworkModelResource->getConnection("core_write")
                ->select()
                ->from(
                    array('s' => $this->_frameworkModelResource->getTableName("cataloginventory_stock_status")),
                    array(
                        'product_id'   => "s.product_id",
                        'in_stock'     => "s.stock_status"
                    )
                )
                ->where("s.product_id IN (?)", $product_ids)
                ->where("s.website_id = ?",$this->_storeModelStoreManagerInterface->getStore()->getWebsiteId())
        );

        $data = array();
        while ($row = $stmt->fetch()) {
            
            $data[$row['product_id']] = ($row['in_stock']) ? true : false;
        }

        return $data;
    }



    /**
     * Return a map of Klevu attributes to Magento attributes.
     *
     * @return array
     */
    protected function getAttributeMap() {
        if (!$this->hasData('attribute_map')) {

            $attribute_map = array();

            $automatic_attributes = $this->getAutomaticAttributes();

            $attribute_map = $this->prepareAttributeMap($attribute_map, $automatic_attributes);
    

            // Add otherAttributeToIndex to $attribute_map.
            $otherAttributeToIndex = $this->_searchHelperConfig->getOtherAttributesToIndex($this->_storeModelStoreManagerInterface->getStore());
            if(!empty($otherAttributeToIndex)) {
                $attribute_map['otherAttributeToIndex'] = $otherAttributeToIndex;
            }
            // Add boostingAttribute to $attribute_map.
            $boosting_value = $this->_searchHelperConfig->getBoostingAttribute($this->_storeModelStoreManagerInterface->getStore());
            if($boosting_value != "use_boosting_rule") {
                if(($boosting_attribute = $this->_searchHelperConfig->getBoostingAttribute($this->_storeModelStoreManagerInterface->getStore())) && !is_null($boosting_attribute)) {
                    $attribute_map['boostingAttribute'][] = $boosting_attribute;
                }
            }

            $this->setData('attribute_map', $attribute_map);
        }

        return $this->getData('attribute_map');
    }

    /**
     * Returns an array of all automatically matched attributes. Includes defaults and filterable in search attributes.
     * @return array
     */
    public function getAutomaticAttributes() {
        if(!$this->hasData('automatic_attributes')) {
            // Default mapped attributes
            $default_attributes = $this->_searchHelperConfig->getDefaultMappedAttributes();
            $attributes = array();
            for($i = 0; $i < count($default_attributes['klevu_attribute']); $i++) {
                $attributes[] = array(
                    'klevu_attribute' => $default_attributes['klevu_attribute'][$i],
                    'magento_attribute' => $default_attributes['magento_attribute'][$i]
                );
            }

            // Get all layered navigation / filterable in search attributes

            foreach($this->getLayeredNavigationAttributes() as $layeredAttribute) {
                $attributes[] = array (
                    'klevu_attribute' => 'other',
                    'magento_attribute' => $layeredAttribute
                );
            }

            $this->setData('automatic_attributes', $attributes);
            // Update the store system config with the updated automatic attributes map.
            $this->_searchHelperConfig->setAutomaticAttributesMap($attributes, $this->_storeModelStoreManagerInterface->getStore());
        }

        return $this->getData('automatic_attributes');
    }

    /**
     * Takes system configuration attribute data and adds to $attribute_map
     * @param $attribute_map
     * @param $additional_attributes
     * @return array
     */
    protected function prepareAttributeMap($attribute_map, $additional_attributes) {

        foreach ($additional_attributes as $mapping) {
            if (!isset($attribute_map[$mapping['klevu_attribute']])) {
                $attribute_map[$mapping['klevu_attribute']] = array();
            }
            $attribute_map[$mapping['klevu_attribute']][] = $mapping['magento_attribute'];
        }
        return $attribute_map;
    }

    /**
     * Return the attribute codes for all filterable in search attributes.
     * @return array
     */
    protected function getLayeredNavigationAttributes() {
        $attributes = $this->_searchHelperConfig->getDefaultMappedAttributes();
        $resource = $this->_frameworkModelResource;
        $select = $this->_frameworkModelResource->getConnection("core_write")
            ->select()
            ->from(
                array("a" => $resource->getTableName("eav_attribute")),
                array("attribute" => "a.attribute_code")
            )
            ->join(
                array("ca" => $resource->getTableName("catalog_eav_attribute")),
                "ca.attribute_id = a.attribute_id",
                ""
            )
            // Only if the attribute is filterable in search, i.e. attribute appears in search layered navigation.
            ->where("ca.is_filterable_in_search = ?", "1")
            // Make sure we exclude the attributes thar synced by default.
            ->where("a.attribute_code NOT IN(?)", array_unique($attributes['magento_attribute']))
            ->group(array("attribute_code"));

        return $this->_frameworkModelResource->getConnection("core_write")->fetchCol($select);
    }

    /**
     * Return the attribute codes for all attributes currently used in
     * configurable products.
     *
     * @return array
     */
    protected function getConfigurableAttributes() {
        $select = $this->_frameworkModelResource->getConnection("core_write")
            ->select()
            ->from(
                array("a" => $this->_frameworkModelResource->getTableName("eav_attribute")),
                array("attribute" => "a.attribute_code")
            )
            ->join(
                array("s" => $this->_frameworkModelResource->getTableName("catalog_product_super_attribute")),
                "a.attribute_id = s.attribute_id",
                ""
            )
            ->group(array("a.attribute_code"));

        return $this->_frameworkModelResource->getConnection("core_write")->fetchCol($select);
    }

    /**
     * Return a list of all Magento attributes that are used by Product Sync
     * when collecting product data.
     *
     * @return array
     */
    protected function getUsedMagentoAttributes() {
        $result = array();

        foreach ($this->getAttributeMap() as $attributes) {
            $result = array_merge($result, $attributes);
        }

        $result = array_merge($result, $this->getConfigurableAttributes());

        return array_unique($result);
    }

    /**
     * Return an array of category paths for all the categories in the
     * current store, not including the store root.
     *
     * @return array A list of category paths where each key is a category
     *               ID and each value is an array of category names for
     *               each category in the path, the last element being the
     *               name of the category referenced by the ID.
     */
    protected function getCategoryPaths() {
        if (!$category_paths = $this->getData('category_paths')) {
            $category_paths = array();
            $rootId = $this->_storeModelStoreManagerInterface->getStore()->getRootCategoryId();  
            $collection = $this->_resourceCategoryCollection
                ->setStoreId($this->_storeModelStoreManagerInterface->getStore()->getId())
                ->addFieldToFilter('level', array('gt' => 1))
                ->addFieldToFilter('path', array('like'=> "1/$rootId/%"))
                ->addIsActiveFilter()
                ->addNameToResult();

            foreach ($collection as $category) {
                    $category_paths[$category->getId()] = array();
                    $path_ids = $category->getPathIds();
                    foreach ($path_ids as $id) {
                        if ($item = $collection->getItemById($id)) {
                            $category_paths[$category->getId()][] = $item->getName();
                        }
                    }
            }

            $this->setData('category_paths', $category_paths);
        }

        return $category_paths;
    }

    /**
     * Return a list of the names of all the categories in the
     * paths of the given categories (including the given categories)
     * up to, but not including the store root.
     *
     * @param array $categories
     *
     * @return array
     */
    protected function getCategoryNames(array $categories) {
        $category_paths = $this->getCategoryPaths();

        $result = array("KLEVU_PRODUCT");
        foreach ($categories as $category) {
            if (isset($category_paths[$category])) {
                $result = array_merge($result, $category_paths[$category]);
            }
        }

        return array_unique($result);
    }

    /**
     * Given a list of category IDs, return the name of the category
     * in that list that has the longest path.
     *
     * @param array $categories
     *
     * @return string
     */
    protected function getLongestPathCategoryName(array $categories) {
        $category_paths = $this->getCategoryPaths();

        $length = 0;
        $name = "";
        foreach ($categories as $id) {
            if (isset($category_paths[$id])) {
                //if (count($category_paths[$id]) > $length) {
                    //$length = count($category_paths[$id]);
                    $name .= end($category_paths[$id]).";";
                //}
            }
        }
        return substr($name,0,strrpos($name,";")+1-1);
    }
    
    /**
     * Get the list of prices based on customer group
     *
     * @param object $item OR $parent
     *
     * @return array
     */
    protected function getGroupPrices($proData) {

        $groupPrices = $proData->getData('tier_price');

            if (is_null($groupPrices)) {

                $attribute = $proData->getResource()->getAttribute('tier_price');
                if ($attribute){
                    $attribute->getBackend()->afterLoad($proData);
                    $groupPrices = $proData->getData('tier_price');
                    
                }
            }

            if (!empty($groupPrices) && is_array($groupPrices)) {
                foreach ($groupPrices as $groupPrice) {
                    if($this->_storeModelStoreManagerInterface->getStore()->getWebsiteId()== $groupPrice['website_id'] || $groupPrice['website_id']==0) {
                        if($groupPrice['price_qty'] == 1) {
                            $groupPriceKey = $groupPrice['cust_group'];
                            $groupname = $this->_customerModelGroup->load($groupPrice['cust_group'])->getCustomerGroupCode();
                            $result['label'] =  $groupname;
                            $result['values'] =  $groupPrice['website_price'];
                            $priceGroupData[$groupPriceKey]= $result;
                        }
                    }
                }
                return $priceGroupData;
            }
    }

    /**
     * Returns either array containing the label and value(s) of an attribute, or just the given value
     *
     * In the case that there are multiple options selected, all values are returned
     *
     * @param string $code
     * @param null   $value
     *
     * @return array|string
     */
    protected function getAttributeData($code, $value = null) {
        if(!empty($value)) {
            if (!$attribute_data = $this->getData('attribute_data')) {

                $attribute_data = array();

                $collection = $this->_productAttributeCollection
                    ->addFieldToFilter('attribute_code', array('in' => $this->getUsedMagentoAttributes()));
      
                foreach ($collection as $attr) {
                    $attr->setStoreId($this->_storeModelStoreManagerInterface->getStore()->getId());
                    $attribute_data[$attr->getAttributeCode()] = array(
                        'label' => $attr->getStoreLabel($this->_storeModelStoreManagerInterface->getStore()->getId()),
                        'values' => ''
                    );

                    if ($attr->usesSource()) {
                                       
						//$attribute_data[$attr->getAttributeCode()] = array();
                        foreach($attr->setStoreId($this->_storeModelStoreManagerInterface->getStore()->getId())->getSource()->getAllOptions(false) as $option) {
                            if (is_array($option['value'])) {
                                foreach ($option['value'] as $sub_option) {
                                    if(count($sub_option) > 0) {
                                        $attribute_data[$attr->getAttributeCode()]['values'][$sub_option['value']] = $sub_option['label'];
                                    }
                                }
                            } else {
                                $attribute_data[$attr->getAttributeCode()]['values'][$option['value']] = $option['label'];
                            }
                        }
                       
                    }
                }

                $this->setData('attribute_data', $attribute_data);
            }
            // make sure the attribute exists
            if (isset($attribute_data[$code])) {

                // was $value passed a parameter?
                if (!is_null($value)) {

                    // If not values are set on attribute_data for the attribute, return just the value passed. (attributes like: name, description etc)
                    if(empty($attribute_data[$code]['values'])) {
                        return $value;
                    }
                    
                    // break up our value into an array by a comma, this is for catching multiple select attributes.
                    $values = explode(",", $value);
                                                   
                    // loop over our array of attribute values
                    foreach ($values as $key => $valueOption) {

                        // if there is a value on the attribute_data use that value (it will be the label for a dropdown select attribute)
                        if (isset($attribute_data[$code]['values'][$valueOption])) {
                            $values[$key] = $attribute_data[$code]['values'][$valueOption];
                        } else { // If no label was found, log an error and unset the value.
                            $this->_searchHelperData->log(\Zend\Log\Logger::WARN, sprintf("Attribute: %s option label was not found, option ID provided: %s", $code, $valueOption));
                            unset($values[$key]);
                        }
                    }

                    // If there was only one value in the array, return the first (select menu, single option), or if there was more, return them all (multi-select).
                    if (count($values) == 1) {
                        $attribute_data[$code]['values'] = $values[0];
                    } else {
                        $attribute_data[$code]['values'] =  $values;
                    }

                }
                return $attribute_data[$code];
            }


            $result['label'] = $code;
            $result['values'] = $value;
            return $result;
        }
    }

    /**
     * Convert the given price into the current store currency.
     *
     * @param $price
     *
     * @return float
     */
    protected function convertPrice($price,$store) {
        $convertPrice = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\PriceCurrencyInterface');
        return $convertPrice->convert($price,false);
    }

    /**
     * Process the given product price for using in Product Sync.
     * Applies tax, if needed, and converts to the currency of the current store.
     *
     * @param $price
     * @param $tax_class_id
     * @param product object
     *
     * @return float
     */
    protected function processPrice($price,$pro) {
		if($price < 0){$price = 0;}else{$price = $price;}
        $config = $this->_searchHelperConfig;
        if($config->isTaxEnabled($this->_storeModelStoreManagerInterface->getStore()->getId())) {
           $taxPrice = $this->_taxHelperData->getTaxPrice($pro, $price, true, null, null, null, $this->_storeModelStoreManagerInterface->getStore()->getId(),false);
           return $taxPrice;
        } else {
            return $price;
        }
    }

    /**
     * Check if the Test Mode is enabled for the current store.
     *
     * @return int 1 if Test Mode is enabled, 0 otherwise.
     */
    protected function isTestModeEnabled() {
        if (!$this->hasData("test_mode_enabled")) {
            $test_mode = 0;
            $test_mode = ($test_mode) ? 1 : 0;
            $this->setData("test_mode_enabled", $test_mode);
        }

        return $this->getData("test_mode_enabled");
    }


    /**
     * Remove any session specific data.
     *
     * @return $this
     */
    protected function reset() {
        $this->unsetData('session_id');
        $this->unsetData('store');
        $this->unsetData('attribute_map');
        $this->unsetData('placeholder_image');
        $this->unsetData('category_paths');
        $this->unsetData('attribute_data');
        return $this;
    }
      
    /**
     * Generate batch for thumbnail image
     * @param $image
     * @return $this
     */ 
	 
    /**
     * Generate thumbnail image for each product
	 *
     * @param string $image
     * 
     * @return $this
     */ 	 
        
    public function thumbImage($image,$mediadir) {
            try {
                $baseImageUrl = $mediadir.DIRECTORY_SEPARATOR."catalog".DIRECTORY_SEPARATOR."product".$image;
                if(file_exists($baseImageUrl)) {
                    list($width, $height, $type, $attr)=getimagesize($baseImageUrl); 
                    if($width > 200 && $height > 200) {
                        $imageResized = $mediadir.DIRECTORY_SEPARATOR."klevu_images".$image;
                        if(!file_exists($imageResized)) {
                            $this->thumbImageObj($baseImageUrl,$imageResized);
                        }
                    }
                }
            }catch(\Exception $e) {
                 $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Image Error:\n%s", $e->getMessage()));
            }
    }
        
    /**
     * Generate 200px thumb image
	 *
     * @param string $imageUrl, string $imageResized
     * 
     * @return $this
     */  
    public function thumbImageObj($imageUrl,$imageResized)
    {

        $imageObj = $this->imageFactory->create($imageUrl);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(TRUE);
        $imageObj->keepFrame(FALSE);
        $imageObj->keepTransparency(true);
        $imageObj->backgroundColor(array(255, 255, 255));
        $imageObj->resize(200, 200);
        $imageObj->save($imageResized);
    }
    
    
    /**
     * Get ida for debugs
	 *
     * @return $this
     */    
    public function debugsIds()
    {
        $select = $this->_frameworkModelResource->getConnection("core_write")->select()
                ->from($resource->getTableName("catalog_product_entity"), array('entity_id','updated_at'))->limit(500)->order('updated_at');
        $data = $this->_frameworkModelResource->getConnection("core_write")->fetchAll($select);
        return $data;
    }
    
    /**
     * Get api for debugs
	 *
     * @return $string
     */    
    public function getApiDebug()
    {
        $configs = $this->_modelConfigData->getCollection()
                  ->addFieldToFilter('path', array("like" => "%rest_api_key%"))->load();
        $data = $configs->getData();
        return $data[0]['value'];
    }
    
    /**
     * Run cron externally for debug using js api
	 *
     * @param $js_api
	 *
     * @return $this
     */    
    public function sheduleCronExteranally($rest_api) {
        $configs = $this->_modelConfigData->getCollection()
                ->addFieldToFilter('value', array("like" => "%$rest_api%"))->load();
        $data = $configs->getData();
        if(!empty($data[0]['scope_id'])){
            $store = $this->_storeModelStoreManagerInterface->getStore($data[0]['scope_id']);
            $this->_modelProductSync
            ->markAllProductsForUpdate($store)
            ->schedule();
        }
    }
    
    
    /**
     * Delete test mode data from product sync
	 *
     * @return $this
     */ 
    public function deleteTestmodeData($store) {
        $condition = array("store_id"=> $store->getId());
        $this->_frameworkModelResource->getConnection("core_write")->delete($resource->getTableName("klevu_product_sync"),$condition);    
    }
    
    /**
     * Get special price expire date attribute value
	 *
     * @return array
     */ 
    public function getExpiryDateAttributeId() {
        $resource = $this->_frameworkModelResource;
        $query = $resource->getConnection("core_write")->select()
                    ->from($resource->getTableName("eav_attribute"), array('attribute_id'))
                    ->where('attribute_code=?','special_to_date');
        $data = $query->query()->fetchAll();
        return $data[0]['attribute_id'];
    }
    
    /**
     * Get prodcuts ids which have expiry date gone and update next day
	 *
     * @return array
     */ 
    public function getExpirySaleProductsIds() {
        $attribute_id = $this->getExpiryDateAttributeId();
        $current_date = date_create("now")->format("Y-m-d");
        $resource = $this->_frameworkModelResource;
        $query = $resource->getConnection("core_write")->select()
                    ->from($resource->getTableName("catalog_product_entity_datetime"), array($this->_entity_value))
                    ->where("attribute_id=:attribute_id AND DATE_ADD(value,INTERVAL 1 DAY)=:current_date")
                    ->bind(array(
                            'attribute_id' => $attribute_id,
                            'current_date' => $current_date
                    ));
        $data = $this->_frameworkModelResource->getConnection("core_write")->fetchAll($query, $query->getBind());
        $pro_ids = array();
        foreach($data as $key => $value)
        {
            $pro_ids[] = $value[$this->_entity_value];
        }
        return $pro_ids;
       
    }
   
    
    /**
     * if special to price date expire then make that product for update
	 *
     * @return $this
     */ 
    public function markProductForUpdate(){
        try {
            $special_pro_ids = $this->getExpirySaleProductsIds();
            if(!empty($special_pro_ids)) {
                $this->updateSpecificProductIds($special_pro_ids);
            }
            
        } catch(\Exception $e) {
                $this->_searchHelperData->log(\Zend\Log\Logger::CRIT, sprintf("Exception thrown in markforupdate %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage()));
        }
    }
    
    /**
     * Mark product ids for update
     *
     * @param array ids
     *
     * @return 
     */ 
    public function updateSpecificProductIds($ids){
        $pro_ids = implode(',', $ids);
        $resource = $this->_frameworkModelResource;
        $where = sprintf("(product_id IN(%s) OR parent_id IN(%s)) AND %s", $pro_ids,$pro_ids,$resource->getConnection('core_write')->quoteInto('type = ?',"products"));
        $resource->getConnection('core_write')->update(
        $resource->getTableName('klevu_product_sync'),
                array('last_synced_at' => '0'),
                $where
                );
   }
   
    /**
     * Update all product ids rating attribute
     *
     * @param string store
     *
     * @return  $this
     */ 
    public function updateProductsRating($store){
        $entity_type = $this->_modelEntityType->loadByCode("catalog_product");
        $entity_typeid = $entity_type->getId();
        $attributecollection = $this->_modelEntityAttribute->getCollection()->addFieldToFilter("entity_type_id", $entity_typeid)->addFieldToFilter("attribute_code", "rating");

        if(count($attributecollection) > 0) {
            $sumColumn = "AVG(rating_vote.{$this->_frameworkModelResource->getConnection("core_write")->quoteIdentifier('percent')})";
            $select = $this->_frameworkModelResource->getConnection("core_write")->select()
                ->from(array('rating_vote' => $this->_frameworkModelResource->getTableName('rating_option_vote')),
                    array(
                        'entity_pk_value' => 'rating_vote.entity_pk_value',
                        'sum'             => $sumColumn,
                        ))
                ->join(array('review' => $this->_frameworkModelResource->getTableName('review')),
                    'rating_vote.review_id=review.review_id',
                    array())
                ->joinLeft(array('review_store' => $this->_frameworkModelResource->getTableName('review_store')),
                    'rating_vote.review_id=review_store.review_id',
                    array('review_store.store_id'))
                ->join(array('rating_store' => $this->_frameworkModelResource->getTableName('rating_store')),
                    'rating_store.rating_id = rating_vote.rating_id AND rating_store.store_id = review_store.store_id',
                    array())
                ->join(array('review_status' => $this->_frameworkModelResource->getTableName('review_status')),
                    'review.status_id = review_status.status_id',
                    array())
                ->where('review_status.status_code = :status_code AND rating_store.store_id = :storeId')
                ->group('rating_vote.entity_pk_value')
                ->group('review_store.store_id');
            $bind = array('status_code' => "Approved",'storeId' => $store->getId());
            $data_ratings = $this->_frameworkModelResource->getConnection("core_write")->fetchAll($select,$bind);
            $allStores = $this->_storeModelStoreManagerInterface->getStores();
            foreach($data_ratings as $key => $value)
            {
                if(count($allStores) > 1) {
                    $this->_modelProductAction->updateAttributes(array($value['entity_pk_value']), array('rating'=>0),0);
                }
                $this->_modelProductAction->updateAttributes(array($value['entity_pk_value']), array('rating'=>$value['sum']), $store->getId());
                $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Rating is updated for product id %s",$value['entity_pk_value']));
            }
        }
   }
   
    /**
     * Convert percent to rating star
     *
     * @param int percentage
     *
     * @return float
     */
    public function convertToRatingStar($percentage) {
        if(!empty($percentage) && $percentage!=0) {
            $start = $percentage * 5;
            return round($start/100, 2);
        } else {
            return;
        }
    }
    

    /**
     * Apply catalog price rules to product on frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
	 *
     * @return $this
     */
    public function calculateFinalPriceFront($item, $gId, $pId, $store)
    {
        $date = $this->localeDate->scopeDate($store->getId());
        $wId =  $store->getWebsiteId();
        $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}";
        if (!$this->rulePricesStorage->hasRulePrice($key)) {
            $rulePrice = $this->resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId);
            $this->rulePricesStorage->setRulePrice($key, $rulePrice);
            return $rulePrice;
        }
        return;
    }
    
    /**
     * Mark products for update if rule is expire
	 *
     * @return void
     */
    public function catalogruleUpdateinfo(){

        $timestamp_after = strtotime("+1 day",strtotime(date_create("now")->format("Y-m-d")));
        $timestamp_before = strtotime("-1 day",strtotime(date_create("now")->format("Y-m-d")));
        $query = $this->_frameworkModelResource->getConnection()->select()
                    ->from( $this->_frameworkModelResource->getTableName("catalogrule_product"), array('product_id'))
                    ->where("customer_group_id=:customer_group_id AND ((from_time BETWEEN :timestamp_before AND :timestamp_after) OR (to_time BETWEEN :timestamp_before AND :timestamp_after))")
                    ->bind(array(
                            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                            'timestamp_before' => $timestamp_before,
                            'timestamp_after' => $timestamp_after
                    ));

        $data = $this->_frameworkModelResource->getConnection()->fetchAll($query, $query->getBind());
        $pro_ids = array();
		
        foreach($data as $key => $value)
        {
            $pro_ids[] = $value['product_id'];
        }
        if(!empty($pro_ids)) {
            $this->updateSpecificProductIds($pro_ids);
        }
    }
    
    /**
     * Perform Category Sync on any configured stores, adding new categories, updating modified and
     * deleting removed category since last sync.
	 *
	 * @param \Magento\Store\Model\Store|null $store
     */
    public function runCategory($store)
    {
            $isActiveAttributeId =  $this->_searchHelperData->getIsActiveAttributeId();
            $this->log(\Zend\Log\Logger::INFO, sprintf("Starting sync for category %s (%s).", $store->getWebsite()->getName() , $store->getName()));
            $rootId = $this->getStore()->getRootCategoryId();
            $rootStoreCategory = "1/$rootId/";
			
			if($this->_ProductMetadataInterface->getEdition() == "Enterprise" && version_compare($this->_ProductMetadataInterface->getVersion(), '2.0.8', '>')===true) {
				$actions = array(
                    'delete' => $this->_frameworkModelResource->getConnection()
                        ->select()
                        /*
                         * Select synced categories in the current store/mode that 
                         * are no longer enabled
                         */
                        ->from(
                                    array('ce' => $this->getTableName("catalog_category_entity")),
                                    array('category_id' => "ce.entity_id")
                                   
                        )
						->join(
                                    array('k' => $this->getTableName("klevu_product_sync")),
                                    "k.product_id = ce.entity_id AND k.type = :type AND store_id=:store_id AND k.parent_id=0",
                                    ""
                                )
                        ->joinLeft(
                                    array('ci' => $this->_frameworkModelResource->getTableName("catalog_category_entity_int")),
                                    "ci.row_id = ce.row_id AND ci.attribute_id = :is_active AND ci.store_id = 0",
                                    ""
                                )
						->joinLeft(
                                    array('cs' => $this->_frameworkModelResource->getTableName("catalog_category_entity_int")),
                                    "cs.row_id = ci.row_id AND cs.attribute_id = :is_active AND cs.store_id = :store_id",
                                    ""
                                )
                        ->where("(CASE WHEN cs.value_id > 0 THEN cs.value ELSE ci.value END = 0 OR k.product_id NOT IN ?)",
                                $this->_frameworkModelResource->getConnection()
                                ->select()
                                ->from(
                                    array('i' => $this->getTableName("catalog_category_entity")),
                                    array('category_id' => "i.entity_id")
                                )
                        )
                        ->group(array('k.product_id', 'k.parent_id'))
                        ->bind(array(
                            'type'=>"categories",
                            'is_active' => $isActiveAttributeId,
							'store_id' => $store->getId()
                        )),
                    'update' => 
                            $this->_frameworkModelResource->getConnection()
                                ->select()
                                /*
                                 * Select categories for the current store/mode
                                 * have been updated since last sync.
                                 */
                                 ->from(
                                    array('k' => $this->getTableName("klevu_product_sync")),
                                    array('category_id' => "k.product_id")
                                   
                                )
                                ->join(
                                    array('ce' => $this->getTableName("catalog_category_entity")),
                                    "k.product_id = ce.entity_id",
                                    ""
                                )
                                ->where("(k.type = :type) AND k.test_mode = :test_mode AND (k.store_id = :store_id) AND (ce.updated_at > k.last_synced_at)")
                                ->bind(array(
                                    'store_id' => $store->getId(),
                                    'type'=> "categories",
                                    'test_mode' => $this->isTestModeEnabled(),
                                )),
                    'add' =>  $this->_frameworkModelResource->getConnection()
                                ->select()
                                /*
                                 * Select categories for the current store/mode
                                 * have been updated since last sync.
                                 */
                                ->from(
                                    array('c' => $this->_frameworkModelResource->getTableName("catalog_category_entity")),
                                    array('category_id' => "c.entity_id")
                                )
								->joinLeft(
                                    array('ci' => $this->_frameworkModelResource->getTableName("catalog_category_entity_int")),
                                    "ci.row_id = c.row_id AND ci.attribute_id = :is_active AND ci.store_id = 0",
                                    ""
                                )
								->joinLeft(
                                    array('cs' => $this->_frameworkModelResource->getTableName("catalog_category_entity_int")),
                                    "cs.row_id = c.row_id AND cs.attribute_id = :is_active AND cs.store_id = :store_id",
                                    ""
                                )
                                ->joinLeft(
                                    array('k' => $this->_frameworkModelResource->getTableName("klevu_product_sync")),
                                    "k.product_id = c.entity_id AND k.test_mode = :test_mode AND k.type = :type AND k.store_id = :store_id AND k.parent_id=0",
                                    ""
                                )
								->where("CASE WHEN cs.value_id > 0 THEN cs.value ELSE ci.value END = 1")
                                ->where("k.product_id IS NULL")
                                ->where("c.path LIKE ?","{$rootStoreCategory}%")
                        ->bind(array(
                            'type' => "categories",
                            'store_id' => $store->getId(),
                            'is_active' => $isActiveAttributeId,
                            'test_mode' => $this->isTestModeEnabled(),
                        )),
                );
			} else {
			
				$actions = array(
                    'delete' => $this->_frameworkModelResource->getConnection()
                        ->select()
                        /*
                         * Select synced categories in the current store/mode that 
                         * are no longer enabled
                         */
                        ->from(
                                    array('ce' => $this->getTableName("catalog_category_entity")),
                                    array('category_id' => "ce.entity_id")
                                   
                        )
						->join(
                                    array('k' => $this->getTableName("klevu_product_sync")),
                                    "k.product_id = ce.entity_id AND k.type = :type AND store_id=:store_id AND k.parent_id=0",
                                    ""
                                )
                        ->joinLeft(
                                    array('ci' => $this->_frameworkModelResource->getTableName("catalog_category_entity_int")),
                                    "ci.entity_id = ce.entity_id AND ci.attribute_id = :is_active AND ci.store_id = 0",
                                    ""
                                )
						->joinLeft(
                                    array('cs' => $this->_frameworkModelResource->getTableName("catalog_category_entity_int")),
                                    "cs.entity_id = ci.entity_id AND cs.attribute_id = :is_active AND cs.store_id = :store_id",
                                    ""
                                )
                        ->where("(CASE WHEN cs.value_id > 0 THEN cs.value ELSE ci.value END = 0 OR k.product_id NOT IN ?)",
                                $this->_frameworkModelResource->getConnection()
                                ->select()
                                ->from(
                                    array('i' => $this->getTableName("catalog_category_entity")),
                                    array('category_id' => "i.entity_id")
                                )
                        )
                        ->group(array('k.product_id', 'k.parent_id'))
                        ->bind(array(
                            'type'=>"categories",
                            'is_active' => $isActiveAttributeId,
							'store_id' => $store->getId()
                        )),
                    'update' => 
                            $this->_frameworkModelResource->getConnection()
                                ->select()
                                /*
                                 * Select categories for the current store/mode
                                 * have been updated since last sync.
                                 */
                                 ->from(
                                    array('k' => $this->getTableName("klevu_product_sync")),
                                    array('category_id' => "k.product_id")
                                   
                                )
                                ->join(
                                    array('ce' => $this->getTableName("catalog_category_entity")),
                                    "k.product_id = ce.entity_id",
                                    ""
                                )
                                ->where("(k.type = :type) AND k.test_mode = :test_mode AND (k.store_id = :store_id) AND (ce.updated_at > k.last_synced_at)")
                                ->bind(array(
                                    'store_id' => $store->getId(),
                                    'type'=> "categories",
                                    'test_mode' => $this->isTestModeEnabled(),
                                )),
                     'add' =>  $this->_frameworkModelResource->getConnection()
                                ->select()
                                /*
                                 * Select categories for the current store/mode
                                 * have been updated since last sync.
                                 */
                                ->from(
                                    array('c' => $this->_frameworkModelResource->getTableName("catalog_category_entity")),
                                    array('category_id' => "c.entity_id")
                                )
								->joinLeft(
                                    array('ci' => $this->_frameworkModelResource->getTableName("catalog_category_entity_int")),
                                    "ci.entity_id = c.entity_id AND ci.attribute_id = :is_active AND ci.store_id = 0",
                                    ""
                                )
								->joinLeft(
                                    array('cs' => $this->_frameworkModelResource->getTableName("catalog_category_entity_int")),
                                    "cs.entity_id = c.entity_id AND cs.attribute_id = :is_active AND cs.store_id = :store_id",
                                    ""
                                )
                                ->joinLeft(
                                    array('k' => $this->_frameworkModelResource->getTableName("klevu_product_sync")),
                                    "k.product_id = c.entity_id AND k.test_mode = :test_mode AND k.type = :type AND k.store_id = :store_id AND k.parent_id=0",
                                    ""
                                )
								->where("CASE WHEN cs.value_id > 0 THEN cs.value ELSE ci.value END = 1")
                                ->where("k.product_id IS NULL")
                                ->where("c.path LIKE ?","{$rootStoreCategory}%")
                        ->bind(array(
                            'type' => "categories",
                            'store_id' => $store->getId(),
                            'is_active' => $isActiveAttributeId,
                            'test_mode' => $this->isTestModeEnabled(),
                        )),
                );
			}
            $errors = 0;
            foreach($actions as $action => $statement) {
				
                if ($this->rescheduleIfOutOfMemory()) {
                    return;
                }
                $method = $action . "Category";
                $category_pages = $this->_frameworkModelResource->getConnection()->fetchAll($statement, $statement->getBind());
                $total = count($category_pages);
                $this->log(\Zend\Log\Logger::INFO, sprintf("Found %d category Pages to %s.", $total, $action));
                $pages = ceil($total / static ::RECORDS_PER_PAGE);
                for ($page = 1; $page <= $pages; $page++) {
                    if ($this->rescheduleIfOutOfMemory()) {
                        return;
                    }
                    $offset = ($page - 1) * static ::RECORDS_PER_PAGE;
                    $result = $this->$method(array_slice($category_pages, $offset, static ::RECORDS_PER_PAGE));
                    if ($result !== true) {
                        $errors++;
                        $this->log(\Zend\Log\Logger::ERR, sprintf("Errors occurred while attempting to %s categories pages %d - %d: %s", $action, $offset + 1, ($offset + static ::RECORDS_PER_PAGE <= $total) ? $offset + static ::RECORDS_PER_PAGE : $total, $result));
                    }
                }
            }
            $this->log(\Zend\Log\Logger::INFO, sprintf("Finished category page sync for %s (%s).", $store->getWebsite()->getName() , $store->getName()));
    }
    /**
     * Add the given Categories to Klevu Search. Returns true if the operation was successful,
     * or the error message if it failed.
     *
     * @param array $data List of Categories to add. Each element should be an array
     *                    containing an element with "category_id" as the key and category id as
     *                    the value.
     *
     * @return bool|string
     */
    protected function addCategory(array $data)
    {
        $total = count($data);
        $data = $this->addcategoryData($data);
        $response = $this->_apiActionAddrecords->setStore($this->getStore())->execute(array(
            'sessionId' => $this->getSessionId() ,
            'records' => $data
        ));
        if ($response->isSuccess()) {
            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }
            $sync_time = $this->_searchHelperCompat->now();
            foreach($data as $i => & $record) {
                if (isset($skipped_record_ids[$i])) {
                    unset($data[$i]);
                    continue;
                }
                $ids[$i] = explode("_", $data[$i]['id']);
                $record = array(
                    $ids[$i][1],
                    0,
                    $this->getStore()->getId() ,
                    $this->isTestModeEnabled() ,
                    $sync_time,
                    "categories"
                );
            }

			if(!empty($data)) {
				foreach($data as $key => $value){
					$write =  $this->_frameworkModelResource->getConnection("core_write");
					$query = "replace into ".$this->_frameworkModelResource->getTableName('klevu_product_sync')
						   . "(product_id, parent_id, store_id, test_mode, last_synced_at, type) values "
						   . "(:product_id, :parent_id, :store_id, :test_mode, :last_synced_at, :type)";

					$binds = array(
						'product_id' => $value[0],
						'parent_id' => $value[1],
						'store_id' => $value[2],
						'test_mode' => $value[3],
						'last_synced_at'  => $value[4],
						'type' => $value[5]
					);
					$write->query($query, $binds);
				}
			}
			
            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d category%s failed (%s)", $skipped_count, ($skipped_count > 1) ? "s" : "", implode(", ", $skipped_records["messages"]));
            }
            else {
                return true;
            }
        }
        else {
            return sprintf("%d category%s failed (%s)", $total, ($total > 1) ? "s" : "", $response->getMessage());
        }
    }
    /**
     * Add the Category Sync data to each Category in the given list. Updates the given
     * list directly to save memory.
     *
     * @param array $categories An array of categories. Each element should be an array with
     *                        containing an element with "id" as the key and the Category
     *                        ID as the value.
     *
     * @return $this
     */
    protected function addcategoryData(&$pages)
    {
        $category_ids = array();
        foreach($pages as $key => $value) {
            $category_ids[] = $value["category_id"];
        }
        $category_data = $this->_catalogModelCategory->getCollection()
		->setStore($this->_storeModelStoreManagerInterface->getStore())
		->addAttributeToSelect("*")->addFieldToFilter('entity_id', array(
            'in' => $category_ids
        ));
		$config = $this->_searchHelperConfig;
		$category_url_rewrite_data = $this->getCategoryUrlRewriteData($category_ids);
		if($config->isSecureUrlEnabled($this->_storeModelStoreManagerInterface->getStore()->getId())) {
            $base_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK,true);
            $media_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA,true);
          
       }else {
            $base_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
            $media_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        }
		
		
		$category_data_new = array();
        foreach($category_data as $category) {
			$category['url'] = $base_url . (
						(isset($category_url_rewrite_data[$category->getId()])) ?
							$category_url_rewrite_data[$category->getId()] :
							"catalog/category/view/id/" . $category->getId()
					);
            $value["id"] = "categoryid_" . $category->getId();
            $value["name"] = $category->getName();
            $value["desc"] = strip_tags($category->getDescription());
            $value["url"] = $category['url'];
            $value["metaDesc"] = $category->getMetaDescription() . $category->getMetaKeywords();
            $value["shortDesc"] = substr(strip_tags($category->getDescription()) , 0, 200);
            $value["listCategory"] = "KLEVU_CATEGORY";
            $value["category"] = "Categories";
            $value["salePrice"] = 0;
            $value["currency"] = "USD";
            $value["inStock"] = "yes";
            $category_data_new[] = $value;
        }
        return $category_data_new;
    }
    /**
     * Update the given categories on Klevu Search. Returns true if the operation was successful,
     * or the error message if it failed.
     *
     * @param array $data List of categories to update. Each element should be an array
     *                    containing an element with "category_id" as the key and category id as
     *                    the value
     *
     * @return bool|string
     */
    protected function updateCategory(array $data)
    {
        $total = count($data);
        $data = $this->addcategoryData($data);
        $response = $this->_apiActionUpdaterecords->setStore($this->getStore())->execute(array(
            'sessionId' => $this->getSessionId() ,
            'records' => $data
        ));
        if ($response->isSuccess()) {
            $helper = $this->_searchHelperData;
            $connection = $this->getConnection();
            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }
            $where = array();
            for ($i = 0; $i < count($data); $i++) {
                if (isset($skipped_record_ids[$i])) {
                    continue;
                }
                $ids[$i] = explode("_", $data[$i]['id']);
                $where[] = sprintf("(%s AND %s AND %s)", $this->_frameworkModelResource->getConnection()->quoteInto("product_id = ?", $ids[$i][1]) , $this->_frameworkModelResource->getConnection()->quoteInto("parent_id = ?", 0) , $this->_frameworkModelResource->getConnection()->quoteInto("type = ?", "categories"));
            }
            $where = sprintf("(%s) AND (%s) AND (%s)", $this->_frameworkModelResource->getConnection()->quoteInto("store_id = ?", $this->getStore()->getId()) , $this->_frameworkModelResource->getConnection()->quoteInto("test_mode = ?", $this->isTestModeEnabled()) , implode(" OR ", $where));
            $this->_frameworkModelResource->getConnection()->update($this->_frameworkModelResource->getTableName('klevu_product_sync') , array(
                'last_synced_at' => $this->_searchHelperCompat->now()
            ) , $where);
            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d category%s failed (%s)", $skipped_count, ($skipped_count > 1) ? "s" : "", implode(", ", $skipped_records["messages"]));
            }
            else {
                return true;
            }
        }
        else {
            return sprintf("%d category%s failed (%s)", $total, ($total > 1) ? "s" : "", $response->getMessage());
        }
    }
    /**
     * Delete the given categories from Klevu Search. Returns true if the operation was
     * successful, or the error message if the operation failed.
     *
     * @param array $data List of categories to delete. Each element should be an array
     *                    containing an element with "category_id" as the key and category id as
     *                    the value.
     *
     * @return bool|string
     */
    protected function deleteCategory(array $data)
    {
        $total = count($data);
        $response = $this->_apiActionDeleterecords->setStore($this->getStore())->execute(array(
            'sessionId' => $this->getSessionId() ,
            'records' => array_map(function ($v)
            {
                return array(
                    'id' => "categoryid_" . $v['category_id']
                );
            }
            , $data)
        ));
        if ($response->isSuccess()) {
            $connection = $this->_frameworkModelResource->getConnection();
            $select = $connection->select()->from(array(
                'k' => $this->_frameworkModelResource->getTableName("klevu_product_sync")
            ))->where("k.store_id = ?", $this->getStore()->getId())->where("k.type = ?", "categories")->where("k.test_mode = ?", $this->isTestModeEnabled());
            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }
            $or_where = array();
            for ($i = 0; $i < count($data); $i++) {
                if (isset($skipped_record_ids[$i])) {
                    continue;
                }
                $or_where[] = sprintf("(%s)", $connection->quoteInto("k.product_id = ?", $data[$i]['category_id']));
            }
            $select->where(implode(" OR ", $or_where));
            $connection->query($select->deleteFromSelect("k"));
            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d category%s failed (%s)", $skipped_count, ($skipped_count > 1) ? "s" : "", implode(", ", $skipped_records["messages"]));
            }
            else {
                return true;
            }
        }
        else {
            return sprintf("%d category%s failed (%s)", $total, ($total > 1) ? "s" : "", $response->getMessage());
        }
    }
    
    /**
     * Get curernt store features based on klevu search account
	 *
     * @return string
     */
    public function getFeatures()
    {    
        if (strlen($code = $this->_frameworkAppRequestInterface->getParam('store'))) { // store level
            $code = $this->_frameworkAppRequestInterface->getParam('store');
            if (!$this->_klevu_features_response) {
                $store = $this->_frameworkModelStore->load($code);
                $store_id = $store->getId();
                $restapi = $this->_searchHelperConfig->getRestApiKey($store_id);
                $param =  array("restApiKey" => $restapi);
                if(!empty($restapi)) {
                    $this->_klevu_features_response = $this->executeFeatures($restapi,$store);
                } else {
                    return;
                }
            }
            return $this->_klevu_features_response;
        }

    }
	
    /**
     * Get the features from config value if not get any response from api
	 *
	 * @param sting $restApi , int $store
	 *
     * @return string
     */
    public function  executeFeatures($restApi,$store) {
        if(!$this->_klevu_enabled_feature_response) {
            $param =  array("restApiKey" => $restApi,"store" => $store->getId());
            $features_request = $this->_apiActionFeatures->execute($param);

            if($features_request->isSuccess()) {
                $this->_klevu_enabled_feature_response = $features_request->getData();
                $this->_searchHelperConfig->saveUpgradeFetaures(serialize($this->_klevu_enabled_feature_response),$store);
            } else {

                if(!empty($restApi)) {
                    $this->_klevu_enabled_feature_response = unserialize($this->_searchHelperConfig->getUpgradeFetaures($store));
        
                }
                $this->_searchHelperData->log(\Zend\Log\Logger::INFO,sprintf("failed to fetch feature details (%s)",$features_request->getMessage()));
            }
        }  

        return $this->_klevu_enabled_feature_response;        
    }
    
    
    /**
     * Get the klevu cron entry which is running mode
	 *
     * @return int
     */
    public function getKlevuCronStatus(){
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Cron\Model\ResourceModel\Schedule\Collection')
        ->addFieldToFilter("job_code", $this->getJobCode())
        ->addFieldToFilter("status", \Magento\Cron\Model\Schedule::STATUS_RUNNING);
        if($collection->getSize()){
            $data = $collection->getData();
            $url_builder = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\UrlInterface');
            $url = $url_builder->getUrl("klevu_search/sync/clearcron");
            return \Magento\Cron\Model\Schedule::STATUS_RUNNING." Since ".$data[0]['executed_at']." <a href='".$url."'>Clear Klevu Cron</a>";
        } else {
            $collection = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Cron\Model\ResourceModel\Schedule\Collection')
            ->addFieldToFilter("job_code", $this->getJobCode())
            ->addFieldToFilter("status",\Magento\Cron\Model\Schedule::STATUS_SUCCESS)
            ->setOrder('finished_at', 'desc');
            if($collection->getSize()){
                $data = $collection->getData();
                return \Magento\Cron\Model\Schedule::STATUS_SUCCESS." ".$data[0]["finished_at"];
            }
        }
        return;
    }
    
    /**
     * Remove the cron which is in running state
	 *
     * @return void
	 *
     */
    public function clearKlevuCron(){
        $condition = array();
        $condition[] = $this->_frameworkModelResource->getConnection()->quoteInto('status = ?', \Magento\Cron\Model\Schedule::STATUS_RUNNING);   
        $condition[] = $this->_frameworkModelResource->getConnection()->quoteInto('job_code = ?',$this->getJobCode());
        $this->_frameworkModelResource->getConnection()->delete($this->_frameworkModelResource->getTableName("cron_schedule"),$condition);   
    }
	
	/**
     * Return the URL rewrite data for the given products for the current store.
     *
     * @param array $product_ids A list of product IDs.
     *
     * @return array A list with product IDs as keys and request paths as values.
     */
    protected function getCategoryUrlRewriteData($category_ids) {
        $stmt = $this->_frameworkModelResource->getConnection("core_write")->query(
            $this->_searchHelperCompat->getCategoryUrlRewriteSelect($category_ids,$this->_storeModelStoreManagerInterface->getStore()->getId())
        );

        $data = array();
        
        while ($row = $stmt->fetch()) {
            if (!isset($data[$row['entity_id']])) {
                $data[$row['entity_id']] = $row['request_path'];
            }
        }

        return $data;
    }
    

}