<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Callback extends MY_Controller{
    function __construct(){
        parent::__construct();
        
        $this->load->model("service_model");
        $this->load->model("charging_model");
        $this->config->load('service_data/data', TRUE);
       
    }

    function getData() {
        $cnfmResult = $this->input->get('cnfmResult');
        $resultCode = $this->input->get('resultCode');
        $isDoubleConfrim = $this->input->get('isDoubleConfrim');
        $transactionId = $this->input->get('transactionId');
        

        $body = file_get_contents('php://input');
        $clickUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        
        echo $body;
        echo "<br>";
        echo $clickUrl;
    }

    function updateInfo($resultCode, $transactionId) {
        $body = file_get_contents('php://input');
        $clickUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        
        $this->charging_model->addCallback('', $resultCode, '', $transactionId, $body, $clickUrl);
        $this->charging_model->updateCharging($resultCode, $transactionId);
        if ($resultCode == 0 || $resultCode == "0") {
            
            $result = $this->charging_model->getServiceDetailsFromTransactionId($transactionId);
            $details = $this->service_model->getServiceDetails($result[0]['serviceName'], $result[0]['package']);
            
            $isOndemand = 0;
            if (strpos($result[0]['package'], 'ondemand') !== false) {
                $isOndemand = 1;
            }

            $this->charging_model->addSubscriber($transactionId, $isOndemand);

            $shortCode = $this->config->item('Short_Code', 'service_data/data');
            $subMsg = $this->config->item('subscription_msg', 'service_data/data');
            $subMsg = str_replace("@package_name", $details[0]['robiServiceName'], $subMsg);
            $amount = (int) $details[0]['amount'] / 100;
            $subMsg = str_replace("@amount", $amount, $subMsg);
            $this->sendSms($shortCode, $result[0]['msisdn'], $subMsg);
        }

    }
}
    