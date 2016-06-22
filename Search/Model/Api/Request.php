<?php

namespace Klevu\Search\Model\Api;

class Request extends \Magento\Framework\DataObject {
    /**
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_modelApiResponse;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Klevu\Search\Model\Api\Response\Empty
     */
    protected $_apiResponseEmpty;

    public function __construct(\Klevu\Search\Model\Api\Response $modelApiResponse, 
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Klevu\Search\Model\Api\Response\Rempty $apiResponseEmpty)
    {
        $this->_modelApiResponse = $modelApiResponse;
        $this->_searchHelperData = $searchHelperData;
        $this->_apiResponseEmpty = $apiResponseEmpty;

        parent::__construct();
    }


    protected $endpoint;

    protected $method;

    protected $headers;

    protected $response_model;

    public function _construct() {
        parent::_construct();

        $this->method = \Zend\Http\Client::GET;
        $this->headers = array();
        $this->response_model = $this->_modelApiResponse;
    }

    /**
     * Set the target endpoint URL for this API request.
     *
     * @param $url
     *
     * @return $this
     */
    public function setEndpoint($url) {
        $this->endpoint = $url;

        return $this;
    }

    /**
     * Return the target endpoint for this API request.
     *
     * @return string
     */
    public function getEndpoint() {
        return $this->endpoint;
    }

    /**
     * Set the HTTP method to use for this API request.
     *
     * @param $method
     *
     * @return $this
     */
    public function setMethod($method) {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the HTTP method configured for this API request.
     *
     * @return mixed
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Set a HTTP header for this API request.
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setHeader($name, $value) {
        $this->headers = array($name => $value);

        return $this;
    }

    /**
     * Get the array of HTTP headers configured for this API request.
     *
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Set the response model to use for this API request.
     *
     * @param \Klevu\Search\Model\Api\Response $response_model
     *
     * @return $this
     */
    public function setResponseModel(\Klevu\Search\Model\Api\Response $response_model) {
        $this->response_model = $response_model;

        return $this;
    }

    /**
     * Return the response model used for this API request.
     *
     * @return \Klevu\Search\Model\Api\Response
     */
    public function getResponseModel() {
        return $this->response_model;
    }

    /**
     * Perform the API request and return the received response.
     *
     * @return \Klevu\Search\Model\Api\Response
     */
    public function send() {
        if (!$this->getEndpoint()) {
            // Can't make a request without a URL
            throw new Exception("Unable to send a Klevu Search API request: No URL specified.");
        }

        $raw_request = $this->build();

        $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("API request:\n%s", $this->__toString()));

        try {
            $raw_response = $raw_request->send();
            

        } catch (\Zend\Http\Client\Exception $e) {
            // Return an empty response
            $this->_searchHelperData->log(\Zend\Log\Logger::ERR, sprintf("HTTP error: %s", $e->getMessage()));
            return $this->_apiResponseEmpty;
        }

        $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf(
            "API response:\n%s",
            //$raw_response->getHeadersAsString(true, "\n"),
            $raw_response->getBody()
        ));

        $response = $this->getResponseModel();
        $response->setRawResponse($raw_response);

        return $response;
    }

    /**
     * Return the string representation of the API request.
     *
     * @return string
     */
    public function __toString() {
        $headers = $this->getHeaders();
        if (count($headers) > 0) {
            array_walk($headers, function (&$value, $key) {
                $value = ($value !== null && $value !== false) ? sprintf("%s: %s", $key, $value) : null;
            });
        }

		if($headers != NULL) {
			return sprintf("%s %s\n%s\n",
				$this->getMethod(),
				$this->getEndpoint(),
				implode("\n", array_filter($headers))
			);
		}
    }

    /**
     * Build the HTTP request to be sent.
     *
     * @return \Zend\Http\Client
     */
    protected function build() {
        $client = new \Zend\Http\Client();
        if(!empty($this->getHeaders())) {
            $client
                ->setUri($this->getEndpoint())
                ->setMethod($this->getMethod())
				->setOptions(array('sslverifypeer' => false))
                ->setHeaders($this->getHeaders());
        } else {
            $client
                ->setUri($this->getEndpoint())
				->setOptions(array('sslverifypeer' => false))
                ->setMethod($this->getMethod());
        }

        return $client;
    }
}
