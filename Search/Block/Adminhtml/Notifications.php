<?php

namespace Klevu\Search\Block\Adminhtml;

class Notifications extends \Magento\Backend\Block\Template {
    /**
     * @var \Klevu\Search\Model\Resource\Notification\Collection
     */
    protected $_resourceNotificationCollection;

    public function __construct(\Klevu\Search\Model\Resource\Notification\Collection $resourceNotificationCollection)
    {
        $this->_resourceNotificationCollection = $resourceNotificationCollection;

        parent::__construct();
    }


    /**
     * Return all notifications.
     *
     * @return \Klevu\Search\Model\Resource\Notification\Collection
     */
    protected function getNotifications() {
        return $this->_resourceNotificationCollection;
    }

    /**
     * Return the URL to dismiss the given notification.
     *
     * @param $notification
     *
     * @return string
     */
    protected function getDismissUrl($notification) {
        return $this->getUrl("adminhtml/klevu_notifications/dismiss", array("id" => $notification->getId()));
    }
}
