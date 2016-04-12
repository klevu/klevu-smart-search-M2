<?php

/**
 * Class \Klevu\Search\Model\Order\Sync
 * @method \Magento\Framework\Db\Adapter\Interface getConnection()
 */
namespace Klevu\Search\Model\Order;

class Sync extends \Klevu\Search\Model\Sync {
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_frameworkModelResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    protected $_modelOrderItem;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Klevu\Search\Model\Api\Action\Producttracking
     */
    protected $_apiActionProducttracking;

    /**
     * @var \Klevu\Search\Model\Resource\Notification\Collection
     */
    //protected $_resourceNotificationCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_frameworkModelDate;

    public function __construct(\Magento\Framework\App\ResourceConnection $frameworkModelResource, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Magento\Sales\Model\Order\Item $modelOrderItem, 
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Klevu\Search\Model\Api\Action\Producttracking $apiActionProducttracking, 
        //\Klevu\Search\Model\Resource\Notification\Collection $resourceNotificationCollection, 
        \Magento\Framework\Stdlib\DateTime\DateTime $frameworkModelDate)
    {
        $this->_frameworkModelResource = $frameworkModelResource;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_modelOrderItem = $modelOrderItem;
        $this->_searchHelperData = $searchHelperData;
        $this->_apiActionProducttracking = $apiActionProducttracking;
        //$this->_resourceNotificationCollection = $resourceNotificationCollection;
        $this->_frameworkModelDate = $frameworkModelDate;

       // parent::__construct();
    }


    const NOTIFICATION_TYPE = "order_sync";

    public function _construct() {
        parent::_construct();

        $this->addData(array(
            "connection" => $this->_frameworkModelResource->getConnection("core_write")
        ));
    }

    public function getJobCode() {
        return "klevu_search_order_sync";
    }

    /**
     * Add the items from the given order to the Order Sync queue. Does nothing if
     * Order Sync is disabled for the store that the order was placed in.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param bool                   $force Skip enabled check
     *
     * @return $this
     */
    public function addOrderToQueue(\Magento\Sales\Model\Order $order, $force = false) {
        if (!$this->isEnabled($order->getStoreId()) && !$force) {
            return $this;
        }

        $items = array();
        foreach ($order->getAllVisibleItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */

            // For configurable products add children items only, for all other products add parents
            if ($item->getProductType() == \Magento\Catalog\Model\Product\Type\Configurable::TYPE_CODE) {
                foreach ($item->getChildrenItems() as $child) {
                    if($child->getId()!=null) {
                        $items[] = $child->getId();
                    }
                }
            } else {
                if($item->getId()!=null) {
                        $items[] = $item->getId();
                }
                
            }
        }

        // in case of multiple addresses used for shipping
        // its possible that items object here is empty
        // if so, we do not add to the item.
        if(!empty($items)) {
           $this->addItemsToQueue($items);
        }

        return $this;
    }

    /**
     * Clear the Order Sync queue for the given store. If no store is given, clears
     * the queue for all stores.
     *
     * @param \Magento\Framework\Model\Store|int|null $store
     *
     * @return int
     */
    public function clearQueue($store = null) {
        $select = $this->getConnection()
            ->select()
            ->from(array("k" => $this->getConnection()->getTableName("klevu_search/order_sync")));

        if ($store) {
            $store = $this->_storeModelStoreManagerInterface->getStore($store);
            $select
                ->join(
                    array("i" => $this->getTableName("sales/order_item")),
                    "k.order_item_id = i.item_id",
                    ""
                )
                ->where("i.store_id = ?", $store->getId());
        }

        $result = $this->getConnection()->query($select->deleteFromSelect("k"));
        return $result->rowCount();
    }

    public function run() {
        try {
            if ($this->isRunning(2)) {
                // Stop if another copy is already running
                $this->log(\Zend\Log\Logger::INFO, "Another copy is already running. Stopped.");
                return;
            }
            
            $stores = $this->_storeModelStoreManagerInterface->getStores();
            foreach ($stores as $store) {
                if($this->_searchHelperConfig->isOrderSyncEnabled($store->getId())) {
                    $this->log(\Zend\Log\Logger::INFO, "Starting sync.");
                    $items_synced = 0;
                    $errors = 0;

                    $item = $this->_modelOrderItem;

                    $stmt = $this->getConnection()->query($this->getSyncQueueSelect());
                    while ($item_id = $stmt->fetchColumn()) {
                        if ($this->rescheduleIfOutOfMemory()) {
                            return;
                        }

                        $item->setData(array());
                        $item->load($item_id);

                        if ($item->getId()) {
                            if ($this->isEnabled($item->getStoreId())) {
                                if ($this->getApiKey($item->getStoreId())) {
                                        $result = $this->sync($item);
                                        if ($result === true) {
                                            $this->removeItemFromQueue($item_id);
                                            $items_synced++;
                                        } else {
                                            $this->log(\Zend\Log\Logger::INFO, sprintf("Skipped order item %d: %s", $item_id, $result));
                                            $errors++;
                                        }
                                }
                            } else {
                                $this->log(\Zend\Log\Logger::ERR, sprintf("Skipped item %d: Order Sync is not enabled for this store.", $item_id));
                                $this->removeItemFromQueue($item_id);
                            }
                        } else {
                            $this->log(\Zend\Log\Logger::ERR, sprintf("Order item %d does not exist: Removed from sync!", $item_id));
                            $this->removeItemFromQueue($item_id);
                            $errors++;
                        }
                    }

                    $this->log(\Zend\Log\Logger::INFO, sprintf("Sync finished. %d items synced.", $items_synced));
                    $this->_searchHelperConfig->setLastOrderSyncRun();

                    if ($errors) {
                        //$this->notify(__("Order Sync failed to sync some of the order items. Please consult the logs for more details."));
                    } else {
                        // If a sync finished without errors, existing notifications no longer apply
                        $this->deleteNotifications();
                    }
                }
            }
        } catch(Exception $e) {
            // Catch the exception that was thrown, log it, then throw a new exception to be caught the Magento cron.
            $this->_searchHelperData->log(\Zend\Log\Logger::CRIT, sprintf("Exception thrown in %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage()));
            throw $e;
        }
    }

    /**
     * Sync the given order item to Klevu. Returns true on successful sync and
     * the error message otherwise.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return bool|string
     */
    protected function sync($item) {
        if (!$this->getApiKey($item->getStoreId())) {
            return "Klevu Search is not configured for this store.";
        }

        $parent = null;
        if ($item->getParentItemId()) {
            $parent = $this->_modelOrderItem->load($item->getParentItemId());
        }

        $response = $this->_apiActionProducttracking
            ->setStore($this->_storeModelStoreManagerInterface->getStore($item->getStoreId()))
            ->execute(array(
            "klevu_apiKey"    => $this->getApiKey($item->getStoreId()),
            "klevu_type"      => "checkout",
            "klevu_productId" => $this->_searchHelperData->getKlevuProductId($item->getProductId(), ($parent) ? $parent->getProductId() : 0),
            "klevu_unit"      => $item->getQtyOrdered() ? $item->getQtyOrdered() : ($parent ? $parent->getQtyOrdered() : null),
            "klevu_salePrice" => $item->getPriceInclTax() ? $item->getPriceInclTax() : ($parent ? $parent->getPriceInclTax() : null),
            "klevu_currency"  => $this->getStoreCurrency($item->getStoreId()),
            "klevu_shopperIP" => $this->getOrderIP($item->getOrderId())
        ));

        if ($response->isSuccessful()) {
            return true;
        } else {
            return $response->getMessage();
        }
    }

    /**
     * Check if Order Sync is enabled for the given store.
     *
     * @param $store_id
     *
     * @return bool
     */
    protected function isEnabled($store_id) {
        $is_enabled = $this->getData("is_enabled");
        if (!is_array($is_enabled)) {
            $is_enabled = array();
        }

        if (!isset($is_enabled[$store_id])) {
            $is_enabled[$store_id] = $this->_searchHelperConfig->isOrderSyncEnabled($store_id);
            $this->setData("is_enabled", $is_enabled);
        }

        return $is_enabled[$store_id];
    }

    /**
     * Return the JS API key for the given store.
     *
     * @param $store_id
     *
     * @return string|null
     */
    protected function getApiKey($store_id) {
        $api_keys = $this->getData("api_keys");
        if (!is_array($api_keys)) {
            $api_keys = array();
        }

        if (!isset($api_keys[$store_id])) {
            $api_keys[$store_id] = $this->_searchHelperConfig->getJsApiKey($store_id);
            $this->setData("api_keys", $api_keys);
        }

        return $api_keys[$store_id];
    }

    /**
     * Get the currency code for the given store.
     *
     * @param $store_id
     *
     * @return string
     */
    protected function getStoreCurrency($store_id) {
        $currencies = $this->getData("currencies");
        if (!is_array($currencies)) {
            $currencies = array();
        }

        if (!isset($currencies[$store_id])) {
            $currencies[$store_id] = $this->_storeModelStoreManagerInterface->getStore($store_id)->getDefaultCurrencyCode();
            $this->setData("currencies", $currencies);
        }

        return $currencies[$store_id];
    }

    /**
     * Return the customer IP for the given order.
     *
     * @param $order_id
     *
     * @return string
     */
    protected function getOrderIP($order_id) {
        $order_ips = $this->getData("order_ips");
        if (!is_array($order_ips)) {
            $order_ips = array();
        }

        if (!isset($order_ips[$order_id])) {
            $order_ips[$order_id] = $this->getConnection()->fetchOne(
                $this->getConnection()
                    ->select()
                    ->from(array("order" => $this->getTableName("sales/order")), "remote_ip")
                    ->where("order.entity_id = ?", $order_id)
            );
            $this->setData("order_ips", $order_ips);
        }

        return $order_ips[$order_id];
    }

    /**
     * Return a select statement for getting all items in the sync queue.
     *
     * @return \Zend\Db\Select
     */
    protected function getSyncQueueSelect() {
        return $this->getConnection()
            ->select()
            ->from($this->getTableName("klevu_search/order_sync"));
    }

    /**
     * Add the given order item IDs to the sync queue.
     *
     * @param $order_item_ids
     *
     * @return int
     */
    protected function addItemsToQueue($order_item_ids) {
        if (!is_array($order_item_ids)) {
            $order_item_ids = array($order_item_ids);
        }

        return $this->getConnection()->insertArray(
            $this->getTableName("klevu_search/order_sync"),
            array("order_item_id"),
            $order_item_ids
        );
    }

    /**
     * Remove the given item from the sync queue.
     *
     * @param $order_item_id
     *
     * @return bool
     */
    protected function removeItemFromQueue($order_item_id) {
        return $this->getConnection()->delete(
            $this->getTableName("klevu_search/order_sync"),
            array("order_item_id" => $order_item_id)
        ) === 1;
    }

    /**
     * Create an Adminhtml notification for Order Sync, overwriting
     * any existing ones.
     *
     * @param $message
     *
     * @return $this
     */
    protected function notify($message) {
        /*$notification = $this->_resourceNotificationCollection
            ->addFieldToFilter("type", array("eq" => static::NOTIFICATION_TYPE))
            ->getFirstItem();

        $notification->addData(array(
            "type"    => static::NOTIFICATION_TYPE,
            "date"    => $this->_frameworkModelDate->timestamp(),
            "message" => $message
        ));

        $notification->save();*/

        return $this;
    }

    /**
     * Delete Adminhtml notifications for Order Sync.
     *
     * @return $this
     */
    protected function deleteNotifications() {
        $this->getConnection()->delete(
            $this->getTableName('klevu_search/notification'),
            array("type" => static::NOTIFICATION_TYPE)
        );

        return $this;
    }
}
