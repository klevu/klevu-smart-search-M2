<?php

namespace Klevu\Search\Test\Model\Api\Response;

class Data extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response\Data
     */
    protected $_apiResponseData;

    public function __construct(\Klevu\Search\Model\Api\Response\Data $apiResponseData)
    {
        $this->_apiResponseData = $apiResponseData;

        parent::__construct();
    }


    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testIsSuccessful($response_code, $response_data_file, $is_successful) {
        $http_response = new \Zend\Http\Response($response_code, array(), $this->getDataFileContents($response_data_file));

        $model = $this->_apiResponseData;
        $model->setRawResponse($http_response);

        $this->assertEquals($is_successful, $model->isSuccessful());
    }

    /**
     * @test
     */
    public function testData() {
        $http_response = new \Zend\Http\Response(200, array(), $this->getDataFileContents("data_response_data.xml"));

        $model = $this->_apiResponseData;
        $model->setRawResponse($http_response);

        $this->assertEquals("test", $model->getTest(), "Failed asserting that data gets set on the response.");
        $this->assertNull($model->getResponse(), "Failed asserting that 'response' element gets removed from data.");
        $this->assertEquals("test", $model->getCamelCase(), "Failed asserting that data keys get converted from camel case.");
    }
}
