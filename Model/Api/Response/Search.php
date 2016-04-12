<?php

namespace Klevu\Search\Model\Api\Response;

class Search extends \Klevu\Search\Model\Api\Response {

    protected function parseRawResponse(\Zend\Http\Response $response) {
        parent::parseRawResponse($response);

        if ($this->isSuccessful()) {
            $data = $this->xmlToArray($this->getXml());

            $this->successful = false;
            if (isset($data['response'])) {
                if (strtolower($data['response']) == 'success') {
                    $this->successful = true;
                }
                unset($data['response']);
            }

            foreach ($data as $key => $value) {
                switch($key) {
                    case 'result':
                        $prepared_value = $value;
                        if (isset($value['id'])) {
                            $prepared_value = array($value);
                        }

                        break;
                    case 'filters':
                        if (isset($value['filter'])) {
                            $prepared_value = $this->_prepareFilters($value['filter']);
                        } else {
                            $prepared_value ='';
                        }
                        break;
                    default:
                        $prepared_value = $value;
                        break;
                }

                $this->setData($this->_underscore($key), $prepared_value);
            }
        }

        return $this;
    }

    protected function _prepareFilters($filters) {
        $prepared_filters = array();
        $i = 0;

        foreach ($filters as $filter) {
            $prepared_filters[$i] = $filter['@attributes'];
            $options = isset($filter['option']) ? $filter['option'] : $filter[0];
            foreach ($options as $option) {
                $prepared_filters[$i]['options'][] = isset($option['@attributes']) ? $option['@attributes'] : $option;
            }
            $i++;
        }

        return $prepared_filters;
    }

    /**
     * Convert XML to an array.
     *
     * @param SimpleXMLElement $xml
     *
     * @return array
     */
    protected function xmlToArray(SimpleXMLElement $xml) {
        return json_decode(json_encode($xml), true);
    }
}
