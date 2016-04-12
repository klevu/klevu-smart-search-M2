<?php

namespace Klevu\Search\Test\Model\Product;

class Sync extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Magento\Framework\Model\Resource
     */
    protected $_frameworkModelResource;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_resourceProductCollection;

    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Klevu\Search\Model\Api\Response\Message
     */
    protected $_apiResponseMessage;

    /**
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexModelIndexer;

    /**
     * @var \Magento\Index\Model\Resource\Event\Collection
     */
    protected $_resourceEventCollection;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_modelEntityAttribute;

    public function __construct(\Magento\Framework\App\ResourceConnection $frameworkModelResource, 
        \Magento\Catalog\Model\Resource\Product\Collection $resourceProductCollection, 
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Klevu\Search\Model\Api\Response\Message $apiResponseMessage, 
        \Magento\Index\Model\Indexer $indexModelIndexer, 
        \Magento\Index\Model\Resource\Event\Collection $resourceEventCollection, 
        \Magento\Eav\Model\Entity\Attribute $modelEntityAttribute)
    {
        $this->_frameworkModelResource = $frameworkModelResource;
        $this->_resourceProductCollection = $resourceProductCollection;
        $this->_modelProductSync = $modelProductSync;
        $this->_apiResponseMessage = $apiResponseMessage;
        $this->_indexModelIndexer = $indexModelIndexer;
        $this->_resourceEventCollection = $resourceEventCollection;
        $this->_modelEntityAttribute = $modelEntityAttribute;

        parent::__construct();
    }


    protected function tearDown() {
        $resource = $this->_frameworkModelResource;

        $resource->getConnection("core_write")->delete($resource->getTableName("klevu_search/product_sync"));

        $this->_resourceProductCollection->delete();

        parent::tearDown();
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     */
    public function testRun() {
        $this->reindexAll();

        $this->replaceApiActionByMock("klevu_search/api_action_startsession", $this->getSuccessfulSessionResponse());

        $model = $this->getModelMock("klevu_search/product_sync", array(
            "isBelowMemoryLimit", "deleteProducts", "updateProducts", "addProducts"
        ));
        $model
            ->expects($this->any())
            ->method("isBelowMemoryLimit")
            ->will($this->returnValue(true));
        $model
            ->expects($this->once())
            ->method("deleteProducts")
            ->with(array(
                array("product_id" => "130", "parent_id" => "0"),
                array("product_id" => "201", "parent_id" => "202"),
                array("product_id" => "211", "parent_id" => "212")
            ))
            ->will($this->returnValue(true));
        $model
            ->expects($this->once())
            ->method("updateProducts")
            ->with(array(
                array("product_id" => "133", "parent_id" => "0"),
                array("product_id" => "134", "parent_id" => "0"),
                array("product_id" => "203", "parent_id" => "204"),
                array("product_id" => "205", "parent_id" => "206")
            ))
            ->will($this->returnValue(true));
        $model
            ->expects($this->once())
            ->method("addProducts")
            ->with(array(
                array("product_id" => "132", "parent_id" => "0"),
                array("product_id" => "207", "parent_id" => "209"),
                array("product_id" => "208", "parent_id" => "209")
            ))
            ->will($this->returnValue(true));

        $model->run();
    }
    
    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     */
    public function testDeleteProducts() {
        $this->reindexAll();

        $this->replaceApiActionByMock("klevu_search/api_action_startsession", $this->getSuccessfulSessionResponse());
        $this->replaceApiActionByMock("klevu_search/api_action_deleterecords", $this->getSuccessfulMessageResponse());

        $this->_modelProductSync->run();

        $this->assertEquals(array(), $this->getProductSyncTableContents());
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     */
    public function testUpdateProducts() {
        $this->reindexAll();

        $this->replaceApiActionByMock("klevu_search/api_action_startsession", $this->getSuccessfulSessionResponse());
        $this->replaceApiActionByMock("klevu_search/api_action_updaterecords", $this->getSuccessfulMessageResponse());

        $this->replaceSessionByMock("core/session");
        $this->replaceSessionByMock("customer/session");

        $this->_modelProductSync->run();

        $contents = $this->getProductSyncTableContents('last_synced_at > "2008-06-27 01:57:22"');

        $this->assertTrue((is_array($contents) && count($contents) == 1));
        $this->assertEquals("133", $contents[0]['product_id']);
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     */
    public function testAddProducts() {
        $this->reindexAll();

        $this->replaceApiActionByMock("klevu_search/api_action_startsession", $this->getSuccessfulSessionResponse());
        $this->replaceApiActionByMock("klevu_search/api_action_addrecords", $this->getSuccessfulMessageResponse());

        $this->replaceSessionByMock("core/session");
        $this->replaceSessionByMock("customer/session");

        $this->_modelProductSync->run();

        $contents = $this->getProductSyncTableContents();

        $this->assertTrue((is_array($contents) && count($contents) == 1));
        $this->assertEquals("133", $contents[0]['product_id']);
    }
    
    

    /**
     * @test
     * @loadFixture
     */
    public function testClearAllProducts() {
        $model = $this->_modelProductSync;

        $model->clearAllProducts(1);

        $contents = $this->getProductSyncTableContents();

        $this->assertTrue(
            is_array($contents) && count($contents) == 1 && $contents[0]['product_id'] == 2,
            "Failed asserting that clearAllProducts() only removes products for the given store."
        );

        $model->clearAllProducts();

        $contents = $this->getProductSyncTableContents();

        $this->assertTrue(
            empty($contents),
            "Failed asserting that clearAllProducts() removes products for all stores."
        );
    }

    /**
     * @test
     */
    public function testAutomaticAttributes() {
        $model = $this->_modelProductSync;

        $automatic_attributes = $model->getAutomaticAttributes();

        $expected_attributes = $this->getExpectedAutomaticAttributes();

        $this->assertEquals($expected_attributes, $automatic_attributes);
    }

    /**
     * Return a klevu_search/api_response_message model with a successful response from
     * a startSession API call.
     *
     * @return \Klevu\Search\Model\Api\Response\Message
     */
    protected function getSuccessfulSessionResponse() {
        $model = $this->_apiResponseMessage->setRawResponse(
            new \Zend\Http\Response(200, array(), $this->getDataFileContents("startsession_response_success.xml"))
        );

        return $model;
    }

    /**
     * Return a klevu_search/api_response_message model with a successful response.
     *
     * @return \Klevu\Search\Model\Api\Response\Message
     */
    protected function getSuccessfulMessageResponse() {
        $model = $this->_apiResponseMessage->setRawResponse(
            new \Zend\Http\Response(200, array(), $this->getDataFileContents("message_response_success.xml"))
        );

        return $model;
    }

    /**
     * Return the contents of the Product Sync table.
     *
     * @param string $where The where clause to use in the database query
     *
     * @return array
     */
    protected function getProductSyncTableContents($where = null) {
        $resource = $this->_frameworkModelResource;
        $connection = $resource->getConnection("core_write");

        $select = $connection->select()->from($resource->getTableName('klevu_search/product_sync'));
        if ($where) {
            $select->where($where);
        }

        return $connection->fetchAll($select);
    }

    /**
     * Run all of the indexers.
     *
     * @return $this
     */
    protected function reindexAll() {
        $indexer = $this->_indexModelIndexer;

        // Delete all index events
        $index_events = $this->_resourceEventCollection;
        foreach ($index_events as $event) {
            /** @var \Magento\Index\Model\Event $event */
            $event->delete();
        }

        // Remove the stores cache from the category product index
        if ($process = $indexer->getProcessByCode("catalog_category_product")) {
            Ecom\Dev\Utils\Reflection::setRestrictedPropertyValue(
                $process->getIndexer()->getResource(), "_storesInfo", null
            );
        }

        $processes = $indexer->getProcessesCollection();

        // Reset all the indexers
        foreach ($processes as $process) {
            /** @var \Magento\Index\Model\Process $process */
            if ($process->hasData('runed_reindexall')) {
                $process->setData('runed_reindexall', false);
            }
        }

        // Run all indexers
        foreach ($processes as $process) {
            /** @var \Magento\Index\Model\Process $process */
            $process->reindexEverything();
        }

        return $this;
    }

    protected function getExpectedAutomaticAttributes() {
        return array(
            array(
                'klevu_attribute' => 'name',
                'magento_attribute' => 'name'),
            array(
                'klevu_attribute' => 'sku',
                'magento_attribute' => 'sku'),
            array(
                'klevu_attribute' => 'image',
                'magento_attribute' => 'image'),
            array(
                'klevu_attribute' => 'desc',
                'magento_attribute' => 'description'),
            array(
                'klevu_attribute' => 'shortDesc',
                'magento_attribute' => 'short_description'),
            array(
                'klevu_attribute' => 'salePrice',
                'magento_attribute' => 'price'),
            array(
                'klevu_attribute' => 'salePrice',
                'magento_attribute' => 'tax_class_id'),
            array(
                'klevu_attribute' => 'weight',
                'magento_attribute' => 'weight'),
        );
    }
    
    /**
     * Run special price prodcuts ids
     * @test
     * @loadFixture
     */
    public function testSpecialpriceProducts()
    {
        $model = $this->_modelProductSync;
        $expirySaleProductsIds = $model->getExpirySaleProductsIds();
        $model->markProductForUpdate();
        $this->assertEquals($this->getExpectedSpecialpriceProducts(), $expirySaleProductsIds);
    }
    
    /**
     * Run special price prodcuts ids
     * @test
     * @loadFixture
     */
    public function testCatalogruleProducts()
    {

        $model = $this->_modelProductSync;
        $catalogruleProductsIds = $model->getCatalogRuleProductsIds();
        $model->markProductForUpdate();
        $this->assertEquals($this->getExpectedSpecialpriceProducts(), $catalogruleProductsIds);
        
    }
    
    /**
     * Expected prodcuts ids
     */
    public function getExpectedSpecialpriceProducts()
    {
        return array(133);
        
    }

    protected function getDataFileContents($file) {
        $directory_tree = array(
            Mage::getModuleDir('', 'Klevu\Search'),
            'Test',
            'Model',
            'Api',
            'data',
            $file
        );

        $file_path = join(DS, $directory_tree);

        return file_get_contents($file_path);
    }

    protected function getPriceAttribute() {
        return $this->_modelEntityAttribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'price');
    }
    
    
}
