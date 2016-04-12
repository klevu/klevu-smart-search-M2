<?php

namespace Klevu\Search\Test\Model\Api\Response;

class Invalid extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response\Invalid
     */
    protected $_apiResponseInvalid;

    public function __construct(\Klevu\Search\Model\Api\Response\Invalid $apiResponseInvalid)
    {
        $this->_apiResponseInvalid = $apiResponseInvalid;

        parent::__construct();
    }


    /**
     * @test
     */
    public function testIsSuccessful() {
        $model = $this->_apiResponseInvalid;

        $this->assertEquals(
            false,
            $model->isSuccessful(),
            "Failed asserting that isSuccessful() returns false when no HTTP response is provided."
        );

        $model->setRawResponse(new \Zend\Http\Response(200, array()));

        $this->assertEquals(
            false,
            $model->isSuccessful(),
            "Failed asserting that isSuccessful() returns false when given a successful HTTP response."
        );

        $model->setRawResponse(new \Zend\Http\Response(500, array()));

        $this->assertEquals(
            false,
            $model->isSuccessful(),
            "Failed asserting that isSuccessful() returns false when given an unsuccessful HTTP response."
        );
    }
}
