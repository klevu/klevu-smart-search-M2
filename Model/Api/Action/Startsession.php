<?php

namespace Klevu\Search\Model\Api\Action;

class Startsession extends \Klevu\Search\Model\Api\Actionall {
    /**
     * @var \Klevu\Search\Model\Api\Response\Invalid
     */
    protected $_apiResponseInvalid;

    /**
     * @var \Klevu\Search\Helper\Api
     */
    protected $_searchHelperApi;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    public function __construct(\Klevu\Search\Model\Api\Response\Invalid $apiResponseInvalid, 
        \Klevu\Search\Helper\Api $searchHelperApi, 
        \Klevu\Search\Helper\Config $searchHelperConfig)
    {
        $this->_apiResponseInvalid = $apiResponseInvalid;
        $this->_searchHelperApi = $searchHelperApi;
        $this->_searchHelperConfig = $searchHelperConfig;

    }


    const ENDPOINT = "/rest/service/startSession";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL  = "Klevu\Search\Model\Api\Request\Xml";
    const DEFAULT_RESPONSE_MODEL = "Klevu\Search\Model\Api\Response\Message";

    public function execute($parameters) {
        $validation_result = $this->validate($parameters);
        if ($validation_result !== true) {
            return $this->_apiResponseInvalid->setErrors($validation_result);
        }

        $request = $this->getRequest();
        $endpoint = $this->buildEndpoint(static::ENDPOINT,$parameters['store'],$this->_searchHelperConfig->getRestHostname($parameters['store']));

        $request
            ->setResponseModel($this->getResponse())
            ->setEndpoint($endpoint)
            ->setMethod(static::METHOD)
            ->setHeader('Authorization',$parameters['api_key']);

        return $request->send();
    }

    protected function validate($parameters) {
        if (!isset($parameters['api_key']) || empty($parameters['api_key'])) {
            return array("Missing API key.");
        } else {
            return true;
        }
    }
    
    public function buildEndpoint($endpoint, $store = null, $hostname = null) {
       
        return static::ENDPOINT_PROTOCOL . (($hostname) ? $hostname : $this->_searchHelperConfig->getHostname($store)) . $endpoint;
    }

}
