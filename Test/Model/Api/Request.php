<?php

namespace Klevu\Search\Test\Model\Api;

class Request extends Ecom\Dev\PHPUnit\Test\Case {

    /**
     * @test
     * @expectedException \Magento\Framework\Exception
     */
    public function testSendNoEndpoint() {
        $model = $this->getModelMock('klevu_search/api_request', array("build"));
        $model
            ->expects($this->never())
            ->method("build");

        $model->send(); // Should throw an exception
    }

    /**
     * @test
     */
    public function testSendNoResponse() {
        $http_client = $this->getMock("Zend\Http\Client", array("request"));
        $http_client
            ->expects($this->once())
            ->method("request")
            ->will($this->throwException(new \Zend\Http\Client\Exception("Test exception")));

        $model = $this->getModelMock('klevu_search/api_request', array("build"));
        $model
            ->expects($this->once())
            ->method("build")
            ->will($this->returnValue($http_client));

        $model->setEndpoint("http://test.klevu.com/");

        $this->assertInstanceOf("Klevu\Search\Model\Api\Response\Empty", $model->send());
    }

    /**
     * @test
     */
    public function testSendValidResponse() {
        $test_raw_response = new \Zend\Http\Response(200, array());

        $http_client = $this->getMock("Zend\Http\Client", array("request"));
        $http_client
            ->expects($this->once())
            ->method("request")
            ->will($this->returnValue($test_raw_response));

        $response_model = $this->getModelMock('klevu_search/api_response', array("setRawResponse"));
        $response_model
            ->expects($this->once())
            ->method("setRawResponse")
            ->with($this->equalTo($test_raw_response));

        $request_model = $this->getModelMock('klevu_search/api_request', array("build"));
        $request_model
            ->expects($this->once())
            ->method("build")
            ->will($this->returnValue($http_client));

        $request_model
            ->setEndpoint("http://test.klevu.com/")
            ->setResponseModel($response_model);

        $this->assertInstanceOf("Klevu\Search\Model\Api\Response", $request_model->send());
    }
}
