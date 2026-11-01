<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Profile extends MY_Controller{
    public $charging_channel_northbound = 'NBOUND';
    
    function __construct(){
        parent::__construct();
        
        $this->load->model("service_model");
        $this->load->model("charging_model");
        $this->config->load('service_data/data', TRUE);
        $this->load->library('Response_Data');
		
    }
    
    function HttpPostMethod($url, $data)
    {
        $url = curl_init($url);
        $posttoken = json_encode($data);
        
        
        $header = array(
            'Content-Type:application/json'
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($url, CURLOPT_SSL_VERIFYPEER, 0);

        $resultdata = curl_exec($url);
        $ResultArray = json_decode($resultdata, true);
        curl_close($url);
        return $ResultArray;
    }
    
    function HttpDeleteMethod($PostURL)
    {
        $url = curl_init($PostURL);
        
        date_default_timezone_set('Asia/Dhaka');
        $timestamp = date("Y-m-d\TH:i:s.000\Z");
        
        $header = array(
            'Content-Type:application/json',
            'version: v1.0',
            'channelId: Merchant WEB',
            'timeStamp: '.$timestamp,
            'x-api-key: D7abqqeZlpwHT9nUQrpp-0sTjYXl0Lvs'
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($url, CURLOPT_SSL_VERIFYPEER, 0);

        $resultdata = curl_exec($url);
        $ResultArray = json_decode($resultdata, true);
        curl_close($url);
        return $ResultArray;
    }

    public function index() {
        echo "Hi";
    }
    
    public function getToken() {
        echo $this->getNBoundToken();
    }

    public function subscribe() {
        $serviceName = $this->input->get('serviceName');
        $package = $this->input->get('package');
        $msisdn = $this->input->get('msisdn');
        
        $Details = $this->service_model->getServiceDetails($serviceName, $package);

        if (empty($Details) || !is_array($Details)) {
            $data['success'] = false;
            $data['message'] = "Service not found";
            echo json_encode($data);
            return;
        }

        $Details = $Details[0];
        
        $url = $Details['baseUrl'];
        $nonce = $this->generate20DigitFast();

        $rand_num = rand(10000, 99999);
        $transactionId = round(microtime(true)).$rand_num;

        date_default_timezone_set('Asia/Dhaka');
        $created = date("Y-m-d\TH:i:s\Z");

        // password digset
        $passwordDigest = $this->genrateBase24Hash((string)$nonce.(string)$Details['amount'].(string)$created.(string)$Details['password']);
        
        $payload = "";
        $payload = $payload.'spid='.$Details['spId'];
        $payload = $payload.'&passwordDigest='.$passwordDigest;
        $payload = $payload.'&nonce='.$nonce;
        $payload = $payload.'&created='.$created;
        $payload = $payload.'&transactionId='.$transactionId;
        $payload = $payload.'&productID='.$Details['productId'];
        $payload = $payload.'&msisdn='.$msisdn;
        $payload = $payload.'&language='.$Details['language'];
        $payload = $payload.'&callbackURL='.$Details['callbackURL'];
        $payload = $payload.'&action=3';
        $payload = $payload.'&amount='.$Details['amount'];
        $payload = $payload.'&serviceId='.$Details['serviceId'];
    
        $this->charging_model->addCharging($msisdn, $serviceName, $package, $transactionId);

        $data['success'] = true;
        $data['aoc_url'] = $url.'?'.$payload;

        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        /*
        //echo $url.'?'.$payload."<br>";
        
        $ResultData = file_get_contents($url.'?'.$payload);
        //echo $ResultData;
        
        // Need to write code for response.
        
        $ref = "";
        if (isset($ResultData['redirectURL'])) {
            $data['success'] = true;
            $data['aoc_url'] = $ResultData['redirectURL'];
            
            $exploded = explode('/', $ResultData['redirectURL']);
            $ref = end($exploded);
        }
        
        //$this->charging_model->addCharging($serviceName, $package, $requestId, $start, $end, $mobile, $email, $invoiceID, $errorCode, $errorMessage, $prevId, $channel, $ref);
        
        //echo json_encode($data, JSON_UNESCAPED_SLASHES);
        */
    }

    
    public function cancel($subId){
        
        $subscriotionID = $subId;
        
        $data['success'] = false;
        $data['message'] = 'subscriotionID not found';
        
        $Details = $this->service_model->getServiceDetailsFromSubscriptionId($subscriotionID);
        
        if (!empty($Details)) {
            $Details = $Details[0];

            $ResultData = $this->HttpDeleteMethod('https://gateway.recurring.pay.bka.sh/gateway/api/subscriptions/'.$subId.'?reason=testing');
            $data['success'] = true;
            $data['message'] = 'subscription canceled';
            
            /*
            if (isset($ResultData['errorCode'])) {
                if ($ResultData['errorCode'] == "00") {
                    $data['success'] = true;
                    $data['message'] = 'subscription canceled';

                    $this->service_model->deleteSubscription($subscriotionID);
                }
            }
             * 
             */
        }
        
        echo json_encode($data);
        
    }
    
    public function renewSubscription()
    {
        $response = new Response_Data();
        
        $msisdn = $this->input->get('msisdn');
        $service = $this->input->get('service');

        if($msisdn == null || $service == null)
        {
            $response->reponse_code = "0";
            $response->response_msg = "Invalid Parameters.";
            echo $response->getJSONResponse($response);
            return;
        }

        if(strlen($msisdn) != 13 || (substr($msisdn,0,5) != '88018' && substr($msisdn,0,5) != '88016')
			|| !is_numeric($msisdn))
		{
			$response->reponse_code = "0";
            $response->response_msg = "Invalid Number.";
            $response->data = "";
            echo $response->getJSONResponse($response);
            return;
		}

        $package_details = $this->service_model->getServiceDetails_fromProfile($msisdn, $service);
        if($package_details == null)
        {
            $response->reponse_code = "0";
            $response->response_msg = "Invalid input parameters.";
            $response->data = "";
            echo $response->getJSONResponse($response);
            return;
        }

        //print_r($package_details);;
        $package_details = $package_details[0];

        echo $this->charge_NorthBound_1stTry($msisdn, $package_details, "RENEWAL");
    }

    private function charge_NorthBound_1stTry($msisdn, $package_details, $actionType){

        $response = new Response_Data();

        $token = $this->getNBoundToken();

        $charge_api = $this->config->item('charge_api_northend', 'service_data/data');
        $charge_api = str_replace("@msisdn", $msisdn, $charge_api);
        //echo $msisdn;die();
        
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $now->setTimeZone(new DateTimeZone('Asia/Dhaka'));
        $request_time = $now->format("Y-m-d H:i:s.u");
        $request_time = substr($request_time, 0, 23);
        //echo $request_time;


        $userName = $this->config->item('SP_Username', 'service_data/data');
        $password = $this->config->item('SP_Password', 'service_data/data');
        $nonce = $this->generate20DigitFast();

        date_default_timezone_set('Asia/Dhaka');
        $created = date("Y-m-d\TH:i:s\Z");

        $passwordDigest = $this->genrateBase24Hash((string)$nonce.(string)$created.(string)$package_details['password']);

        $headers = array(
            'Content-type: application/json',
            'Apptoken: WSSE realm="SDP", profile="UsernameToken", type="Appkey"',
            'X-WSSE: UsernameToken Username="'.$package_details['spId'].'", PasswordDigest="'.$passwordDigest.'", Nonce="'.$nonce.'", Created="'.$created.'"',
            'X-RequestHeader: request ServiceId="'.$package_details['serviceId'].'", oauth_token=""',
            'Accept: application/json',
            'Authorization: Bearer '.$token
        );

        //echo "-----New Headers-----<br>";
        //var_dump($headers);
        //echo "<br>--------------------<br>";

        $rand_num = rand(1000,9999);
        $transID = "NB".$msisdn.".".round(microtime(true)).".".$rand_num;
        
        $amount = $package_details['amount'];
        $description = $package_details['robiServiceName'];
		$code = $package_details['serviceId'];

        $newData = array(
            "amountTransaction" => array(
                "endUserId" => "tel:".$msisdn,
                "paymentAmount" => array(
                    "chargingInformation" => array(
                        "amount" => (int)$amount,
                        "currency" => "BDT",
                        "code" => $code,
                        "description" => array($description)
                    )
                ),
                "referenceCode" => $transID,
                "transactionStatus" => "Charged"
            )
        );

        //echo "<br>--------------------<br>";
        //echo json_encode($newData);
        //echo "<br>--------------------<br>";
        
        $ch = curl_init($charge_api);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($newData));

        $result = curl_exec($ch);
        //echo "-----Result-----<br>";
        //print_r($result);
        //echo "<br>--------------------<br>";

        $info = curl_getinfo($ch);
        //print_r($info);
        if(curl_errno($ch) !== 0)
        {
            curl_close($ch);
            
            $response->reponse_code = "0";
            $response->response_msg = "Error";
            $response->data = "Internal Error";
            //return $response->getJSONResponse($response);
            //return "0";
        }
        else
        {
            curl_close($ch);

            $obj = json_decode($result, true);
            //print_r($obj);die();

            if(isset($obj['amountTransaction']) && !empty($obj['amountTransaction']))
            {

                if(isset($obj['amountTransaction']['transactionStatus']) && 
                    $obj['amountTransaction']['transactionStatus'] == "Charged")
                {
                    $response->reponse_code = "1";
                    $response->response_msg = "SUCCESS";
                    $response->data = json_encode($obj['amountTransaction']['paymentAmount']);
                    //return $response->getJSONResponse($response);
                }
                else
                {
                    $response->reponse_code = "0";
                    $response->response_msg = "FAILED";
                    $response->data = json_encode($obj['amountTransaction']['paymentAmount']);
                    //return $response->getJSONResponse($response);
                }
                
                //die();
                //return "1";
            }
            else if(isset($obj['requestError']) && !empty($obj['requestError']))
            {
                if(isset($obj['requestError']['serviceException']) && !empty($obj['requestError']['serviceException']))
                {
                    if(isset($obj['requestError']['serviceException']['messageId'])
                        && !empty($obj['requestError']['serviceException']['messageId'])
                            && $obj['requestError']['serviceException']['messageId'] == "SVC0002")
                    {
                        // Do unsubsciption
                        $response->reponse_code = "2";
                        $response->response_msg = "FAILED";
                        $response->data = json_encode($obj['requestError']);
                        //return $response->getJSONResponse($response);
                    }
                    else
                    {
                        $response->reponse_code = "5";
                        $response->response_msg = "FAILED";
                        $response->data = json_encode($obj['requestError']);
                        //return $response->getJSONResponse($response);
                    }
                }
                else if(isset($obj['requestError']['policyException']) && !empty($obj['requestError']['policyException']))
                {
                    if(isset($obj['requestError']['policyException']['messageId']) 
                        && !empty($obj['requestError']['policyException']['messageId'])
                            && $obj['requestError']['policyException']['messageId'] == "POL0001")
                    {
                        // Do unsubsciption, and add to black list.
                        $response->reponse_code = "3";
                        $response->response_msg = "FAILED";
                        $response->data = json_encode($obj['requestError']);
                        //return $response->getJSONResponse($response);
                    }
                    else
                    {
                        $response->reponse_code = "6";
                        $response->response_msg = "FAILED";
                        $response->data = json_encode($obj['requestError']);
                        //return $response->getJSONResponse($response);
                    }
                }
				else
                {
                    $response->reponse_code = "4";
                    $response->response_msg = "FAILED";
                    $response->data = json_encode($obj['requestError']);
                    //return $response->getJSONResponse($response);
                }
				

                //echo $response->getJSONResponse($response);
                //die();
            }
            else 
            {
                /*
                {
                    "fault": {
                        "code": 900901,
                        "message": "Invalid Credentials",
                        "description": "Access failure for API: /payment/v1, version: v1 status: (900901) - Invalid Credentials. Make sure you have given the correct access token"
                    }
                } 
                */
                if(isset($obj['fault']['code']) && $obj['fault']['code']=="900901"
                    && isset($obj['fault']['description']) 
                    && strpos($obj['fault']['description'], 'correct access token') !== false)
                {
                    $response->reponse_code = "-2";
                    $response->response_msg = "Error";
                    $response->data = json_encode($obj['fault']['description']);
                }
                else
                {
                    $response->reponse_code = "0";
                    $response->response_msg = "Error";
                    $response->data = json_encode($obj['fault']);
                    //return $response->getJSONResponse($response);
                    //die();
                }
                
            }
        }
        
        $data = array(
            'msisdn' => $msisdn,
            'servicename' => $package_details['serviceName'],
            'packagename' => $package_details['package'],
            'clientCorrelator' => $transID,
            'amount' => $package_details['amount'],
            'referenceCode' => $transID,
            'requestTime' => $request_time,
            'chargeStatus' => $response->reponse_code,
            'chargingChannel' => $this->charging_channel_northbound,
            'response' => $response->response_msg,
            'actionType' => $actionType,
            'response_details' => $response->data
        );

        //echo json_encode($data);
        $this->charging_model->insert_NBound_charging_log($data);

        return $response->getJSONResponse($response);
        //die();
    }

    function testSendSms() {
        $this->sendSms("25063", "8801878958625", "Test sender");
    }

    public function getPackageFromSubId(){
        $msisdn = $this->input->get('subId');
        $service = $this->input->get('service');
        
        echo $this->service_model->getPackageNameFromSubId($msisdn, $service);
    }

    public function getStatus($msisdn, $serviceName) {
        echo $this->service_model->checkStatus($msisdn, $serviceName);
    }

    public function unSubscribe($msisdn, $serviceName) {
        $details = $this->service_model->getServiceDetails($serviceName, $this->service_model->getPackageNameFromSubId($msisdn, $serviceName));
        
        $this->service_model->deleteUser($msisdn, $serviceName);
            
        $shortCode = $this->config->item('Short_Code', 'service_data/data');
        $unSubMsg = $this->config->item('unsubscription_msg', 'service_data/data');
        $unSubMsg = str_replace("@package_name", $details[0]['robiServiceName'], $unSubMsg);
        $this->sendSms($shortCode, $msisdn, $unSubMsg);
    }
    
    public function ondemand(){
        $serviceName = $this->input->get('serviceName');
        $amount = $this->input->get('amount');
        $package = "ONDEMAND";
        
        $Details = $this->service_model->getServiceDetails($serviceName, $package);
        $Details = $Details[0];
        
        $url = $Details['baseUrl'];
        
        $token = $this->getToken($Details['secret'], $Details['username'], $Details['password'], $Details['clientID'], $Details['baseUrl']);
        //echo $token.'<br>';
        
        $rand_num = rand(1,99999);
        $requestId = round(microtime(true)).$rand_num;

        $PostData = array(
                'clientID' => $Details['clientID'],
                'orderID' => $requestId,
                'package' => $Details['package'],
                'amount' => $amount,
                'callBackURL' => $Details['userReturnURL']
            );
        
        //echo json_encode($PostData)."<br>";
        
        /*$url = SANBOX_URL;
            
        if (DEVELOPMENT_MODE == 'PRODUCTION') {
            $url = URL;
        }  */
        
        $invoiceID = '';
        $errorCode = '';
        $errorMessage = '';
        
        
        $data['success'] = false;
        $data['aoc_url'] = '';
        
        $ResultData = $this->HttpPostMethodWithToken($url.'/api/v2.0/charge', $PostData, $token);
        if (isset($ResultData['url'])) {
            $data['success'] = true;
            $data['aoc_url'] = $ResultData['url'];
            
            $errorMessage = $ResultData['errorMessage'];
            $invoiceID = $ResultData['spTransID'];
            $errorCode = $ResultData['errorCode'];
        }
        
        $this->charging_model->addCharging($serviceName, $package, $requestId, null, null, null, null, $invoiceID, $errorCode, $errorMessage);
        
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
    }
    
    public function getTotalChargedAmountOfLastMonth(){
        $subscriotionID = $this->input->get('subscriotionID');
        
        $data['success'] = false;
        $data['message'] = 'subscriotionID not found';
        $data['total_amount'] = '';
        $data['package'] = '';
        $data['total_amount_current_month'] = '';
        
        $Details = $this->service_model->getServiceDetailsFromSubscriptionId($subscriotionID);
        
        if (!empty($Details)) {
            $Details = $Details[0];
            $amount = $Details['amount'];
            
            $totalTimesCharged = $this->charging_model->checkTotalChargedAmount($subscriotionID);
            
            $total = (int)$amount * (int)$totalTimesCharged;
            
            $totalTimesChargedThisMonth = $this->charging_model->checkTotalChargedAmountThisMonth($subscriotionID);
            
            $totalThisMonth = (int)$amount * (int)$totalTimesChargedThisMonth;

            $data['success'] = true;
            $data['total_amount'] = $total;
            $data['total_amount_current_month'] = $totalThisMonth;
            $data['message'] = '';
            $data['package'] = $Details['cycle'];
        }
        
        echo json_encode($data);
    }
    
    function generate20DigitFast() {
        $number = (string) mt_rand(1, 9);
        for ($i = 0; $i < 19; $i++) {
            $number .= mt_rand(0, 9);
        }

        return $number;
    }

    function genrateBase24Hash($data) {
        $binaryHash = hash('sha256', $data, true);
        $base64Hash = base64_encode($binaryHash);
        return $base64Hash;
    }
}
?>