<?php

namespace Klevu\Search\Model\Api\Action;

class Gettimezone extends \Klevu\Search\Model\Api\Actionall {

    const ENDPOINT = "/analytics/getTimezone";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL  = "klevu_search/api_request_post";
    const DEFAULT_RESPONSE_MODEL = "klevu_search/api_response_timezone";

    protected function validate($parameters) {
        return true;
    }
}
