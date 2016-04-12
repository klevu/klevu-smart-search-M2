<?php

namespace Klevu\Search\Model\Api\Request;

class Xml extends \Klevu\Search\Model\Api\Request {

    public function __toString() {
        $string = parent::__toString();

        return sprintf("%s\n%s\n", $string, $this->getDataAsXml());
    }

    /**
     * Convert the request data into an XML string.
     *
     * @return string
     */
    public function getDataAsXml() {
        $xml = new \SimpleXMLElement("<request/>");
        $this->_convertArrayToXml($this->getData(), $xml);
        return $xml->asXML();
    }

    /**
     * Add data to the request as XML content and set the Content-Type to application/xml.
     *
     * @return \Zend\Http\Client
     */
    protected function build() {
        $client = parent::build();
		$convertDataToXml = $this->getDataAsXml();
        $gZen = gzencode($convertDataToXml,5);
		
        $requestHeaders  = $client->getRequest()->getHeaders();
			
	    if($gZen !== false) {
			$requestHeaders->addHeaders(array("Content-Encoding" => "gzip"));
			$requestHeaders->addHeaders(array("Content-Type" => "application/xml"));
            $client
                ->setHeaders($requestHeaders) 
                ->setRawBody($gZen);
        } else {
			$requestHeaders->addHeaders(array("Content-Type" => "application/xml"));
            $client
                ->setHeaders($requestHeaders) 
                ->setRawBody($convertDataToXml);
        }

        return $client;
    }

    /**
     * Convert the given array of data into a SimpleXMLElement. Uses array keys as XML element
     * names and values as element values, except for numeric keys where the element name gets
     * set to "item{numeric_key}" unless the value is an array in which case it gets added to
     * the parent XML element directly. Recursively descends into array values to convert them
     * into XML. For example:
     *
     * array(
     *     "sessionId" => "Klevu-ses-132123123123_123",
     *     "records" => array(
     *         0 => array(
     *             "record" => array(
     *                 "pairs" => array(
     *                     0 => array(
     *                         "pair" => array(
     *                             "key" => "id",
     *                             "value" => "1"
     *                         )
     *                     ),
     *                     1 => array(
     *                         "pair" => array(
     *                             "key" => "name",
     *                             "value" => "Test product"
     *                         )
     *                     )
     *                 )
     *             )
     *         ),
     *         1 => array(
     *             "record" => array(
     *                 "pairs" => array(
     *                     0 => array(
     *                         "pair" => array(
     *                             "key" => "id",
     *                             "value" => "1"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * );
     *
     * will get converted to:
     *
     * <?xml version="1.0"?>
     * <request>
     *     <sessionId>Klevu-ses-132123123123_123</sessionId>
     *     <records>
     *         <record>
     *             <pairs>
     *                 <pair>
     *                     <key>id</key>
     *                     <value>1</value>
     *                 </pair>
     *                 <pair>
     *                     <key>name</key>
     *                     <value>Test product</value>
     *                 </pair>
     *             </pairs>
     *         </record>
     *         <record>
     *             <pairs>
     *                 <pair>
     *                     <key>id</key>
     *                     <value>1</value>
     *                 </pair>
     *             </pairs>
     *         </record>
     *     </records>
     * </request>
     *
     * @param array            $array  The data to convert.
     * @param SimpleXmlElement $parent XML element used as a parent for the data.
     */
    protected function _convertArrayToXml(array $array, \SimpleXmlElement &$parent) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $this->_convertArrayToXml($value, $parent);
                } else {
                    $child = $parent->addChild($key);
                    $this->_convertArrayToXml($value, $child);
                }
            } else {
                $key = (is_numeric($key)) ? "item" . $key : $key;
                $parent->addChild($key, htmlspecialchars($value));
            }
        }
    }
}
