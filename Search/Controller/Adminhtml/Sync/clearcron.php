<?php

namespace Klevu\Search\Controller\Adminhtml\Sync;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;
use Klevu\Search\Model\Product\Sync;
use Magento\Framework\Event\ManagerInterface;



class clearcron extends \Magento\Backend\App\Action
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
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Klevu\Search\Helper\Data $searchHelperData)
    {


        $this->_modelProductSync = $modelProductSync;
        $this->_frameworkEventManagerInterface = $context->getEventManager();

        parent::__construct($context);
    }
    
    protected function _isAllowed()
    {
         return true;
    }
    
	/* clear the cron entry */
    public function execute() {
        $this->_modelProductSync->clearKlevuCron();
        $this->messageManager->addSuccess(__("Running Klevu product Sync entry cleared from cron_schedule table."));
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
