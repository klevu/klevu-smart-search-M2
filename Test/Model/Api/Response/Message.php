<?php

namespace Klevu\Search\Test\Model\Api\Response;

class Message extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response\Message
     */
    protected $_apiResponseMessage;

    public function __construct(\Klevu\Search\Model\Api\Response\Message $apiResponseMessage)
    {
        $this->_apiResponseMessage = $apiResponseMessage;

        parent::__construct();
    }


    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testIsSuccessful($response_code, $response_data_file, $is_successful) {
        $http_response = new \Zend\Http\Response($response_code, array(), $this->getDataFileContents($response_data_file));

        $model = $this->_apiResponseMessage;
        $model->setRawResponse($http_response);

        $this->assertEquals($is_successful, $model->isSuccessful());
    }

    /**
     * @test
     */
    public function testGetSessionId() {
        $http_response = new \Zend\Http\Response(200, array(), $this->getDataFileContents("message_response_session_id.xml"));

        $model = $this->_apiResponseMessage;
        $model->setRawResponse($http_response);

        $this->assertEquals("Klevu-session-1234567890", $model->getSessionId());
    }
}
