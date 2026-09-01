<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Service_model extends Common_model{
	function __construct(){
		parent::__construct();		
	}

	public function getServiceDetails($servicename, $packagename){
		$sql = "SELECT * FROM ".$this->package_table." where serviceName = '$servicename' and package = '$packagename'";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
    public function getServiceDetailsFromServiceNameAndRealPackage($servicename, $packagename){
		$sql = "SELECT * FROM ".$this->package_table." where serviceName = '$servicename' and package = '$packagename'";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}

        public function getServiceDetailsFormServiceName($servicename){
		$sql = "SELECT * FROM ".$this->package_table." where serviceName = '$servicename'";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
         public function getServiceDetailsFormOperatorServiceName($servicename){
		$sql = "SELECT * FROM ".$this->package_table." where clientName = '$servicename'";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
	public function getSubscriptionStatus($msisdn, $service){
		$sql = "SELECT * FROM ".$this->tbl_subscriber_profile." where serviceName = '$service' and msisdn = '$msisdn'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->result_array();
	}
        
        public function getSubscriptionDetailFromMsisdn($msisdn){
		$sql = "SELECT * FROM ".$this->tbl_subscriber_profile." where msisdn = '$msisdn'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->result_array();
	}

        public function getServiceNameFromApplicationOperatonAndCountry($applicationId, $operatorId, $countryId){
		$sql = "SELECT * FROM ".$this->package_table." where applicationId = '$applicationId' and operatorId = '$operatorId' and countryId = '$countryId'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->row()->serviceName;
	}

        public function getSubscriptionDeatilsFromMsisdn($msisdn, $applicationId, $operatorId, $countryId){
                $serviceName = $this->getServiceNameFromApplicationOperatonAndCountry($applicationId, $operatorId, $countryId);
		$sql = "SELECT * FROM ".$this->tbl_subscriber_profile." where serviceName = '$serviceName' and msisdn = '$msisdn'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->result_array();
	}
        
        function addCallbackHistory($msisdn, $callbackUrl, $requestId, $requestBody) {
            $data = array(
                 'msisdn'=>$msisdn,
                 'callbackUrl'=>$callbackUrl,
                 'requestId'=>$requestId,
                 'requestBody'=>$requestBody
            );

            //echo json_encode($data);

            $this->db->trans_start();
            $this->db->insert($this->callback_history, $data);
            $this->db->trans_complete();

            return $this->db->trans_status();
        }
        
        function updateUserProfile($subscriptionID, $serviceName, $package, $requestId, $mobile) {
        
            if (!$this->checkExistenceOfUser($subscriptionID, $serviceName)) {
                $data = array(
                     'msisdn'=>$subscriptionID,
                     'serviceName'=>$serviceName,
                     'package'=>$package,
                     'requestId'=>$requestId,
                     'mobile'=>$mobile
                );

                $this->db->trans_start();
                $this->db->insert($this->tbl_subscriber_profile, $data);
                $this->db->trans_complete();

                return $this->db->trans_status();
            } else {
                date_default_timezone_set('Asia/Dhaka');
                $now = date('Y-m-d H:i:s');
        
                $this->db->trans_start();
                $this->db->set('lastRenewedTime', $now);
                $this->db->where('msisdn', $subscriptionID);
                $this->db->where('serviceName', $serviceName);
                $this->db->update($this->tbl_subscriber_profile);
                $this->db->trans_complete();

                return $this->db->trans_status();
            }

            return false;
        } 

        function checkExistenceOfUser($msisdn, $serviceName) {
            $this->db->from($this->tbl_subscriber_profile);
            $this->db->where('msisdn', $msisdn );
            $this->db->where('serviceName', $serviceName );
            $q = $this->db->get();
            if($q->num_rows() > 0) {
                return true;
            }

            return false;
        }
        
        function deleteUser($msisdn, $service) {
            $str = "insert into tbl_subscriber_profile_history(msisdn, serviceName, package, registrationTime, status, lastRenewedTime, renewTime) select msisdn, serviceName, package, registrationTime, status, lastRenewedTime, renewTime from tbl_subscriber_profile where msisdn = '{$msisdn}' and  serviceName = '{$service}'";
            $query=$this->db->query($str);
            
            
            $str = "delete from tbl_subscriber_profile where msisdn = '{$msisdn}' and  serviceName = '{$service}'";
            $query=$this->db->query($str);
        }
        
        public function getClickCompanyInfo($serviceName, $clickId){
		$sql = "SELECT * FROM tbl_click_info_mapping where serviceName = '$serviceName' and clickId = '$clickId'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
                if($query->num_rows() > 0) {
                    return $query->row()->company;
                }
                
                return '';
	}

        public function getAllPackages(){
		$sql = "SELECT * FROM ".$this->package_table;
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->result_array();
	}
        
        public function getTokenDetails() {
            $sql = "SELECT * FROM tbl_token";
            $query = $this->db->query($sql);
	    //echo $this->db->last_query();die();
            if($query->num_rows() > 0) {
                return $query->row()->refresh_token;
            }
            
            return '';
        }
        
        function insertToken($accessToken, $refreshToken) {
            $sql = "truncate table tbl_token";
            $query = $this->db->query($sql);
            
            $data = array(
                 'access_token'=>$accessToken,
                 'refresh_token'=>$refreshToken
            );

            //echo json_encode($data);

            $this->db->trans_start();
            $this->db->insert('tbl_token', $data);
            $this->db->trans_complete();

            return $this->db->trans_status();
        }
        
        function deleteSubscription($subscriotionID) {
            $str = "insert into tbl_subscriber_profile_history(msisdn, serviceName, package, registrationTime, status, lastRenewedTime, renewTime) select msisdn, serviceName, package, registrationTime, status, lastRenewedTime, renewTime from tbl_subscriber_profile where msisdn = '{$subscriotionID}'";
            $query=$this->db->query($str);
            
            
            $str = "delete from tbl_subscriber_profile where msisdn = '{$subscriotionID}'";
            $query=$this->db->query($str);
        }
        
        function getServiceDetailsFromSubscriptionId($subscriptionID){
		$sql = "SELECT * FROM tbl_subscriber_profile where msisdn = '$subscriptionID'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
                
                $serviceName = '';
                $packageName = '';
                if($query->num_rows() > 0) {
                    $serviceName = $query->row()->serviceName;
                    $packageName = $query->row()->package;
                }
                
                $sql = "SELECT * FROM ".$this->package_table." where serviceName = '$serviceName' and package = '$packageName'";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
        function getPackageNameFromSubId($msisdn, $serviceName) {
            $this->db->from($this->tbl_subscriber_profile);
            $this->db->where('msisdn', $msisdn );
            $this->db->where('serviceName', $serviceName );
            $q = $this->db->get();
            if($q->num_rows() > 0) {
                return $q->row()->package;
            }

            return "";
        }
        
       
        
}
?>