<?php

namespace Klevu\Search\Test\Model\Order;

class Sync extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Magento\Framework\Model\Resource
     */
    protected $_frameworkModelResource;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesModelOrder;

    /**
     * @var \Klevu\Search\Model\Order\Sync
     */
    protected $_modelOrderSync;

    /**
     * @var \Klevu\Search\Model\Api\Response\Data
     */
    protected $_apiResponseData;

    public function __construct(\Magento\Framework\App\ResourceConnection $frameworkModelResource, 
        \Magento\Sales\Model\Order $salesModelOrder, 
        \Klevu\Search\Model\Order\Sync $modelOrderSync, 
        \Klevu\Search\Model\Api\Response\Data $apiResponseData)
    {
        $this->_frameworkModelResource = $frameworkModelResource;
        $this->_salesModelOrder = $salesModelOrder;
        $this->_modelOrderSync = $modelOrderSync;
        $this->_apiResponseData = $apiResponseData;

        parent::__construct();
    }


    protected function tearDown() {
        $resource = $this->_frameworkModelResource;
        $connection = $resource->getConnection("core_write");

        $connection->delete($resource->getTableName("klevu_search/order_sync"));

        parent::tearDown();
    }

    /**
     * @test
     * @loadFixture
     */
    public function testAddOrderToQueue() {
        $order = $this->_salesModelOrder;
        $order->load(1);

        $model = $this->_modelOrderSync;
        $model->addOrderToQueue($order);

        $this->assertEquals(array(array("order_item_id" => "2")), $this->getOrderSyncTableContents(),
            "Failed asserting that addOrderToQueue() adds the child configurable item to Order Sync queue."
        );
    }

    /**
     * @test
     * @loadFixture
     */
    public function testClearQueue() {
        $model = $this->_modelOrderSync;

        $model->clearQueue(1);

        $this->assertEquals(array(array("order_item_id" => "3")), $this->getOrderSyncTableContents(),
            "Failed asserting that clearQueue() only removes order items for the store given."
        );

        $model->clearQueue();

        $this->assertEmpty($this->getOrderSyncTableContents(),
            "Failed asserting that clearQueue() removes all items if no store is given."
        );
    }

    /**
     * @test
     * @loadFixture
     */
    public function testRun() {
        $this->replaceApiActionByMock(
            "klevu_search/api_action_producttracking",
            $this->_apiResponseData->setRawResponse(
                new \Zend\Http\Response(200, array(), $this->getDataFileContents("data_response_success_only.xml"))
            )
        );

        $model = $this->getModelMock("klevu_search/order_sync", array("isRunning", "isBelowMemoryLimit"));
        $model
            ->expects($this->any())
            ->method("isRunning")
            ->will($this->returnValue(false));
        $model
            ->expects($this->any())
            ->method("isBelowMemoryLimit")
            ->will($this->returnValue(true));

        $model->run();

        $this->assertEmpty($this->getOrderSyncTableContents(),
            "Failed asserting that order item gets removed from the sync queue."
        );
    }

    protected function getOrderSyncTableContents($where = null) {
        $resource = $this->_frameworkModelResource;
        $connection = $resource->getConnection("core_write");

        $select = $connection->select()->from($resource->getTableName("klevu_search/order_sync"));
        if ($where) {
            $select->where($where);
        }

        return $connection->fetchAll($select);
    }
}
