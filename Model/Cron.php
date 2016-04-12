<?php

namespace Klevu\Search\Model;

class Cron extends \Magento\Framework\DataObject {
    /**
     * @var \Klevu\Search\Model\Resource\Notification\Collection
     
    protected $_resourceNotificationCollection;

    public function __construct(\Klevu\Search\Model\Resource\Notification\Collection $resourceNotificationCollection)
    {
        $this->_resourceNotificationCollection = $resourceNotificationCollection;

        parent::__construct();
    }


    public function clearCronCheckNotification() {
        $collection = $this->_resourceNotificationCollection;
        $collection->addFieldToFilter("type", array("eq" => "cron_check"));

        foreach ($collection as $notification) {
            $notification->delete();
        }
    }
    */
}
