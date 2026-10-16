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

        $params = [];
        if (isset($result['params'])) {
            $params = $result['params'];
        }

        $services = $this->service_model->getSubscriptionDetailFromMsisdn($msisdn);
        $count = 0;
        foreach ($services as $service) {
            $serviceName = $service['service_name'];
            $package = $service['package_name'];

            $serviceDetails = $this->service_model->getServiceDetails($serviceName, $package);
            $serviceDetail = $serviceDetails[0];

            $data[$count]['status'] = 'Active';
            $data[$count]['serviceName'] = $serviceDetail['robiServiceName'];
            $data[$count]['serviceValidity'] = $this->getServiceValidity($serviceDetail['serviceValidity']);
            $data[$count]['activationDate'] = $this->getFormattedDate($service['registration_time']);

            if (isset($params['platformName'])) {
                $data[$count]['platformName'] = 'DOB_Boostconnect';
            }

            if (isset($params['serviceId'])) {
                $data[$count]['serviceId'] = $serviceDetail['serviceId'];
            }

            if (isset($params['expiryDate'])) {
                $data[$count]['expiryDate'] = $this->getFormattedDate($service['validity_date']);
            }

            if (isset($params['nextRenewalDate'])) {
                $data[$count]['nextRenewalDate'] = $this->getFormattedDate($service['renew_time']);
            }
            
            if (isset($params['chargedAmount'])) {
                $data[$count]['chargedAmount'] = $this->getFormattedDate($serviceDetail['amount'].' BDT');
            }

            if (isset($params['activationMode'])) {
                $data[$count]['activationMode'] = $service['subscriberType'];
            }

            if (isset($params['activationConsent'])) {
                $data[$count]['activationConsent'] = 'OTP';
            }

            if (isset($params['consentGivenDate'])) {
                $data[$count]['activationConsent'] = $this->getFormattedDate($service['registration_time']);
            }

            if (isset($params['lastAttemptDate'])) {
                $data[$count]['lastAttemptDate'] =  $this->getFormattedDate($service['renew_time']);
            }

            if (isset($params['dndStatus'])) {
                $data[$count]['dndStatus'] =  'Inactive';
            }

            if (isset($params['dndOptInDate'])) {
                $data[$count]['dndOptInDate'] =  '';
            }

            if (isset($params['dndOptOutDate'])) {
                $data[$count]['dndOptOutDate'] =  '';
            }

            if (isset($params['cpName'])) {
                $data[$count]['cpName'] =  $serviceDetail['cpName'];
            }

            if (isset($params['serviceType'])) {
                $data[$count]['serviceType'] =  $serviceDetail['serviceType'];
            }
            
            if (isset($params['serviceDescription'])) {
                $data[$count]['serviceDescription'] =  $serviceDetail['serviceDescription'];
            }

            if (isset($params['deactivationDate'])) {
                $data[$count]['deactivationDate'] =  '';
            }
            
            $count++;            
        }

        //Get info about deactivated services
        $services = $this->service_model->getSubscriptionDetailHisotryFromMsisdn($msisdn);
        foreach ($services as $service) {
            if (!$this->checkPresenceOfService($data, $service['service_name'])) {
                $serviceName = $service['service_name'];
                $package = $service['package_name'];

                $serviceDetails = $this->service_model->getServiceDetails($serviceName, $package);
                $serviceDetail = $serviceDetails[0];

                $data[$count]['status'] = 'Active';
                $data[$count]['serviceName'] = $serviceDetail['robiServiceName'];
                $data[$count]['serviceValidity'] = $this->getServiceValidity($serviceDetail['serviceValidity']);
                $data[$count]['activationDate'] = $this->getFormattedDate($service['registration_time']);
                $data[$count]['deactivationDate'] =  $this->getFormattedDate($service['unsub_time']);
            }
        }

        echo json_encode($data);
    }

    function deactivateAll() {
        $msisdn = $this->input->post('msisdn":');

        $response = new Response_Data();

        if (!isset($msisdn) || strlen($msisdn) != 13 || substr($msisdn, 0, 3) != '880') {
            $response->reponse_code = "0";
            $response->response_msg = "Invalid Number.";
            $response->data = "";
            echo $response->getJSONResponse($response);
            return;
        }

        $this->service_model->deleteUserFromAllServices($msisdn);

        $data['status'] = 'Success';
        $data['message'] = 'All services deactivated successfully';

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

    function checkPresenceOfService($services, $serviceName) {
        foreach ($services as $service) {
            if ($service['service_name'] == $serviceName) {
                return true;
            }
        }

        return false;
    }
}

?>