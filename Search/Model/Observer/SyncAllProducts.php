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

class SyncAllProducts implements ObserverInterface{

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

    public function __construct(
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Magento\Framework\Filesystem $magentoFrameworkFilesystem, 
        \Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_modelProductSync = $modelProductSync;
        $this->_magentoFrameworkFilesystem = $magentoFrameworkFilesystem;
        $this->_searchHelperData = $searchHelperData;
    }


 
   /**
     * Mark all of the products for update and then schedule a sync
     * to run immediately.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $store = null;
        $sync = $this->_modelProductSync;

        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
        
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
        $sync->schedule();
    }
    
}