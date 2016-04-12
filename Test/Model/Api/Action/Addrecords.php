<?php

namespace Klevu\Search\Test\Model\Api\Action;

class Addrecords extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Action\Addrecords
     */
    protected $_apiActionAddrecords;

    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    public function __construct(\Klevu\Search\Model\Api\Action\Addrecords $apiActionAddrecords, 
        \Klevu\Search\Model\Api\Response $modelApiResponse)
    {
        $this->_apiActionAddrecords = $apiActionAddrecords;
        $this->_modelApiResponse = $modelApiResponse;

        parent::__construct();
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

        $action = $this->_apiActionAddrecords;
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
     * @test
     * @dataProvider dataProvider
     */
    public function testValidateRequiredFieldsRecords($field) {
        $parameters = $this->getTestParameters();
        unset($parameters['records'][0][$field]);

        $request = $this->getModelMock('klevu_search/api_request', array("send"));
        $request
            ->expects($this->never())
            ->method("send");

        $action = $this->_apiActionAddrecords;
        $action
            ->setRequest($request);

        $response = $action->execute($parameters);

        $this->assertInstanceOf(
            "Klevu\Search\Model\Api\Response\Invalid",
            $response,
            sprintf("Failed to assert that validation fails when one of the records is missing a %s field.", $field)
        );
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testValidateRequiredFieldsRecordsOptional($field) {
        $parameters = $this->getTestParameters();
        $parameters['records'][0][$field] = "";

        $response = $this->_modelApiResponse;
        $response
            ->setRawResponse(new \Zend\Http\Response(200, array(), "Test response"));

        $request = $this->getModelMock('klevu_search/api_request', array("send"));
        $request
            ->expects($this->once())
            ->method("send")
            ->will($this->returnValue($response));

        $action = $this->_apiActionAddrecords;
        $action
            ->setRequest($request);

        $this->assertEquals(
            $response,
            $action->execute($parameters),
            sprintf("Failed to assert that validation passes when one of the records is missing the optional %s field.", $field)
        );
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testValidateRequiredFieldsRecordsEmpty($field) {
        $parameters = $this->getTestParameters();
        $parameters['records'][0][$field] = "";

        $request = $this->getModelMock('klevu_search/api_request', array("send"));
        $request
            ->expects($this->never())
            ->method("send");

        $action = $this->_apiActionAddrecords;
        $action
            ->setRequest($request);

        $response = $action->execute($parameters);

        $this->assertInstanceOf(
            "Klevu\Search\Model\Api\Response\Invalid",
            $response,
            sprintf("Failed to assert that validation fails when one of the records as an empty %s field.", $field)
        );
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testValidateRequiredFieldsRecordsAllowedEmpty($field) {
        $parameters = $this->getTestParameters();
        $parameters['records'][0][$field] = "";

        $response = $this->_modelApiResponse;
        $response
            ->setRawResponse(new \Zend\Http\Response(200, array(), "Test response"));

        $request = $this->getModelMock('klevu_search/api_request', array("send"));
        $request
            ->expects($this->once())
            ->method("send")
            ->will($this->returnValue($response));

        $action = $this->_apiActionAddrecords;
        $action
            ->setRequest($request);

        $this->assertEquals(
            $response,
            $action->execute($parameters),
            sprintf("Failed to assert that validation passes when one of the records has an empty %s field.", $field)
        );
    }

    protected function getTestParameters() {
        return array(
            'sessionId' => "Klevu-session-1234567890",
            'records' => array(
                array(
                    'id' => "1",
                    'name' => "Test Product",
                    'url'  => "http://box.klevu.com/",
                    'image' => "http://box.klevu.com/image.jpg",
                    'salePrice' => "19.99",
                    'startPrice' => "15.99",
                    'toPrice' => "29.99",
                    'currency' => "GBP",
                    'shortDesc' => "A test product",
                    'desc' => "A longer description of the test product",
                    'category' => "Tablets",
                    'listCategory' => array("Electronics", "Tablets", "Sale"),
                    'inStock' => "yes",
                    'brand' => "Klevu",
                    'model' => "X-300",
                    'color' => "blue",
                    'size'  => "N/A",
                    'weight' => "100",
                    'other' => array(
                        'modes' => array(
                            'label' => 'Modes',
                            'values' => array("fast", "eco", "precise")
                        )
                    )
                )
            )
        );
    }
}
