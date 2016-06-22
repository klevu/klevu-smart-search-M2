<?php
namespace Klevu\Search\Model\Api\Action;

class Removetestmode extends \Klevu\Search\Model\Api\Actionall {
    /**
     * @var \Klevu\Search\Helper\Api
     */
    protected $_searchHelperApi;

    public function __construct(\Klevu\Search\Helper\Api $searchHelperApi)
    {
        $this->_searchHelperApi = $searchHelperApi;

    }
  
  
    const ENDPOINT = "/n-search/changeWebstoreMode";
    const METHOD   = "POST";
    
    const DEFAULT_REQUEST_MODEL  = "klevu_search/api_request_post";
    const DEFAULT_RESPONSE_MODEL = "klevu_search/api_response_data";
	
	public function removeTestMode($parameters)
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
