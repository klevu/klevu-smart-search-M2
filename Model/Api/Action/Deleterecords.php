<?php

namespace Klevu\Search\Model\Api\Action;

class Deleterecords extends \Klevu\Search\Model\Api\Action\Addrecords {

    const ENDPOINT = "/rest/service/deleteRecords";
    const METHOD   = "POST";

    // mandatory_field_name => allowed_empty
    protected $mandatory_fields = array(
        "id" => false
    );
}
