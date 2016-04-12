<?php

namespace Klevu\Search\Test\Model\Api\Action;

class Startsession extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    /**
     * @var \Klevu\Search\Model\Api\Action\Startsession
     */
    protected $_apiActionStartsession;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse, 
        \Klevu\Search\Model\Api\Action\Startsession $apiActionStartsession)
    {
        $this->_modelApiResponse = $modelApiResponse;
        $this->_apiActionStartsession = $apiActionStartsession;

        parent::__construct();
    }


    public function testValidate() {
        $parameters = array(
            'api_key' => "dGVzdC1hcGkta2V5",
            'store'   => null
        );

        $response = $this->_modelApiResponse;
        $response->setRawResponse(new \Zend\Http\Response(200, array(), "Test response"));

        $request = $this->getModelMock('klevu_search/api_request', array("send"));
        $request
            ->expects($this->once())
            ->method("send")
            ->will($this->returnValue($response));

        $action = $this->_apiActionStartsession;
        $action
            ->setRequest($request);

        $this->assertEquals($response, $action->execute($parameters));

        $returned_response = $action->execute(array());

        $this->assertInstanceOf("Klevu\Search\Model\Api\Response\Invalid", $returned_response);
        $this->assertEquals(array("Missing API key."), $returned_response->getErrors());
    }
}
