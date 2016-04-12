<?php
namespace Klevu\Search\Model\Api\Action;

class Debuginfo extends \Klevu\Search\Model\Api\Action {
    /**
     * @var \Klevu\Search\Helper\Api
     */
    protected $_searchHelperApi;

    public function __construct(\Klevu\Search\Helper\Api $searchHelperApi)
    {
        $this->_searchHelperApi = $searchHelperApi;

        parent::__construct();
    }
  
  
    const ENDPOINT = "/n-search/logReceiver";
    const METHOD   = "POST";
    
    const DEFAULT_REQUEST_MODEL  = "klevu_search/api_request_post";
    const DEFAULT_RESPONSE_MODEL = "klevu_search/api_response_data";
	
	public function debugKlevu($parameters)
	{
       $endpoint = $this->_searchHelperApi->buildEndpoint(static::ENDPOINT);
	   $response = $this->getResponse();
	   $request = $this->getRequest();
       $request
            ->setResponseModel($response)
            ->setEndpoint($endpoint)
            ->setMethod(static::METHOD)
            ->setData($parameters);
        return $request->send();
       
	
	}
}
