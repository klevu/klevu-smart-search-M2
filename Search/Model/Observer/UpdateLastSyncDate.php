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

class UpdateLastSyncDate implements ObserverInterface{

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
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_frameworkModelResource;

    public function __construct(
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Magento\Framework\Filesystem $magentoFrameworkFilesystem, 
        \Klevu\Search\Helper\Data $searchHelperData,
		\Magento\Framework\App\ResourceConnection $frameworkModelResource
)
    {
        $this->_modelProductSync = $modelProductSync;
        $this->_magentoFrameworkFilesystem = $magentoFrameworkFilesystem;
        $this->_searchHelperData = $searchHelperData;
		$this->_frameworkModelResource = $frameworkModelResource;

    }

    /**
     * When products are updated in bulk, update products so that they will be synced.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
		try {
			$product_ids[] = $observer->getEvent()->getProduct()->getId();

			if(empty($product_ids)) {
				return;
			}

			$product_ids = implode(',', $product_ids);
			$where = sprintf("product_id IN(%s) OR parent_id IN(%s)", $product_ids, $product_ids);
			$resource = $this->_frameworkModelResource;
			$resource->getConnection('core_write')
				->update(
					$resource->getTableName('klevu_product_sync'),
					array('last_synced_at' => '0'),
					$where
				);
	    } catch(\Exception $e) {
                 $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Error while updating date in klevu product sync:\n%s", $e->getMessage()));
        }
    }
    
}