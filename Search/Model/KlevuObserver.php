<?php

/**
 * Class \Klevu\Search\Model\Observer
 *
 * @method setIsProductSyncScheduled($flag)
 * @method bool getIsProductSyncScheduled()
 */
namespace Klevu\Search\Model;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout\Interceptor;


class KlevuObserver implements ObserverInterface{
    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Model\Order\Sync
     */
    protected $_modelOrderSync;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_frameworkModelResource;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_magentoFrameworkFilesystem;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Magento\Rating\Model\Rating
     */
    protected $_ratingModelRating;

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

    public function __construct(\Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Model\Order\Sync $modelOrderSync, 
        \Magento\Config\Model\ResourceModel\Config $frameworkModelResource, 
        \Magento\Framework\Filesystem $magentoFrameworkFilesystem, 
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Magento\Review\Model\Rating $ratingModelRating, 
        \Magento\Eav\Model\Entity\Type $modelEntityType, 
        \Magento\Eav\Model\Entity\Attribute $modelEntityAttribute, 
        \Magento\Catalog\Model\Product\Action $modelProductAction,
        \Magento\Framework\View\Layout\Interceptor $appConfigScopeConfigInterface)
    {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_modelProductSync = $modelProductSync;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_modelOrderSync = $modelOrderSync;
        $this->_frameworkModelResource = $frameworkModelResource;
        $this->_magentoFrameworkFilesystem = $magentoFrameworkFilesystem;
        $this->_searchHelperData = $searchHelperData;
        $this->_ratingModelRating = $ratingModelRating;
        $this->_modelEntityType = $modelEntityType;
        $this->_modelEntityAttribute = $modelEntityAttribute;
        $this->_modelProductAction = $modelProductAction;

        //parent::__construct();
    }


    /**
     * Schedule a Product Sync to run immediately.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function scheduleProductSync(\Magento\Framework\Event\Observer $observer) {
        if (!$this->getIsProductSyncScheduled()) {
            $this->_modelProductSync->schedule();
            $this->setIsProductSyncScheduled(true);
        }
    }

    /**
     * Schedule an Order Sync to run immediately. If the observed event
     * contains an order, add it to the sync queue before scheduling.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function scheduleOrderSync(\Magento\Framework\Event\Observer $observer) {
        $store = $this->_storeModelStoreManagerInterface->getStore($observer->getEvent()->getStore());
        if($this->_searchHelperConfig->isOrderSyncEnabled($store->getId())) {
            $model = $this->_modelOrderSync;
            $order = $observer->getEvent()->getOrder();
            if ($order) {
                $model->addOrderToQueue($order);
            }
            $model->schedule();
        }
    }

    /**
     * When products are updated in bulk, update products so that they will be synced.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function setProductsToSync(\Magento\Framework\Event\Observer $observer) {
        $product_ids = $observer->getData('product_ids');

        if(empty($product_ids)) {
            return;
        }

        $product_ids = implode(',', $product_ids);
        $where = sprintf("product_id IN(%s) OR parent_id IN(%s)", $product_ids, $product_ids);
        $resource = $this->_frameworkModelResource;
        $resource->getConnection('core_write')
            ->update(
                $resource->getTableName('klevu_search/product_sync'),
                array('last_synced_at' => '0'),
                $where
            );
    }

    /**
     * Mark all of the products for update and then schedule a sync
     * to run immediately.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function syncAllProducts(\Magento\Framework\Event\Observer $observer) {
        $store = null;
        $sync = $this->_modelProductSync;

        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof \Magento\Catalog\Model\Resource\Eav\Attribute) {
            // On attribute change, sync only if the attribute was added
            // or removed from layered navigation
            if ($attribute->getOrigData("is_filterable_in_search") == $attribute->getData("is_filterable_in_search")) {
                return;
            }
        }

        if ($observer->getEvent()->getStore()) {
            // Only sync products for a specific store if the event was fired in that store
            $store = $this->_storeModelStoreManagerInterface->getStore($observer->getEvent()->getStore());
        }

        $sync->markAllProductsForUpdate($store);

        if (!$this->getIsProductSyncScheduled()) {
            $sync->schedule();
            $this->setIsProductSyncScheduled(true);
        }
    }
    /**
     * When product image updated from admin this will generate the image thumb.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function createThumb(\Magento\Framework\Event\Observer $observer) {

        $image = $observer->getEvent()->getProduct()->getImage();
        if(($image != "no_selection") && (!empty($image))) {
            try {
                $imageResized = $this->_magentoFrameworkFilesystem->getDirPath('media').DS."klevu_images".$image;
                $baseImageUrl = $this->_magentoFrameworkFilesystem->getDirPath('media').DS."catalog".DS."product".$image;
                if(file_exists($baseImageUrl)) {
                    list($width, $height, $type, $attr)= getimagesize($baseImageUrl); 
                    if($width > 200 && $height > 200) {
                            if(file_exists($imageResized)) {
                                if (!unlink('media/klevu_images'. $image))
                                {
                                    $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Image Deleting Error:\n%s", $image));  
                                }
                            }
                            $this->_modelProductSync->thumbImageObj($baseImageUrl,$imageResized);
                    }
                }
            } catch(Exception $e) {
                 $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Image Error:\n%s", $e->getMessage()));
            }
        }
    }
  
    /**
     * Apply model rewrites for the search landing page, if it is enabled.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function applyLandingPageModelRewrites(\Magento\Framework\Event\Observer $observer) {
        echo "hi";
        exit;
        if ($this->_searchHelperConfig->isLandingEnabled()) {

            $rewrites = array(
                "global/models/catalogsearch_resource/rewrite/fulltext_collection"         => "Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection",
                "global/models/catalogsearch_mysql4/rewrite/fulltext_collection"           => "Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection",
                "global/models/catalogsearch/rewrite/layer_filter_attribute"               => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Attribute",
                "global/models/catalog/rewrite/config"                                     => "Klevu\Search\Model\Catalog\Model\Config",
                "global/models/catalog/rewrite/layer_filter_price"                         => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Price",
                "global/models/catalog/rewrite/layer_filter_category"                      => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Category",
                "global/models/catalog_resource/rewrite/layer_filter_attribute"            => "Klevu\Search\Model\CatalogSearch\Resource\Layer\Filter\Attribute",
                "global/models/catalog_resource_eav_mysql4/rewrite/layer_filter_attribute" => "Klevu\Search\Model\CatalogSearch\Resource\Layer\Filter\Attribute"
            );

            $config = Mage::app()->getConfig();
            foreach ($rewrites as $key => $value) {
                $config->setNode($key, $value);
            }
        }
    }

    /**
     * Call remove testmode
     */
    public function removeTest() {
        $this->_modelProductSync->removeTestMode();    
        
    }
    
    /**
     * make prodcuts for update when category change products
     */
    public function setCategoryProductsToSync(\Magento\Framework\Event\Observer $observer) {
        try {
            $updatedProductsIds = $observer->getData('product_ids');
            
            if (count($updatedProductsIds) == 0) {
                return;
            }
            $this->_modelProductSync->updateSpecificProductIds($updatedProductsIds);

        } catch (Exception $e) {
            $this->_searchHelperData->log(\Zend\Log\Logger::CRIT, sprintf("Exception thrown in %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage()));
        }
        
    }
    
    /**
     * Update the product ratings value in product attribute
     */
    public function ratingsUpdate(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $object = $observer->getEvent()->getObject();
            $statusId = $object->getStatusId();
            $allStores = Mage::app()->getStores();
            if($statusId == 1) {
                $productId = $object->getEntityPkValue();
                $ratingObj = $this->_ratingModelRating->getEntitySummary($productId);
                $ratings = $ratingObj->getSum()/$ratingObj->getCount();
                $entity_type = $this->_modelEntityType->loadByCode("catalog_product");
                $entity_typeid = $entity_type->getId();
                $attributecollection = $this->_modelEntityAttribute->getCollection()->addFieldToFilter("entity_type_id", $entity_typeid)->addFieldToFilter("attribute_code", "rating");
                if(count($attributecollection) > 0) {
                    if(count($object->getData('stores')) > 0) {
                        foreach($object->getData('stores') as $key => $value) {
                            $this->_modelProductAction->updateAttributes(array($productId), array('rating'=>$ratings),$value);
                        }
                    }
                    /* update attribute */
                    if(count($allStores) > 1) {
                        $this->_modelProductAction->updateAttributes(array($productId), array('rating'=>0),0);
                    }

                    /* mark product for update to sync data with klevu */
                    $this->_modelProductSync->updateSpecificProductIds(array($productId));
                }
            }
        } catch (Exception $e) {
            $this->_searchHelperData->log(\Zend\Log\Logger::CRIT, sprintf("Exception thrown in %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage()));
        }
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer){
    
            $rewrites = array(
                "global/models/catalogsearch_resource/rewrite/fulltext_collection"         => "Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection",
                "global/models/catalogsearch_mysql4/rewrite/fulltext_collection"           => "Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection",
                "global/models/catalogsearch/rewrite/layer_filter_attribute"               => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Attribute",
                "global/models/catalog/rewrite/config"                                     => "Klevu\Search\Model\Catalog\Model\Config",
                "global/models/catalog/rewrite/layer_filter_price"                         => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Price",
                "global/models/catalog/rewrite/layer_filter_category"                      => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Category",
                "global/models/catalog_resource/rewrite/layer_filter_attribute"            => "Klevu\Search\Model\CatalogSearch\Resource\Layer\Filter\Attribute",
                "global/models/catalog_resource_eav_mysql4/rewrite/layer_filter_attribute" => "Klevu\Search\Model\CatalogSearch\Resource\Layer\Filter\Attribute"
            );

            $config = $this->_appConfigScopeConfigInterface;
            foreach ($rewrites as $key => $value) {
                $config->setNode($key, $value);
            }
    
    }
    
 
}