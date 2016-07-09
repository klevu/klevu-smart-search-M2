<?php

/**
 * Class \Klevu\Search\Model\Observer
 *
 * @method setIsProductSyncScheduled($flag)
 * @method bool getIsProductSyncScheduled()
 */
namespace Klevu\Search\Model\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout\Interceptor;

class RatingsUpdate implements ObserverInterface{

    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_magentoFrameworkFilesystem;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;


    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $_modelProductAction;
	
	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;
	
	/**
     * @var \Magento\Rating\Model\Rating
     */
    protected $_ratingModelRating;
	
	/**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_modelEntityType;



    public function __construct(
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Magento\Framework\Filesystem $magentoFrameworkFilesystem, 
        \Klevu\Search\Helper\Data $searchHelperData,
		\Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface,
		\Magento\Review\Model\Rating $ratingModelRating,
        \Magento\Eav\Model\Entity\Type $modelEntityType,
		\Magento\Eav\Model\Entity\Attribute $modelEntityAttribute,
		\Magento\Catalog\Model\Product\Action $modelProductAction

		
)
    {
        $this->_modelProductSync = $modelProductSync;
        $this->_magentoFrameworkFilesystem = $magentoFrameworkFilesystem;
        $this->_searchHelperData = $searchHelperData;
		$this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
		$this->_ratingModelRating = $ratingModelRating;
		$this->_modelEntityType = $modelEntityType;
		$this->_modelEntityAttribute = $modelEntityAttribute;
		$this->_modelProductAction = $modelProductAction;


    }


	/**
     * Update the product ratings value in product attribute
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {

            $object = $observer->getEvent()->getObject();
            $statusId = $object->getStatusId();
			$allStores = $this->_storeModelStoreManagerInterface->getStores();
            if($statusId == 1) {
				$productId = $object->getEntityPkValue();
                $ratingObj = $this->_ratingModelRating->getEntitySummary($productId);
				if($ratingObj->getCount() != 0) {
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
            }
        } catch (Exception $e) {
            $this->_searchHelperData->log(\Zend\Log\Logger::CRIT, sprintf("Exception thrown in %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage()));
        }
    }
    
}