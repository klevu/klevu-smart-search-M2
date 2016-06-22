<?php

namespace Klevu\Search\Controller\Adminhtml\Klevu;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;
use Klevu\Search\Helper\Config;
use Klevu\Search\Model\Product\Sync;
use Klevu\Search\Helper\Data;
use Magento\Framework\Event\ManagerInterface;

class SyncWithoutCron extends \Magento\Backend\App\Action {
    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_frameworkEventManagerInterface;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendModelSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_psrLogLoggerInterface;

    public function __construct(\Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Magento\Framework\Event\ManagerInterface $frameworkEventManagerInterface, 
        \Magento\Backend\Model\Session $backendModelSession, 
        \Psr\Log\LoggerInterface $psrLogLoggerInterface)
    {
        $this->_modelProductSync = $modelProductSync;
        $this->_frameworkEventManagerInterface = $frameworkEventManagerInterface;
        $this->_backendModelSession = $backendModelSession;
        $this->_psrLogLoggerInterface = $psrLogLoggerInterface;

        parent::__construct();
    }


    /* Sync data based on sync options selected */
    
    /* Run the product sync externally */
    
    /* Run the product sync */ 
    public function execute() {
        try {
            $this->_modelProductSync->run();
            /* Use event For other content sync */
            $this->_frameworkEventManagerInterface->dispatch('content_data_to_sync', array());
            $this->_backendModelSession->addSuccess(__("Data updates have been sent to Klevu"));
        } catch (\Magento\Framework\Model\Store\Exception $e) {
            $this->_psrLogLoggerInterface->error($e);
        }
        return $this->_redirectReferer("adminhtml/dashboard");
    }
    
    /* save sync options using Ajax */
    
}
