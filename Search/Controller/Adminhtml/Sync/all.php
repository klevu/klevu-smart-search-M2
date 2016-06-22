<?php

namespace Klevu\Search\Controller\Adminhtml\Sync;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;
use Klevu\Search\Helper\Config;
use Klevu\Search\Model\Product\Sync;
use Klevu\Search\Helper\Data;
use Magento\Framework\Event\ManagerInterface;



class all extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendModelSession;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_frameworkEventManagerInterface;

    public function __construct(\Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Magento\Backend\Model\Session $backendModelSession, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Magento\Framework\Event\ManagerInterface $frameworkEventManagerInterface)
    {

        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_backendModelSession = $backendModelSession;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_modelProductSync = $modelProductSync;
        $this->_searchHelperData = $searchHelperData;
        $this->_frameworkEventManagerInterface = $frameworkEventManagerInterface;

        parent::__construct($context);
    }

    public function execute() {
        $store = $this->getRequest()->getParam("store");

        if ($store !== null) {
            try {
                $store = $this->_storeModelStoreManagerInterface->getStore($store);
            } catch (\Magento\Framework\Model\Store\Exception $e) {
                $this->_backendModelSession->addError(__("Selected store could not be found!"));
                $this->_redirect($this->_redirect->getRefererUrl());
            }
        }
        if ($this->_searchHelperConfig->isProductSyncEnabled()) {
            if($this->_searchHelperConfig->getSyncOptionsFlag() == "2") {
                $this->_modelProductSync
                    ->markAllProductsForUpdate($store)
                    ->schedule();

                if ($store) {
                    $this->_searchHelperData->log(\Zend\Log\Logger::INFO, sprintf("Product Sync scheduled to re-sync ALL products in %s (%s).",
                        $store->getWebsite()->getName(),
                        $store->getName()
                    ));

                    $this->messageManager->addSuccess(sprintf("Klevu Search Product Sync scheduled to be run on the next cron run for ALL products in %s (%s).",
                        $store->getWebsite()->getName(),
                        $store->getName()
                    ));
                } else {
                    $this->_searchHelperData->log(\Zend\Log\Logger::INFO, "Product Sync scheduled to re-sync ALL products.");

                    $this->messageManager->addSuccess(__("Klevu Search Sync scheduled to be run on the next cron run for ALL products."));
                }
            } else {
                $this->syncWithoutCron();
            }
        } else {
            $this->messageManager->addError(__("Klevu Search Product Sync is disabled."));
        }
        
        $this->_frameworkEventManagerInterface->dispatch('sync_all_external_data', array(
            'store' => $store
        ));

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
    
    protected function _isAllowed()
    {
         return true;
    }
    
    public function SyncWithoutCron() {
        try {
            $this->_modelProductSync->run();
            /* Use event For other content sync */
            $this->_frameworkEventManagerInterface->dispatch('content_data_to_sync', array());
            $this->messageManager->addSuccess(__("Data updates have been sent to Klevu"));
        } catch (\Magento\Framework\Model\Store\Exception $e) {
            $this->_psrLogLoggerInterface->error($e);
        }
        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
