<?php

namespace Klevu\Search\Model\Api\Action;

class Adduser extends \Klevu\Search\Model\Api\Actionall {

    const ENDPOINT = "/n-search/addUser";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL = "Klevu\Search\Model\Api\Request\Post";
    const DEFAULT_RESPONSE_MODEL = "Klevu\Search\Model\Api\Response\Data";

    protected function validate($parameters) {
        $errors = array();

        if (!isset($parameters['email']) || empty($parameters['email'])) {
            $errors['email'] = "Missing email";
        }

        if (!isset($parameters['password']) || empty($parameters['password'])) {
            $errors['password'] = "Missing password";
        }

        if (!isset($parameters['url']) || empty($parameters['password'])) {
            $errors['url'] = "Missing url";
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }
}
