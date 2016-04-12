<?php

namespace Klevu\Search\Test\Model\Api\Action;

class Getuserdetail extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    /**
     * @var \Klevu\Search\Model\Api\Action\Getuserdetail
     */
    protected $_apiActionGetuserdetail;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse, 
        \Klevu\Search\Model\Api\Action\Getuserdetail $apiActionGetuserdetail)
    {
        $this->_modelApiResponse = $modelApiResponse;
        $this->_apiActionGetuserdetail = $apiActionGetuserdetail;

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

        $action = $this->_apiActionGetuserdetail;
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

        $action = $this->_apiActionGetuserdetail;
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
            'email'    => "test@klevu.com",
            'password' => "password1"
        );
    }
}
