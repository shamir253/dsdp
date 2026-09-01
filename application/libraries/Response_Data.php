<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Response_Data {

    public $reponse_code = "";
    public $response_msg = "";
    public $url = "";
    public $data = "";

    public function getJSONResponse($data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }
}