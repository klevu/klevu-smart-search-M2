<?php

namespace Klevu\Search\Controller\Adminhtml\Manual;

class sync extends \Magento\Backend\App\Action
{


    public function execute() {

        \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Model\Product\Sync')->runManually();
        /* Use event For other content sync */
         \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Event\ManagerInterface')->dispatch('content_data_to_sync', array());
         return $this->_redirect($this->_redirect->getRefererUrl());
    }
    
        /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
