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

class ScheduleOrderSync implements ObserverInterface{

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
     * @var \Klevu\Search\Model\Order\Sync
     */
    protected $_modelOrderSync;
	
	

    public function __construct(
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Magento\Framework\Filesystem $magentoFrameworkFilesystem, 
        \Klevu\Search\Helper\Data $searchHelperData,
		\Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface,
		\Klevu\Search\Model\Order\Sync $modelOrderSync)
    {
        $this->_modelProductSync = $modelProductSync;
        $this->_magentoFrameworkFilesystem = $magentoFrameworkFilesystem;
        $this->_searchHelperData = $searchHelperData;
		$this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
		$this->_modelOrderSync = $modelOrderSync;
    }


 
    /**
     * Schedule an Order Sync to run immediately. If the observed event
     * contains an order, add it to the sync queue before scheduling.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $store = $this->_storeModelStoreManagerInterface->getStore($observer->getEvent()->getStore());
        $model = $this->_modelOrderSync;
        $order = $observer->getEvent()->getOrder();
        if ($order) {
            $model->addOrderToQueue($order);
        }
        $model->schedule();
    }
    
}