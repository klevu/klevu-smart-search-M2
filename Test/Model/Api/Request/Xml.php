<?php

namespace Klevu\Search\Test\Model\Api\Request;

class Xml extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Request\Xml
     */
    protected $_apiRequestXml;

    public function __construct(\Klevu\Search\Model\Api\Request\Xml $apiRequestXml)
    {
        $this->_apiRequestXml = $apiRequestXml;

        parent::__construct();
    }


    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testGetDataAsXml($data, $xml) {
        $request = $this->_apiRequestXml;

        $request->setData($data);

        $this->assertEquals($xml, preg_replace("/\n/", "", $request->getDataAsXml()));
    }
}
