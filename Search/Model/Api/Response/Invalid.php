<?php

/**
 * Class \Klevu\Search\Model\Api\Response\Invalid
 *
 * @method setErrors($errors)
 */
namespace Klevu\Search\Model\Api\Response;

class Invalid extends \Klevu\Search\Model\Api\Response {

    public function _construct() {
        $this->successful = false;
    }

    /**
     * Return the array of errors.
     *
     * @return array
     */
    public function getErrors() {
        $errors = $this->getData('errors');

        if (!$errors) {
            $errors = array();
        }

        if (!is_array($errors)) {
            $errors = array($errors);
        }

        return $errors;
    }

    /**
     * Return the response message.
     *
     * @return string
     */
    public function getMessage() {
        $message = "Invalid request";

        $errors = $this->getErrors();
        if (count($errors) > 0) {
            $message = sprintf("%s: %s", $message, implode(", ", $errors));
        }

        return $message;
    }

    /**
     * Override the parse response method, this API response is doesn't use HTTP.
     *
     * @param \Zend\Http\Response $response
     *
     * @return $this
     */
    protected function parseRawResponse(\Zend\Http\Response $response) {
        // Do nothing
        return $this;
    }
}
