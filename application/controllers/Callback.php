<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Callback extends MY_Controller{
    function __construct(){
        parent::__construct();
        
        $this->load->model("service_model");
        $this->load->model("charging_model");
    }

    function getData() {
        $cnfmResult = $this->input->get('cnfmResult');
        $resultCode = $this->input->get('resultCode');
        $isDoubleConfrim = $this->input->get('isDoubleConfrim');
        $transactionId = $this->input->get('transactionId');
        

        $body = file_get_contents('php://input');
        $clickUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        
        
    }
}
    