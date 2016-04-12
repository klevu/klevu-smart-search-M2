<?php

namespace Klevu\Search\Test\Model\Api\Response;

class Timezone extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response\Timezone
     */
    protected $_apiResponseTimezone;

    public function __construct(\Klevu\Search\Model\Api\Response\Timezone $apiResponseTimezone)
    {
        $this->_apiResponseTimezone = $apiResponseTimezone;

        parent::__construct();
    }


    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testIsSuccessful($response_code, $response_data_file, $is_successful) {
        $http_response = new \Zend\Http\Response($response_code, array(), $this->getDataFileContents($response_data_file));

        $model = $this->_apiResponseTimezone;
        $model->setRawResponse($http_response);

        $this->assertEquals($is_successful, $model->isSuccessful());
    }
}
