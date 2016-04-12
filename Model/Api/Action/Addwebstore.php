<?php

namespace Klevu\Search\Model\Api\Action;

class Addwebstore extends \Klevu\Search\Model\Api\Actionall {

    const ENDPOINT = "/n-search/addWebstore";
    const METHOD   = "POST";


    const DEFAULT_REQUEST_MODEL = "Klevu\Search\Model\Api\Request\Post";
    const DEFAULT_RESPONSE_MODEL = "Klevu\Search\Model\Api\Response\Data";

    protected function validate($parameters) {
        $errors = array();

        if (!isset($parameters['customerId']) || empty($parameters['customerId'])) {
            $errors['customerId'] = "Missing customer id.";
        }

        if (!isset($parameters['testMode']) || empty($parameters['testMode'])) {
            $errors['testMode'] = "Missing test mode.";
        } else {
            if (!in_array($parameters['testMode'], array("true", "false"))) {
                $errors['testMode'] = "Test mode must contain the text true or false.";
            }
        }

        if (!isset($parameters['storeName']) || empty($parameters['storeName'])) {
            $errors['storeName'] = "Missing store name.";
        }

        if (!isset($parameters['language']) || empty($parameters['language'])) {
            $errors['language'] = "Missing language.";
        }

        if (!isset($parameters['timezone']) || empty($parameters['timezone'])) {
            $errors['timezone'] = "Missing timezone.";
        }

        if (!isset($parameters['version']) || empty($parameters['version'])) {
            $errors['version'] = "Missing module version";
        }

        if (!isset($parameters['country']) || empty($parameters['country'])) {
            $errors['country'] = "Missing country.";
        }

        if (!isset($parameters['locale']) || empty($parameters['locale'])) {
            $errors['locale'] = "Missing locale.";
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }
}
