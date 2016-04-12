<?php

namespace Klevu\Search\Test\Model\Api\Action;

class Adduser extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    /**
     * @var \Klevu\Search\Model\Api\Action\Adduser
     */
    protected $_apiActionAdduser;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse, 
        \Klevu\Search\Model\Api\Action\Adduser $apiActionAdduser)
    {
        $this->_modelApiResponse = $modelApiResponse;
        $this->_apiActionAdduser = $apiActionAdduser;

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

        $action = $this->_apiActionAdduser;
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

        $action = $this->_apiActionAdduser;
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
            "email"    => "test@klevu.com",
            "password" => "password1",
            "url"      => "http://www.klevu.com/"
        );
    }
}
