<?php

namespace Klevu\Search\Test\Model\Api\Action;

class Producttracking extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    /**
     * @var \Klevu\Search\Model\Api\Action\Producttracking
     */
    protected $_apiActionProducttracking;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse, 
        \Klevu\Search\Model\Api\Action\Producttracking $apiActionProducttracking)
    {
        $this->_modelApiResponse = $modelApiResponse;
        $this->_apiActionProducttracking = $apiActionProducttracking;

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

        $action = $this->getModel();
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

        $action = $this->getModel();
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

    /**
     * Return the model being tested.
     *
     * @return \Klevu\Search\Model\Api\Action\Producttracking
     */
    protected function getModel() {
        return $this->_apiActionProducttracking;
    }

    protected function getTestParameters() {
        return array(
            'klevu_apiKey'    => "test-api-key",
            'klevu_type'      => "checkout",
            'klevu_productId' => 1,
            'klevu_unit'      => 1,
            'klevu_salePrice' => 100.00,
            'klevu_currency'  => "GBP",
        );
    }

}
