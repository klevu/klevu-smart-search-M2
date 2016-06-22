<?php

namespace Klevu\Search\Model\Api;

class Actionall extends \Magento\Framework\DataObject {
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
    
    /**
     * @var \Klevu\Search\Model\Api
     */
    protected $_searchModelApi;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    public function __construct(\Klevu\Search\Model\Api\Response\Invalid $apiResponseInvalid, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface
        )
    {
        $this->_apiResponseInvalid = $apiResponseInvalid;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;

    }


    const ENDPOINT = "";
    const METHOD   = "GET";
    const ENDPOINT_PROTOCOL = 'https://';
    const ENDPOINT_DEFAULT_HOSTNAME = 'box.klevu.com';

    const DEFAULT_REQUEST_MODEL = "Klevu\Search\Model\Api\Request";
    const DEFAULT_RESPONSE_MODEL = "Klevu\Search\Model\Api\Response";


    /** @var \Klevu\Search\Model\Api\Request $request */
    protected $request;

    /** @var \Klevu\Search\Model\Api\Response $response */
    protected $response;

    /**
     * Set the request model to use for this API action.
     *
     * @param \Klevu\Search\Model\Api\Request $request_model
     *
     * @return $this
     */
    public function setRequest(\Klevu\Search\Model\Api\Request $request_model) {
        $this->request = $request_model;

        return $this;
    }

    /**
     * Return the request model used for this API action.
     *
     * @return \Klevu\Search\Model\Api\Request
     */
    public function getRequest() {
        if (!$this->request) {
            $this->request = \Magento\Framework\App\ObjectManager::getInstance()->get(static::DEFAULT_REQUEST_MODEL);
        }

        return $this->request;
    }

    /**
     * Set the response model to use for this API action.
     *
     * @param \Klevu\Search\Model\Api\Response $response_model
     *
     * @return $this
     */
    public function setResponse(\Klevu\Search\Model\Api\Response $response_model) {
        $this->response = $response_model;

        return $this;
    }

    /**
     * Return the response model used for this API action.
     *
     * @return \Klevu\Search\Model\Api\Response
     */
    public function getResponse() {
        if (!$this->response) {
            $this->response = \Magento\Framework\App\ObjectManager::getInstance()->get(static::DEFAULT_RESPONSE_MODEL);
        }

        return $this->response;
    }

    /**
     * Execute the API action with the given parameters.
     *
     * @param array $parameters
     *
     * @return \Klevu\Search\Model\Api\Response
     */
    public function execute($parameters) {
        $validation_result = $this->validate($parameters);
        if ($validation_result !== true) {
            return $this->_apiResponseInvalid->setErrors($validation_result);
        }

        $request = $this->getRequest();

        $endpoint = $this->buildEndpoint(static::ENDPOINT, $this->_storeModelStoreManagerInterface->getStore(),$this->_searchHelperConfig->getHostname($this->_storeModelStoreManagerInterface->getStore()));
        $request
            ->setResponseModel($this->getResponse())
            ->setEndpoint($endpoint)
            ->setMethod(static::METHOD)
            ->setData($parameters);

        return $request->send();
    }

    /**
     * Get the store used for this request
     * @return \Magento\Framework\Model\Store
     */
    public function getStore() {
        if(!$this->hasData('store')) {
            $this->setData('store', $this->_storeModelStoreManagerInterface->getStore());
        }
        return $this->getData('store');
    }

    /**
     * Validate the given parameters against the API action specification and
     * return true if validation passed or an array of validation error messages
     * otherwise.
     *
     * @param $parameters
     *
     * @return bool|array
     */
    protected function validate($parameters) {
        return true;
    }
    
    public function buildEndpoint($endpoint, $store = null, $hostname = null) {
        return static::ENDPOINT_PROTOCOL . (($hostname) ? $hostname : $this->_searchHelperConfig->getHostname($store)) . $endpoint;
    }
}
