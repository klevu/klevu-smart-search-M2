<?php

namespace Klevu\Search\Model\Api\Action;

class Producttracking extends \Klevu\Search\Model\Api\Actionall {
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

        //parent::__construct();
    }


    const ENDPOINT = "/analytics/productTracking";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL = "Klevu\Search\Model\Api\Request\Post";
    const DEFAULT_RESPONSE_MODEL = "Klevu\Search\Model\Api\Response\Data";

    protected function validate($parameters) {
        $errors = array();

        if (!isset($parameters["klevu_apiKey"]) || empty($parameters["klevu_apiKey"])) {
            $errors["klevu_apiKey"] = "Missing JS API key.";
        }

        if (!isset($parameters["klevu_type"]) || empty($parameters["klevu_type"])) {
            $errors["klevu_type"] = "Missing type.";
        }

        if (!isset($parameters["klevu_productId"]) || empty($parameters["klevu_productId"])) {
            $errors["klevu_productId"] = "Missing product ID.";
        }

        if (!isset($parameters["klevu_unit"]) || empty($parameters["klevu_unit"])) {
            $errors["klevu_unit"] = "Missing unit.";
        }

        if (!isset($parameters["klevu_salePrice"]) || empty($parameters["klevu_salePrice"])) {
            $errors["klevu_salePrice"] = "Missing sale price.";
        }

        if (!isset($parameters["klevu_currency"]) || empty($parameters["klevu_currency"])) {
            $errors["klevu_currency"] = "Missing currency.";
        }


        if (count($errors) == 0) {
            return true;
        }
        return $errors;
    }


    /**
     * Execute the API action with the given parameters.
     *
     * @param array $parameters
     *
     * @return \Klevu\Search\Model\Api\Response
     */
    public function execute($parameters = array()) {
        $validation_result = $this->validate($parameters);
        if ($validation_result !== true) {
            return $this->_apiResponseInvalid->setErrors($validation_result);
        }

        $request = $this->getRequest();

        $endpoint = $this->buildEndpoint(
            static::ENDPOINT,
            $this->getStore(),
            $this->_searchHelperConfig->getAnalyticsUrl()
        );

        $request
            ->setResponseModel($this->getResponse())
            ->setEndpoint($endpoint)
            ->setMethod(static::METHOD)
            ->setData($parameters);

        return $request->send();
    }
	
	public function buildEndpoint($endpoint, $store = null, $hostname = null) {
       
        return static::ENDPOINT_PROTOCOL . (($hostname) ? $hostname : $this->_searchHelperConfig->getHostname($store)) . $endpoint;
    }
}
