<?php

namespace Klevu\Search\Model\Api\Action;

class Features extends \Klevu\Search\Model\Api\Actionall {
    /**
     * @var \Klevu\Search\Model\Api\Response\Invalid
     */
    protected $_apiResponseInvalid;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_frameworkModelStore;

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
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Klevu\Search\Helper\Data $searchHelperData,
		\Magento\Store\Model\Store $frameworkModelStore
		)
    {
        $this->_apiResponseInvalid = $apiResponseInvalid;
        $this->_searchHelperApi = $searchHelperApi;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_searchHelperData = $searchHelperData;
		$this->_frameworkModelStore = $frameworkModelStore;

    }



    const ENDPOINT = "/uti/getFeaturesAndUpgradeLink";
    const METHOD   = "POST";
    const DEFAULT_REQUEST_MODEL = "Klevu\Search\Model\Api\Request\Post";
    const DEFAULT_RESPONSE_MODEL = "Klevu\Search\Model\Api\Response\Data";
    
    protected function validate($parameters) {

        $errors = array();
       
        if (!isset($parameters["restApiKey"]) || empty($parameters["restApiKey"])) {
            $errors["restApiKey"] = "Missing Rest API key.";
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
        $store = $this->_frameworkModelStore->load($parameters['store']);
        $endpoint = $this->buildEndpoint(static::ENDPOINT, $store,$this->_searchHelperConfig->getTiresUrl($store));
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
