<?php

namespace Klevu\Search\Test\Model\Api\Action;

class Addwebstore extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    /**
     * @var \Klevu\Search\Model\Api\Action\Addwebstore
     */
    protected $_apiActionAddwebstore;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse, 
        \Klevu\Search\Model\Api\Action\Addwebstore $apiActionAddwebstore)
    {
        $this->_modelApiResponse = $modelApiResponse;
        $this->_apiActionAddwebstore = $apiActionAddwebstore;

        parent::__construct();
    }


    /**
     * @test
     */
    public function testValidate() {
        $parameters = $this->getTestParameters();

        $response = $this->_modelApiResponse;
        $response->setRawResponse(new \Zend\Http\Response(200, array(), "Test response"));

        $request = $this->getModelMock('klevu_search/api_request', array("send"));
        $request
            ->expects($this->once())
            ->method("send")
            ->will($this->returnValue($response));

        $action = $this->_apiActionAddwebstore;
        $action
            ->setRequest($request);

        $this->assertEquals($response, $action->execute($parameters));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testValidateRequiredFields($field) {
        $parameters = $this->getTestParameters();
        unset($parameters[$field]);

        $request = $this->getModelMock('klevu_search/api_request', array("send"));
        $request
            ->expects($this->never())
            ->method("send");

        $action = $this->_apiActionAddwebstore;
        $action
            ->setRequest($request);

        $response = $action->execute($parameters);

        $this->assertInstanceOf("Klevu\Search\Model\Api\Response\Invalid", $response);

        $this->assertArrayHasKey(
            $field,
            $response->getErrors(),
            sprintf("Failed to assert that an error is returned for %s parameter.", $field)
        );
    }

    protected function getTestParameters() {
        return array(
            "customerId" => "42",
            "testMode"   => "true",
            "storeName"  => "Test Store",
            "language"   => "en",
            "timezone"   => "Europe/London",
            "version"    => "1.0.0",
            "locale"     => "en_GB",
            "country"    => "GB"
        );
    }
}
