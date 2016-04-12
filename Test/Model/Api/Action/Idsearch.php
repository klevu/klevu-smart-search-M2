<?php

namespace Klevu\Search\Test\Model\Api\Action;

class Idsearch extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    /**
     * @var \Klevu\Search\Model\Api\Action\Idsearch
     */
    protected $_apiActionIdsearch;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse, 
        \Klevu\Search\Model\Api\Action\Idsearch $apiActionIdsearch)
    {
        $this->_modelApiResponse = $modelApiResponse;
        $this->_apiActionIdsearch = $apiActionIdsearch;

        parent::__construct();
    }


    /**
     * Test that validation passes and successful response is received.
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

        $action = $this->_apiActionIdsearch;
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

        $action = $this->_apiActionIdsearch;
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
     */
    public function testValidationPaginationStartsFromLessThanZero() {
        $field = 'paginationStartsFrom';
        $parameters = $this->getTestParameters();
        $parameters[$field] = -1;

        $request = $this->getModelMock('klevu_search/api_request', array("send"));
        $request
            ->expects($this->never())
            ->method("send");

        $action = $this->_apiActionIdsearch;
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
            'ticket' => 'klevu-14255510895641069',
            'noOfResults' => 30,
            'term' => 'exam',
            'paginationStartsFrom' => 0,
            'ipAddress' => '127.0.0.1',
            'klevuSort' => 'rel',
            'enableFilters' => 1,
            'filterResults' => ''
        );
    }
}
