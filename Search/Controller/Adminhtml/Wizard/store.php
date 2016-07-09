<?php

namespace Klevu\Search\Controller\Adminhtml\Wizard;

class store extends \Magento\Backend\App\Action
{
    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;

    public function __construct(\Magento\Backend\App\Action\Context $Context,\Klevu\Search\Model\Session $searchModelSession                                )
    {
        $this->_searchModelSession = $searchModelSession;

        parent::__construct($Context);
    }

    public function execute() {
        $request = $this->getRequest();

        if (!$request->isAjax()) {
            return $this->_redirect("adminhtml/dashboard");
        }

        $session = $this->_searchModelSession;

        if (!$session->getConfiguredCustomerId()) {
            $session->addError(__("You must configure a user first."));
            return $this->_redirect("*/*/user");
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
  
    
}
