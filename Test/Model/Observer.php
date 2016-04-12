<?php

use Ecom\Dev\PHPUnit\Test\Case\Util as TestUtil;

namespace Klevu\Search\Test\Model;

class Observer extends Ecom\Dev\PHPUnit\Test\Case {
    /**
     * @var \Magento\Framework\Model\Resource
     */
    protected $_frameworkModelResource;

    /**
     * @var \Klevu\Search\Model\Observer
     */
    protected $_searchModelObserver;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesModelOrder;

    /**
     * @var \Magento\Cron\Model\Resource\Schedule\Collection
     */
    protected $_resourceScheduleCollection;

    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Klevu\Search\Model\Order\Sync
     */
    protected $_modelOrderSync;

    public function __construct(\Magento\Framework\App\ResourceConnection $frameworkModelResource, 
        \Klevu\Search\Model\Observer $searchModelObserver, 
        \Magento\Sales\Model\Order $salesModelOrder, 
        \Magento\Cron\Model\Resource\Schedule\Collection $resourceScheduleCollection, 
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Klevu\Search\Model\Order\Sync $modelOrderSync)
    {
        $this->_frameworkModelResource = $frameworkModelResource;
        $this->_searchModelObserver = $searchModelObserver;
        $this->_salesModelOrder = $salesModelOrder;
        $this->_resourceScheduleCollection = $resourceScheduleCollection;
        $this->_modelProductSync = $modelProductSync;
        $this->_modelOrderSync = $modelOrderSync;

        parent::__construct();
    }


    public function setUp() {
        parent::setUp();

        $collection = $this->getProductSyncCronScheduleCollection();
        foreach ($collection as $item) {
            $item->delete();
        }

        $collection = $this->getOrderSyncCronScheduleCollection();
        foreach ($collection as $item) {
            $item->delete();
        }
    }

    public function tearDown() {
        $collection = $this->getProductSyncCronScheduleCollection();
        foreach ($collection as $item) {
            $item->delete();
        }

        $resource = $this->_frameworkModelResource;
        $resource->getConnection("core_write")->delete($resource->getTableName("klevu_search/order_sync"));

        Ecom\Dev\Utils\Reflection::setRestrictedPropertyValue(
            Mage::getConfig(),
            "_classNameCache",
            array()
        );

        parent::tearDown();
    }

    public function testScheduleProductSync() {
        $observer = $this->_searchModelObserver;

        $observer->scheduleProductSync(new \Magento\Framework\Event\Observer());

        $this->assertEquals(1, $this->getProductSyncCronScheduleCollection()->getSize(),
        "Failed to assert that scheduleProductSync() schedules the Product Sync cron when called.");
    }

    /**
     * @test
     * @loadFixture
     */
    public function testScheduleOrderSync() {
        $model = $this->_searchModelObserver;

        $order = $this->_salesModelOrder->load(1);
        $event = new \Magento\Framework\Event();
        $event->addData(array(
            "event_name" => "sales_order_place_after",
            "order" => $order
        ));
        $observer = new \Magento\Framework\Event\Observer();
        $observer->addData(array("event" => $event));

        $model->scheduleOrderSync($observer);
        
        $this->assertEquals(array(array("order_item_id" => "2")), $this->getOrderSyncQueue());

        $this->assertEquals(1, $this->getOrderSyncCronScheduleCollection()->getSize(),
            "Failed to assert that scheduleOrderSync() schedules the Order Sync cron when called."
        );
    }

    /**
     * @test
     * @loadFixture
     */
    public function testLandingPageRewritesDisabled() {
        $this->_searchModelObserver->applyLandingPageModelRewrites(new \Magento\Framework\Event\Observer());

        foreach ($this->getLandingPageRewrites() as $type => $rewrites) {
            foreach ($rewrites as $name => $class) {
                $object = null;
                switch ($type) {
                    case "resource":
                        $object = Mage::getResourceModel($name);
                        break;
                    case "model":
                    default:
                        $object = Mage::getModel($name);
                }

                $this->assertNotInstanceOf($class, $object,
                    sprintf("Failed asserting that %s %s is not rewritten when landing page is disabled.",
                        $name,
                        $type
                    )
                );
            }
        }
    }

    /**
     * @test
     * @loadFixture
     */
    public function testLandingPageRewritesEnabled() {
        $this->_searchModelObserver->applyLandingPageModelRewrites(new \Magento\Framework\Event\Observer());

        foreach ($this->getLandingPageRewrites() as $type => $rewrites) {
            foreach ($rewrites as $name => $class) {
                $object = null;
                switch ($type) {
                    case "resource":
                        $object = Mage::getResourceModel($name);
                        break;
                    case "model":
                    default:
                        $object = Mage::getModel($name);
                }

                $this->assertInstanceOf($class, $object,
                    sprintf("Failed asserting that %s %s gets rewritten to %s when landing page is enabled.",
                        $name,
                        $type,
                        $class
                    )
                );
            }
        }
    }

    /**
     * Return a cron/schedule collection filtered for Product Sync jobs only.
     *
     * @return \Magento\Cron\Model\Resource\Schedule\Collection
     */
    protected function getProductSyncCronScheduleCollection() {
        return $this->_resourceScheduleCollection
                ->addFieldToFilter("job_code", $this->_modelProductSync->getJobCode());
    }

    /**
     * Return a cron/schedule collection filtered for Order Sync jobs only.
     *
     * @return \Magento\Cron\Model\Resource\Schedule\Collection
     */
    protected function getOrderSyncCronScheduleCollection() {
        return $this->_resourceScheduleCollection
            ->addFieldToFilter("job_code", $this->_modelOrderSync->getJobCode());
    }

    /**
     * Return all items in the Order Sync queue.
     *
     * @return array
     */
    protected function getOrderSyncQueue() {
        $resource = $this->_frameworkModelResource;
        $connection = $resource->getConnection("core_write");
        return $connection->fetchAll($connection
            ->select()
            ->from($resource->getTableName("klevu_search/order_sync"))
        );
    }

    /**
     * Return the model rewrites the landing page is expected to have.
     *
     * @return array
     */
    protected function getLandingPageRewrites() {
        return array(
            "resource" => array(
                "catalogsearch/fulltext_collection" => "Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection",
                "catalog/layer_filter_attribute"    => "Klevu\Search\Model\CatalogSearch\Resource\Layer\Filter\Attribute"
            ),
            "model"    => array(
                "catalogsearch/layer_filter_attribute" => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Attribute",
                "catalog/layer_filter_price"           => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Price",
                "catalog/layer_filter_category"        => "Klevu\Search\Model\CatalogSearch\Layer\Filter\Category",
                "catalog/config"                       => "Klevu\Search\Model\Catalog\Model\Config"
            )
        );
    }
}
