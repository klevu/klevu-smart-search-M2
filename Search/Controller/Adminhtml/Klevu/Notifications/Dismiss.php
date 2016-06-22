<?php

namespace Klevu\Search\Controller\Adminhtml\Klevu\Notifications;

class Dismiss extends \Klevu\Search\Controller\Adminhtml\Klevu\Notifications
{
    /**
     * @var \Klevu\Search\Model\Notification
     */
    protected $_searchModelNotification;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendModelSession;

    public function __construct(\Klevu\Search\Model\Notification $searchModelNotification, 
        \Magento\Backend\Model\Session $backendModelSession)
    {
        $this->_searchModelNotification = $searchModelNotification;
        $this->_backendModelSession = $backendModelSession;

        parent::__construct();
    }

    public function execute() {
        $id = intval($this->getRequest()->getParam("id"));

        $notification = $this->_searchModelNotification->load($id);

        if ($notification->getId()) {
            $notification->delete();
        } else {
            $this->_backendModelSession->addError("Unable to dismiss Klevu notification as it does not exist.");
        }

        return $this->_redirectReferer($this->getUrl("adminhtml/dashboard"));
    }
}
