<?php

namespace Klevu\Search\Model\Api\Action;

class Addrecords extends \Klevu\Search\Model\Api\Actionall {
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    public function __construct(\Klevu\Search\Model\Api\Response\Invalid $apiResponseInvalid, 
        \Klevu\Search\Helper\Api $searchHelperApi, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_apiResponseInvalid = $apiResponseInvalid;
        $this->_searchHelperApi = $searchHelperApi;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_searchHelperData = $searchHelperData;

    }


    const ENDPOINT = "/rest/service/addRecords";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL  = "Klevu\Search\Model\Api\Request\Xml";
    const DEFAULT_RESPONSE_MODEL = "Klevu\Search\Model\Api\Response\Message";

    // mandatory_field_name => allowed_empty
    protected $mandatory_fields = array(
        "id"               => false,
        "name"             => false,
        "url"              => false,
        "salePrice"        => false,
        "currency"         => false,
        "category"         => true,
        "listCategory"     => true
    );

    public function execute($parameters = array()) {
        $response = $this->getResponse();

        $validation_result = $this->validate($parameters);
        if ($validation_result !== true) {
            return $this->_apiResponseInvalid->setErrors($validation_result);
        }

        $skipped_records = $this->validateRecords($parameters);
        if (count($parameters['records']) > 0) {
            if ($skipped_records != null) {
                // Validation has removed some of the records due to errors, but the rest
                // can still be submitted, so just log and proceed
                $response->setData("skipped_records", $skipped_records);
            }
        } else {
            return $this->_apiResponseInvalid->setErrors(array(
                "all_records_invalid" => implode(", ", $skipped_records["messages"])
            ));
        }

        $this->prepareParameters($parameters);
        $endpoint = $this->buildEndpoint(static::ENDPOINT, $this->getStore(),$this->_searchHelperConfig->getRestHostname($this->getStore()));
        $request = $this->getRequest();
        $request
            ->setResponseModel($response)
            ->setEndpoint($endpoint)
            ->setMethod(static::METHOD)
            ->setData($parameters);

        return $request->send();
    }

    /**
     * Get the store used for this request.
     * @return \Magento\Framework\Model\Store
     */
    public function getStore() {
        if (!$this->hasData('store')) {
            $this->setData('store', $this->_storeModelStoreManagerInterface->getStore());
        }

        return $this->getData('store');
    }

    protected function validate($parameters) {
        $errors = array();

        if (!isset($parameters['sessionId']) || empty($parameters['sessionId'])) {
            $errors['sessionId'] = "Missing session ID";
        }

        if (!isset($parameters['records']) || !is_array($parameters['records']) || count($parameters['records']) == 0) {
            $errors['records'] = "No records";
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }

    /**
     * Validate the records parameter and remove records that are invalid. Modifies
     * the $parameters argument in place. Return the list of skipped records and
     * their error messages.
     *
     * @param $parameters
     *
     * @return array
     */
    protected function validateRecords(&$parameters) {
        if (isset($parameters['records']) && is_array($parameters['records'])) {
            $skipped_records = array(
                "index"         => array(),
                "messages"      => array()
            );

            foreach ($parameters['records'] as $i => $record) {
                $missing_fields = array();
                $empty_fields = array();

                foreach ($this->mandatory_fields as $mandatory_field => $allowed_empty) {
                    if (!array_key_exists($mandatory_field, $record)) {
                        $missing_fields[] = $mandatory_field;
                    } else {
                        if (!$allowed_empty && !is_numeric($record[$mandatory_field]) && empty($record[$mandatory_field])) {
                            $empty_fields[] = $mandatory_field;
                        }
                    }
                }

                $id = (isset($record['id']) && !empty($record['id'])) ? sprintf(" (id: %d)", $record['id']) : "";

                if (count($missing_fields) > 0 || count($empty_fields) > 0) {
                    unset($parameters["records"][$i]);
                    $skipped_records["index"][] = $i;
                    if (count($missing_fields) > 0) {
                        $skipped_records["messages"][] = sprintf("Record %d%s is missing mandatory fields: %s", $i, $id, implode(", ", $missing_fields));
                    }
                    if (count($empty_fields) > 0) {
                        $skipped_records["messages"][] = sprintf("Record %d%s has empty mandatory fields: %s", $i, $id, implode(", ", $empty_fields));
                    }
                }
            }

            return $skipped_records;
        }
    }

    /**
     * Convert the given parameters to a format expected by the XML request model.
     *
     * @param $parameters
     */
    protected function prepareParameters(&$parameters) {
        foreach ($parameters['records'] as &$record) {
            if (isset($record['listCategory']) && is_array($record['listCategory'])) {
                $record['listCategory'] = implode(";", $record['listCategory']);
            }

            if (isset($record['other']) && is_array($record['other'])) {
                $this->prepareOtherParameters($record);
            }

            if (isset($record['otherAttributeToIndex']) && is_array($record['otherAttributeToIndex'])) {
                $this->prepareOtherAttributeToIndexParameters($record);
            }
            
            if (isset($record['groupPrices']) && is_array($record['groupPrices'])) {
                $this->prepareGroupPricesParameters($record);
            }

            $pairs = array();

            foreach ($record as $key => $value) {
                $pairs[] = array(
                    'pair' => array(
                        'key' => $key,
                        'value' => $value
                    )
                );
            }

            $record = array(
                'record' => array(
                    'pairs' => $pairs
                )
            );
        }
    }

    /**
     * Flattens other parameters array to a string formatted: key:value[,value]
     * @param string
     */
    protected function prepareOtherParameters(&$record) {
        foreach ($record['other'] as $key => &$value) {
            $key = $this->sanitiseOtherAttribute($key);
            if(is_array($value)){
                $label = $this->sanitiseOtherAttribute($value['label']);
                $value = $this->sanitiseOtherAttribute($value['values']);
            }else {
                $label = $this->sanitiseOtherAttribute($key);
                $value = $this->sanitiseOtherAttribute($value);
            }

            if (is_array($value)) {
                $value = implode(",", $value);
            }

            $value = sprintf("%s:%s:%s", $key, $label, $value);
        }
        $record['other'] = implode(";", $record['other']);
    }

    /**
     * Flattens otherAttributeToIndex parameters array to a string formatted: key:value[,value]
     * @param string
     */
    protected function prepareOtherAttributeToIndexParameters(&$record) {
        foreach ($record['otherAttributeToIndex'] as $key => &$value) {
            $key = $this->sanitiseOtherAttribute($key);
            
            if(is_array($value)){
                $label = $this->sanitiseOtherAttribute($value['label']);
                $value = $this->sanitiseOtherAttribute($value['values']);
            }else {
                $label = $this->sanitiseOtherAttribute($key);
                $value = $this->sanitiseOtherAttribute($value);
            }
            
            if (is_array($value)) {
                $value = implode(",", $value);
            }

            $value = sprintf("%s:%s:%s", $key, $label, $value);
        }
        $record['otherAttributeToIndex'] = implode(";", $record['otherAttributeToIndex']);
    }
    
    
    /**
     * Flattens GroupPrices parameters array to a string formatted: key:value[,value]
     * @param string
     */
    protected function prepareGroupPricesParameters(&$record) {
        foreach ($record['groupPrices'] as $key => &$value) {
            $key = $this->sanitiseOtherAttribute($key);
            
            if(is_array($value)){
                $label = $this->sanitiseOtherAttribute($value['label']);
                $value = $this->sanitiseOtherAttribute($value['values']);
            }else {
                $label = $this->sanitiseOtherAttribute($key);
                $value = $this->sanitiseOtherAttribute($value);
            }
            
            if (is_array($value)) {
                $value = implode(",", $value);
            }

            $value = sprintf("%s:%s:%s", $key, $label, $value);
        }
        $record['groupPrices'] = implode(";", $record['groupPrices']);
    }

    /**
     * Remove the characters used to organise the other attribute values from the
     * passed in string.
     *
     * @param $value
     *
     * @return string
     */
    protected function sanitiseOtherAttribute($value) {
        return $this->_searchHelperData->santiseAttributeValue($value);
    }
	
	public function buildEndpoint($endpoint, $store = null, $hostname = null) {
       
        return static::ENDPOINT_PROTOCOL . (($hostname) ? $hostname : $this->_searchHelperConfig->getHostname($store)) . $endpoint;
    }
}
