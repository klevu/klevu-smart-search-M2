<?php

namespace Klevu\Search\Model\Api\Action;

class Searchtermtracking extends \Klevu\Search\Model\Api\Actionall {
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


    const ENDPOINT = "/analytics/n-search/search";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL = "Klevu\Search\Model\Api\Request\Post";
    const DEFAULT_RESPONSE_MODEL = "Klevu\Search\Model\Api\Response\Data";


    protected function validate($parameters) {
        $errors = array();

        if (!isset($parameters["klevu_apiKey"]) || empty($parameters["klevu_apiKey"])) {
            $errors["klevu_apiKey"] = "Missing JS API key.";
        }
        if (!isset($parameters["klevu_term"]) || empty($parameters["klevu_term"])) {
            $errors["klevu_term"] = "Missing klevu term.";
        }
        if (!isset($parameters["klevu_totalResults"]) || empty($parameters["klevu_totalResults"])) {
            $errors["klevu_type"] = "Missing Total Results.";
        }

        if (!isset($parameters["klevu_shopperIP"]) || empty($parameters["klevu_shopperIP"])) {
            $errors["klevu_shopperIP"] = "Missing klevu shopperIP.";
        }

        if (!isset($parameters["klevu_typeOfQuery"]) || empty($parameters["klevu_typeOfQuery"])) {
            $errors["klevu_unit"] = "Missing Type of Query.";
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

        $endpoint = $this->_searchHelperApi->buildEndpoint(
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
}
