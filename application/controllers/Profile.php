<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Profile extends MY_Controller{
    function __construct(){
        parent::__construct();
        
        $this->load->model("service_model");
        $this->load->model("charging_model");

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
        $passwordDigest = $this->genrateBase24Hash($Details['secret'].$nonce.$created.$Details['password']);
        
        $payload = "";
        $payload = $payload.'spid='.$Details['spId'];
        $payload = $payload.'&passwordDigest='.$passwordDigest;
        $payload = $payload.'&nonce='.$nonce;
        $payload = $payload.'&created='.$created;
        $payload = $payload.'&transactionId ='.$transactionId;
        $payload = $payload.'&productID='.$Details['productId'];
        $payload = $payload.'&msisdn ='.$msisdn;
        $payload = $payload.'&language='.$Details['language'];
        $payload = $payload.'&callbackURL='.$Details['callbackURL'];
        $payload = $payload.'&action=1';
        $payload = $payload.'&amount='.$Details['amount'];
        $payload = $payload.'&serviceId='.$Details['serviceId'];
    
        
        $Result_Data = file_get_contents($url.'?'.$payload);
        
        // Need to write code for response.
        
        $ref = "";
        if (isset($ResultData['redirectURL'])) {
            $data['success'] = true;
            $data['aoc_url'] = $ResultData['redirectURL'];
            
            $exploded = explode('/', $ResultData['redirectURL']);
            $ref = end($exploded);
        }
        
        $this->charging_model->addCharging($serviceName, $package, $requestId, $start, $end, $mobile, $email, $invoiceID, $errorCode, $errorMessage, $prevId, $channel, $ref);
        
        echo json_encode($data, JSON_UNESCAPED_SLASHES);

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
    
    public function getPackageFromSubId(){
        $msisdn = $this->input->get('subId');
        $service = $this->input->get('service');
        
        echo $this->service_model->getPackageNameFromSubId($msisdn, $service);
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