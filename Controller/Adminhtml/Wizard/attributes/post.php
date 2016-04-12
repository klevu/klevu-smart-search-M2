<?php

namespace Klevu\Search\Controller\Adminhtml\Wizard\attributes;

class post extends \Klevu\Search\Controller\Adminhtml\Wizard
{
    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    public function __construct(\Klevu\Search\Model\Session $searchModelSession, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Model\Product\Sync $modelProductSync)
    {
        $this->_searchModelSession = $searchModelSession;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_modelProductSync = $modelProductSync;

        parent::__construct();
    }

    public function execute() {
    
        $request = $this->getRequest();

        if (!$request->isPost() || !$request->isAjax()) {
            return $this->_redirect("adminhtml/dashboard");
        }

        $session = $this->_searchModelSession;

        if (!$session->getConfiguredCustomerId()) {
            $session->addError(__("You must configure a user first."));
            return $this->_redirect("*/*/configure_user");
        }

        if (!$session->getConfiguredStoreCode()) {
            $session->addError(__("Must select a store"));
            return $this->_redirect("*/*/configure_store");
        }

        if ($attributes = $request->getPost("attributes")) {
            $store = $this->_storeModelStoreManagerInterface->getStore($session->getConfiguredStoreCode());

            $this->_searchHelperConfig->setAdditionalAttributesMap($attributes, $store);

            $session->addSuccess(__("Attributes configured successfully. Attribute mappings saved to System Configuration."));

            // Schedule a Product Sync
            $this->_modelProductSync->schedule();

            $this->loadLayout();
            $this->initLayoutMessages("klevu_search/session");
            $this->renderLayout();
            return;
        } else {
            $session->addError(__("Missing attributes!"));
            //return $this->_forward("configure_attributes");
        }
    }
}
