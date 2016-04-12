<?php

namespace Klevu\Search\Test\Model;

class Notification extends Ecom\Dev\PHPUnit\Test\Case {
    /**
     * @var \Magento\Framework\Model\Resource
     */
    protected $_frameworkModelResource;

    /**
     * @var \Klevu\Search\Model\Notification
     */
    protected $_searchModelNotification;

    public function __construct(\Magento\Framework\App\ResourceConnection $frameworkModelResource, 
        \Klevu\Search\Model\Notification $searchModelNotification)
    {
        $this->_frameworkModelResource = $frameworkModelResource;
        $this->_searchModelNotification = $searchModelNotification;

        parent::__construct();
    }


    protected function tearDown() {
        $resource = $this->_frameworkModelResource;
        $resource->getConnection("core_write")->delete($resource->getTableName("klevu_search/notification"));

        parent::tearDown();
    }

    /**
     * @test
     * @loadFixture
     */
    public function testLoad() {
        $notification = $this->_searchModelNotification->load(1);

        $this->assertEquals(1, $notification->getId());
        $this->assertEquals("2014-05-13 11:08:00", $notification->getDate());
        $this->assertEquals("test", $notification->getType());
        $this->assertEquals("Testing", $notification->getMessage());
    }

    /**
     * @test
     */
    public function testSave() {
        $notification = $this->_searchModelNotification;

        $notification->setData(array(
            "type" => "test",
            "message" => "Testing"
        ));

        $notification->save();

        $this->assertNotNull($notification->getId());

        $result = $this->_searchModelNotification->load($notification->getId());

        $this->assertEquals($result->getId(), $result->getId());
        $this->assertNotNull($result->getDate());
        $this->assertEquals("test", $result->getType());
        $this->assertEquals("Testing", $result->getMessage());
    }
}
