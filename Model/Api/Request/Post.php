<?php

namespace Klevu\Search\Model\Api\Request;

class Post extends \Klevu\Search\Model\Api\Request {

    public function __toString() {
        $string = parent::__toString();

        $parameters = $this->getData();
        if (count($parameters) > 0) {
            array_walk($parameters, function(&$value, $key) {
                $value = sprintf("%s: %s", $key, $value);
            });
        }

        return sprintf("%s\nPOST parameters:\n%s\n", $string, implode("\n", $parameters));
    }

    /**
     * Add POST parameters to the request, force POST method.
     *
     * @return \Zend\Http\Client
     */
    protected function build() {
        $client = parent::build();

        $client
            ->setMethod(\Zend\Http\Request::METHOD_POST)
            ->setParameterPost($this->getData());

        return $client;
    }
}
