<?php

namespace Klevu\Search\Controller\Adminhtml\Wizard;

class user extends \Magento\Backend\App\Action
{
    public function execute() {

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
