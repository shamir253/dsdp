<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Service extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model("service_model");
        $this->load->model("charging_model");
        $this->config->load('service_data/data', TRUE);
         $this->load->library('Response_Data');
    }

    function HttpGetMethod($url, $data)
    {
        $url = curl_init($url);

        $header = array(
            'Content-Type:application/json'
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $data);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($url, CURLOPT_SSL_VERIFYPEER, 0);

        $resultdata = curl_exec($url);
        $ResultArray = json_decode($resultdata, true);
        curl_close($url);
        return $ResultArray;
    }

    public function checkStatus()
    {
        $response = new Response_Data();

        $body = file_get_contents('php://input');
        $result = json_decode($body, true);
        if (!isset($result['msisdn']) || strlen($result['msisdn']) != 13 || substr($result['msisdn'], 0, 3) != '880') {
            $response->reponse_code = "0";
            $response->response_msg = "Invalid Number.";
            $response->data = "";
            echo $response->getJSONResponse($response);
            return;
        }

        $msisdn = $result['msisdn'];

        $data = [];
        $addedServices = [];

        $params = [];
        if (isset($result['params'])) {
            $params = $result['params'];
        }

        //echo json_encode($params);

        $services = $this->service_model->getSubscriptionDetailFromMsisdn($msisdn);
        $count = 0;
        foreach ($services as $service) {
            $serviceName = $service['service_name'];
            $package = $service['package_name'];

            array_push($addedServices, $serviceName);

            $serviceDetails = $this->service_model->getServiceDetails($serviceName, $package);
            $serviceDetail = $serviceDetails[0];

            $data[$count]['status'] = 'Active';
            $data[$count]['serviceName'] = $serviceDetail['robiServiceName'];
            $data[$count]['serviceValidity'] = $this->getServiceValidity($serviceDetail['serviceValidity']);
            $data[$count]['activationDate'] = $this->getFormattedDate($service['registration_time']);

            if (in_array('platformName', $params, true)) {
                $data[$count]['platformName'] = 'DOB_Boostconnect';
            }

            if (in_array('serviceId', $params, true)) {
                $data[$count]['serviceId'] = $serviceDetail['serviceId'];
            }

            if (in_array('expiryDate', $params, true)) {
                $data[$count]['expiryDate'] = $this->getNewDate($service['last_renewed_time'], $serviceDetail['serviceValidity']);
            }

            if (in_array('nextRenewalDate', $params, true) && stripos($serviceDetail['package'], 'ondemand') === false) {
                $data[$count]['nextRenewalDate'] = $this->getNewDate($service['last_renewed_time'], $serviceDetail['serviceValidity']);
            }
            
            if (in_array('chargedAmount', $params, true)) {
                $data[$count]['chargedAmount'] = $serviceDetail['amount'];
            }

            if (in_array('activationMode', $params, true)) {
                $data[$count]['activationMode'] = $service['subscriberType'];
            }

            if (in_array('activationConsent', $params, true)) {
                $data[$count]['activationConsent'] = 'OTP';
            }

            if (in_array('consentGivenDate', $params, true)) {
                $data[$count]['activationConsent'] = $this->getFormattedDate($service['registration_time']);
            }

            if (in_array('lastAttemptDate', $params, true) && stripos($serviceDetail['package'], 'ondemand') === false) {
                $data[$count]['lastAttemptDate'] =  $this->getFormattedDate($service['last_renewed_time']);
            }

            if (in_array('dndStatus', $params, true)) {
                $data[$count]['dndStatus'] =  'Inactive';
            }

            if (in_array('dndOptInDate', $params, true)) {
                $data[$count]['dndOptInDate'] =  '';
            }

            if (in_array('dndOptOutDate', $params, true)) {
                $data[$count]['dndOptOutDate'] =  '';
            }

            if (in_array('cpName', $params, true)) {
                $data[$count]['cpName'] =  $serviceDetail['cpName'];
            }

            if (in_array('serviceType', $params, true)) {
                $data[$count]['serviceType'] =  $serviceDetail['serviceType'];
            }
            
            if (in_array('serviceDescription', $params, true)) {
                $data[$count]['serviceDescription'] =  $serviceDetail['serviceDescription'];
            }

            if (in_array('deactivationDate', $params, true)) {
                $data[$count]['deactivationDate'] =  '';
            }
            
            $count++;            
        }

        //Get info about deactivated services
        $services = $this->service_model->getSubscriptionDetailHistoryFromMsisdn($msisdn);
        foreach ($services as $service) {

            if (!$this->checkPresenceOfService($addedServices, $service['service_name'])) {
                $serviceName = $service['service_name'];
                $package = $service['package_name'];

                $serviceDetails = $this->service_model->getServiceDetails($serviceName, $package);
                $serviceDetail = $serviceDetails[0];

                $data[$count]['status'] = 'Deactive';
                $data[$count]['serviceName'] = $serviceDetail['robiServiceName'];
                $data[$count]['serviceValidity'] = $this->getServiceValidity($serviceDetail['serviceValidity']);
                $data[$count]['activationDate'] = $this->getFormattedDate($service['registration_time']);
                $data[$count]['deactivationDate'] =  $this->getFormattedDate($service['unsub_time']);
            }

            $count++;
        }

        echo json_encode($data);
    }

    function deactivateAll() {
        $body = file_get_contents('php://input');
        $result = json_decode($body, true);

        if (!isset($result['msisdn']) || $result['msisdn'] == "") {
            echo "MSISDN is required.";
            return;
        }

        $msisdn = $result['msisdn'];

        $response = new Response_Data();

        if (!isset($msisdn) || strlen($msisdn) != 13 || substr($msisdn, 0, 3) != '880') {
            $response->reponse_code = "0";
            $response->response_msg = "Invalid Number.";
            $response->data = "";
            echo $response->getJSONResponse($response);
            return;
        }

        $services = $this->service_model->getSubscriptionDetailFromMsisdn($msisdn);
        foreach ($services as $service) {
            if (stripos($service['package_name'], 'ondemand') === false) {
                $details = $this->service_model->getServiceDetails($service['service_name'], $service['package_name']);
            
                $this->service_model->deleteUser($msisdn, $service['service_name']);
                
                $shortCode = $this->config->item('Short_Code', 'service_data/data');
                $unSubMsg = $this->config->item('unsubscription_msg', 'service_data/data');
                $unSubMsg = str_replace("@package_name", $details[0]['robiServiceName'], $unSubMsg);
                $this->sendSms($shortCode, $msisdn, $unSubMsg);
            }
        }

        $data['status'] = 'Success';
        $data['message'] = 'All services deactivated successfully';

        echo json_encode($data);
    }

    function deactivate() {
        $body = file_get_contents('php://input');
        $result = json_decode($body, true);

        if (!isset($result['msisdn']) || $result['msisdn'] == "") {
            echo "MSISDN is required.";
            return;
        }

        $msisdn = $result['msisdn'];

        if (!isset($result['serviceId']) || $result['serviceId'] == "") {
            echo "Service ID is required.";
            return;
        }

        $serviceId = $result['serviceId'];


        $response = new Response_Data();

        if (!isset($msisdn) || strlen($msisdn) != 13 || substr($msisdn, 0, 3) != '880') {
            $response->reponse_code = "0";
            $response->response_msg = "Invalid Number.";
            $response->data = "";
            echo $response->getJSONResponse($response);
            return;
        }

        $result = $this->service_model->getSubscriptionDetailFromMsisdnAndServiceId($msisdn, $serviceId);
        foreach ($result as $service) {
            $details = $this->service_model->getServiceDetails($service['service_name'], $service['package_name']);
        
            $this->service_model->deleteUser($msisdn, $service['service_name']);
            
            $shortCode = $this->config->item('Short_Code', 'service_data/data');
            $unSubMsg = $this->config->item('unsubscription_msg', 'service_data/data');
            $unSubMsg = str_replace("@package_name", $details[0]['robiServiceName'], $unSubMsg);
            $this->sendSms($shortCode, $msisdn, $unSubMsg);
        }

        
        $data['status'] = 'Success';
        $data['message'] = 'Service deactivated successfully';

        echo json_encode($data);
    }

    function getServiceValidity($num)
    {
        $num = (int) $num;
        if ($num == 1) {
            return '1 Day';
        } else {
            return '' . $num . ' Days';
        }
    }

    function getFormattedDate($date)
    {
        date_default_timezone_set('Asia/Dhaka');
        return date('Y-m-d\TH:i:s\Z', strtotime($date));
    }

    function getNewDate($date, $days)
    {
        date_default_timezone_set('Asia/Dhaka');
        return date('Y-m-d\TH:i:s\Z', strtotime($date . "+".$days." days"));
    }

    function checkPresenceOfService($data, $serviceName) {
        if (in_array($serviceName, $data, true)) {
            return true;
        }

        return false;
    }
}

?>