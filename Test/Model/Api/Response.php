<?php

namespace Klevu\Search\Test\Model\Api;

class Response extends \Klevu\Search\Test\Model\Api\Test\Case {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse)
    {
        $this->_modelApiResponse = $modelApiResponse;

        parent::__construct();
    }


    /**
     * @test
     * @dataProvider dataProvider
     * @dataProviderFile response_testIsSuccessful.yaml
     */
    public function testIsSuccessful($response_code, $response_data_file, $is_successful) {
        $response_body = ($response_data_file) ? $this->getDataFileContents($response_data_file) : "";
        $http_response = new \Zend\Http\Response($response_code, array(), $response_body);

        $model = $this->_modelApiResponse;
        $model->setRawResponse($http_response);

        $this->assertEquals($is_successful, $model->isSuccessful());
    }
}
