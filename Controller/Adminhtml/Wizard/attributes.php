<?php

namespace Klevu\Search\Controller\Adminhtml\Wizard;

class attributes extends \Klevu\Search\Controller\Adminhtml\Wizard
{
    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;

    public function __construct(\Klevu\Search\Model\Session $searchModelSession)
    {
        $this->_searchModelSession = $searchModelSession;

        parent::__construct();
    }

    public function execute() {
        
        $request = $this->getRequest();

        if (!$request->isAjax()) {
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

        $this->loadLayout();
        $this->initLayoutMessages('klevu_search/session');
        $this->renderLayout();
    }
}
