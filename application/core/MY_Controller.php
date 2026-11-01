<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('Response_Data');

        $this->load->model("service_model");
        $this->config->load('service_data/data', TRUE);
        $this->load->library('Response_Data');
		
    }

    public function getNBoundToken()
    {
        $url = $this->config->item('getToken_api_northend', 'service_data/data');
        $basic = $this->config->item('Auth_Basic', 'service_data/data');
        $username = $this->config->item('SP_Username', 'service_data/data');
        $password = $this->config->item('SP_Password', 'service_data/data');

        $headers = array(
            'Content-type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $basic,
        );

        $params = array(
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
            'scope' => 'PRODUCTION'
        );
        //echo $url; var_dump($headers);var_dump($params);
        //echo "<br>--------------------<br>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $result = curl_exec($ch);

        $info = curl_getinfo($ch);
        //print_r($info);
        //echo "<br>--------------------<br>";

        if (curl_errno($ch) !== 0) {
            $resp = "Error: " . curl_error($ch);
            curl_close($ch);
            return $resp;
        }

        curl_close($ch);

        //echo "-----Result-----<br>";
        //var_dump($result);
        $obj = json_decode($result, true);
        //echo "-----Result-----<br>";
        //var_dump($obj);
        //echo "<br>--------------------<br>";

        if (isset($obj['access_token']) && !empty($obj['access_token'])) {
            return $obj['access_token'];
        }

        return "-1";
    }

    public function revokeToken($oldToken)
    {
        echo "jkasdfhjkl";
    }

    public function sendSms($sender, $receiver, $message)
    {
        $response = new Response_Data();

        $senderName = $sender;

        if ($sender == null || $receiver == null || $message == null) {
            $response->reponse_code = "0";
            $response->response_msg = "Invalid Parameters.";
            echo $response->getJSONResponse($response);
            return;
        }

        if (
            strlen($receiver) != 13 || (substr($receiver, 0, 5) != '88018' && substr($receiver, 0, 5) != '88016')
            || !is_numeric($receiver)
        ) {
            $response->reponse_code = "0";
            $response->response_msg = "Invalid Number.";
            $response->data = "";
            echo $response->getJSONResponse($response);
            return;
        }

        $now = DateTime::createFromFormat('U.u', microtime(true));
        $request_time = $now->format("Y-m-d H:i:s.u");
        $request_time = substr($request_time, 0, 23);

        $resourceURL = "";
        $deliveryStatus = "";
        $serverTransID = "";
        $error_code = "";
        $error_message = "";


        //send immediately. And keep history
        $token = $this->getNBoundToken();
        //echo $token;die();

        $sms_api = $this->config->item('sendSms_api_northend', 'service_data/data');
        $sms_api = str_replace("@msisdn", $receiver, $sms_api);
        //echo $sms_api;die();

        $headers = array(
            'Content-type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        );
        //var_dump($headers);die();

        $rand_num = rand(1000, 99999);
        $correlator = "SMS" . $receiver . "." . round(microtime(true)) . "." . $rand_num;

        $data_string = '
            {
                "outboundSMSMessageRequest": {
                    "address": [
                        "tel:+' . $receiver . '"
                    ],
                    "senderAddress": "tel:' . $sender . '",
                    "outboundSMSTextMessage": {
                        "message": "' . addslashes($message) . '"
                    },
                    "clientCorrelator": "' . $correlator . '",
                    "senderName": "' . $senderName . '"
                }
            }';

        if (!isset($data_string) || empty($data_string) || $data_string == "null") {
            $response->reponse_code = "0";
            $response->response_msg = "FAILED";
            $response->data = "Wrong Message Format";
            echo $response->getJSONResponse($response);
            return;
        }
        //print_r($data_string);die();

        $ch = curl_init($sms_api);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        $result = curl_exec($ch);
        //print_r($result);die();

        $info = curl_getinfo($ch);
        if (curl_errno($ch) !== 0) {
            curl_close($ch);

            $response->reponse_code = "0";
            $response->response_msg = "Error";
            $response->data = "Internal Error";
            echo $response->getJSONResponse($response);
            return;
        } else {
            curl_close($ch);

            $obj = json_decode($result, true);
            //print_r($obj);die();

            if (isset($obj['outboundSMSMessageRequest']) && !empty($obj['outboundSMSMessageRequest'])) {
                $deliveryInfoList = $obj['outboundSMSMessageRequest']['deliveryInfoList'];

                if (isset($deliveryInfoList['resourceURL']))
                    $resourceURL = $deliveryInfoList['resourceURL'];

                if (isset($deliveryInfoList['deliveryInfo'])) {
                    $deliveryInfo = $deliveryInfoList['deliveryInfo'];
                    if (isset($deliveryInfo[0]['deliveryStatus']))
                        $deliveryStatus = $deliveryInfo[0]['deliveryStatus'];
                }

                if (isset($obj['outboundSMSMessageRequest']['resourceURL'])) {
                    $resource_URL = $obj['outboundSMSMessageRequest']['resourceURL'];
                    $serverTransID = substr($resource_URL, strrpos($resource_URL, '/') + 1);
                }

                $data = array(
                    'sender' => $sender,
                    'sender_name' => $senderName,
                    'receiver' => $receiver,
                    'message' => $message,
                    'request_time' => $request_time,
                    'status' => 1,
                    'schedule_time' => $request_time,
                    //'sent_time' => $schedule_time,
                    'delivery_status' => $deliveryStatus,
                    'resource_url' => $resourceURL,
                    'client_correlator' => $correlator,
                    'server_refId' => $serverTransID,
                    'isNow' => 1
                );

                
            } else if (isset($obj['Fault']) && !empty($obj['Fault'])) {
                if (isset($obj['Fault']['error_code']))
                    $error_code = $obj['Fault']['error_code'];

                if (isset($obj['Fault']['error_message']))
                    $error_message = $obj['Fault']['error_message'];

                $deliveryStatus = "Filed";

                $data = array(
                    'sender' => $sender,
                    'sender_name' => $senderName,
                    'receiver' => $receiver,
                    'message' => $message,
                    'request_time' => $request_time,
                    'status' => 1,
                    'schedule_time' => $request_time,
                    //'sent_time' => $transID,
                    'delivery_status' => $deliveryStatus,
                    //'resource_url' => $resourceURL,
                    'client_correlator' => $correlator,
                    //'server_refId' => $serverTransID,
                    'isNow' => 1,
                    'error_code' => $error_code,
                    'error_message' => $error_message
                );
            }

            //echo json_encode($data);
            //$this->service_model->insert_Into_Outbox($data);
        }



        $response->reponse_code = "0";
        $response->response_msg = "SUCCESS";
        //$response->data = "Internal Error";
        //echo $response->getJSONResponse($response);
        return;
    }

}
?>