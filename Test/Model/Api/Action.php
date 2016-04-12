<?php

namespace Klevu\Search\Test\Model\Api;

class Action extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    /**
     * @var \Klevu\Search\Model\Api\Action
     */
    protected $_modelApiAction;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse, 
        \Klevu\Search\Model\Api\Action $modelApiAction)
    {
        $this->_modelApiResponse = $modelApiResponse;
        $this->_modelApiAction = $modelApiAction;

        parent::__construct();
    }


    /**
     * @test
     */
    public function testExecute() {
        $response_model = $this->_modelApiResponse;
        $response_model
            ->setRawResponse(new \Zend\Http\Response(200, array(), "Test response"));

        $request_model = $this->getModelMock('klevu_search/api_request', array("send"));
        $request_model
            ->expects($this->once())
            ->method("send")
            ->will($this->returnValue($response_model));

        $action = $this->_modelApiAction;
        $action
            ->setRequest($request_model)
            ->setResponse($response_model);

        $this->assertEquals($response_model, $action->execute());
    }

    /**
     * @test
     */
    public function testValidate() {
        $errors = array("Test error", "Another test error");

        $action = $this->getModelMock('klevu_search/api_action', array("validate"));
        $action
            ->expects($this->once())
            ->method("validate")
            ->will($this->returnValue($errors));

        $request_model = $this->getModelMock('klevu_search/api_request', array("send"));
        $request_model
            ->expects($this->never())
            ->method("send");

        $action->setRequest($request_model);

        $response = $action->execute();

        $this->assertInstanceOf($this->getGroupedClassName("model", "klevu_search/api_response_invalid"), $response);
        $this->assertEquals(
            $errors,
            $response->getErrors(),
            "Returned response model does not contain the validation errors expected."
        );
    }
}
