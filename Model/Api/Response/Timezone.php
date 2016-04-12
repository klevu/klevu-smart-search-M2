<?php

namespace Klevu\Search\Model\Api\Response;

class Timezone extends \Klevu\Search\Model\Api\Response\Data {

    protected function parseRawResponse(\Zend\Http\Response $response) {
        parent::parseRawResponse($response);

        // Timezone responses don't have a status parameters, just data
        // So the presence of the data is the status
        if ($this->hasData('timezone')) {
            $this->successful = true;
        } else {
            $this->successful = false;
        }
    }
}
