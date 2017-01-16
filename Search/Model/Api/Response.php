<?php

/**
 * Class \Klevu\Search\Model\Api\Response
 *
 * @method setMessage($message)
 * @method getMessage()
 */
namespace Klevu\Search\Model\Api;

class Response extends \Magento\Framework\DataObject {
    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    public function __construct(\Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_searchHelperData = $searchHelperData;

        parent::__construct();
    }


    protected $raw_response;

    protected $successful;
    protected $xml;

    public function _construct() {
        parent::_construct();

        $this->successful = false;
    }

    /**
     * Set the raw response object representing this API response.
     *
     * @param \Zend\Http\Response $response
     *
     * @return $this
     */
    public function setRawResponse(\Zend\Http\Response $response) {
        $this->raw_response = $response;

        $this->parseRawResponse($response);

        return $this;
    }

    /**
     * Check if the API response indicates success.
     *
     * @return boolean
     */
    public function isSuccess() {
        return $this->successful;
    }

    /**
     * Return the response XML content.
     *
     * @return SimpleXMLElement
     */
    public function getXml() {
        return $this->xml;
    }

    /**
     * Extract the API response data from the given HTTP response object.
     *
     * @param \Zend\Http\Response $response
     *
     * @return $this
     */
    protected function parseRawResponse(\Zend\Http\Response $response) {
        if ($response->isSuccess()) {
            $content = $response->getBody();
            if (strlen($content) > 0) {
                try {
                    $xml = simplexml_load_string($response->getBody());
                } catch (\Exception $e) {
                    // Failed to parse XML
                    $this->successful = false;
                    $this->setMessage("Failed to parse a response from Klevu.");
                    $this->_searchHelperData->log(\Zend\Log\Logger::ERR, sprintf("Failed to parse XML response: %s", $e->getMessage()));
                    return $this;
                }

                $this->xml = $xml;
                $this->successful = true;
            } else {
                // Response contains no content
                $this->successful = false;
                $this->setMessage('Failed to parse a response from Klevu.');
                $this->_searchHelperData->log(\Zend\Log\Logger::ERR, "API response content is empty.");
            }
        } else {
            // Unsuccessful HTTP response
            $this->successful = false;
            switch ($response->getStatusCode()) {
                case 403:
                    $message = "Incorrect API keys.";
                    break;
                case 500:
                    $message = "API server error.";
                    break;
                case 503:
                    $message = "API server unavailable.";
                    break;
                default:
                    $message = "Unexpected error.";
            }
            $this->setMessage(sprintf("Failed to connect to Klevu: %s", $message));
            $this->_searchHelperData->log(\Zend\Log\Logger::ERR, sprintf("Unsuccessful HTTP response: %s %s", $response->getStatusCode(), $response->toString()));
        }

        return $this;
    }
}
