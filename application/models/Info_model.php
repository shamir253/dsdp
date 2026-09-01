<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Info_model extends Common_model{
	function __construct(){
		parent::__construct();		
	}

	public function getSubscribers($serviceName, $startDate, $endDate){
		$sql = "select CAST(registrationTime AS DATE) as regDate, count(*) as regCount  from tbl_subscriber_profile where serviceName = '{$serviceName}' and registrationTime >= '{$startDate}' and registrationTime <= '{$endDate}' group by CAST(registrationTime AS DATE) order by CAST(registrationTime AS DATE)";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
        public function getUnsubscribers($serviceName, $startDate, $endDate){
		$sql = "select CAST(unsubTime AS DATE) as deregDate, count(*) as deregCount  from tbl_subscriber_profile_history where serviceName = '{$serviceName}' and unsubTime >= '{$startDate}' and unsubTime <= '{$endDate}' group by CAST(unsubTime AS DATE) order by CAST(unsubTime AS DATE)";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
       public function getDayWiseChargedCount($serviceName, $startDate, $endDate){
		$sql = "select CAST(receivedTime AS DATE) as recTime, count(distinct notificationID) as chargedCount from tbl_notification where serviceName = '{$serviceName}' and status = 'SUCCEEDED_PAYMENT' and subscriptionID in (select msisdn from tbl_subscriber_profile where CAST(registrationTime AS DATE) = CAST(receivedTime AS DATE)) and receivedTime >= '{$startDate}' and receivedTime <= '{$endDate}' group by CAST(receivedTime AS DATE) order by CAST(receivedTime AS DATE)";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
        public function getTotalRenew($serviceName, $startDate, $endDate){
		$sql = "select CAST(receivedTime AS DATE) as recTime, count(distinct notificationID) as chargedCount  from tbl_notification where serviceName = '{$serviceName}' and status = 'SUCCEEDED_PAYMENT' and receivedTime >= '{$startDate}' and receivedTime <= '{$endDate}' group by CAST(receivedTime AS DATE) order by CAST(receivedTime AS DATE)";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}

        public function getTodaysSubscribers($serviceName, $start){
		$sql = "select name, mobile, registrationTime from myteam.dbo.tbl_user_account, tbl_subscriber_profile where registrationTime > '{$start}' and serviceName = '{$serviceName}' and myteam.dbo.tbl_user_account.msisdn = tbl_subscriber_profile.msisdn";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
        public function getWinnerList(){
		$sql = "select name, mobile, totalpoint from myteam.dbo.tbl_daily_quiz_result, myteam.dbo.tbl_user_account where convert(varchar(10), participationTime, 120)=convert(varchar(10), getdate()-1, 120) and tbl_daily_quiz_result.msisdn = tbl_user_account.msisdn order by totalPoint desc";
		$query 	= $this->db->query($sql);
		return $query->result_array();
	}
        
         public function getDayWiseNewRev($serviceName, $date){
                $startDate = $date.' 00:00:00';
                $endDate = $date.' 23:59:59';
                
                $total = 0;
                
		$sql = "select CAST(receivedTime AS DATE) as recTime, count(distinct notificationID) as chargedCount from tbl_notification where serviceName = '{$serviceName}' and status = 'SUCCEEDED_PAYMENT' and subscriptionID in (select msisdn from tbl_subscriber_profile where CAST(registrationTime AS DATE) = CAST(receivedTime AS DATE) and package = 'WEEKLY') and receivedTime >= '{$startDate}' and receivedTime <= '{$endDate}' group by CAST(receivedTime AS DATE) order by CAST(receivedTime AS DATE)";
		$query 	= $this->db->query($sql);
                
                if($query->num_rows() > 0) {
                    $total = $total + ((int)$query->row()->chargedCount * 30);
                }
		
                $sql = "select CAST(receivedTime AS DATE) as recTime, count(distinct notificationID) as chargedCount from tbl_notification where serviceName = '{$serviceName}' and status = 'SUCCEEDED_PAYMENT' and subscriptionID in (select msisdn from tbl_subscriber_profile where CAST(registrationTime AS DATE) = CAST(receivedTime AS DATE) and package = 'FIFTEEN_DAYS') and receivedTime >= '{$startDate}'  and receivedTime <= '{$endDate}' group by CAST(receivedTime AS DATE) order by CAST(receivedTime AS DATE)";
		$query 	= $this->db->query($sql);
                
                if($query->num_rows() > 0) {
                    $total = $total + ((int)$query->row()->chargedCount * 50);
                }
                
                $sql = "select CAST(receivedTime AS DATE) as recTime, count(distinct notificationID) as chargedCount from tbl_notification where serviceName = '{$serviceName}' and status = 'SUCCEEDED_PAYMENT' and subscriptionID in (select msisdn from tbl_subscriber_profile where CAST(registrationTime AS DATE) = CAST(receivedTime AS DATE) and package = 'MONTHLY') and receivedTime >= '{$startDate}' and receivedTime <= '{$endDate}' group by CAST(receivedTime AS DATE) order by CAST(receivedTime AS DATE)";
		$query 	= $this->db->query($sql);
                
                if($query->num_rows() > 0) {
                    $total = $total + ((int)$query->row()->chargedCount * 80);
                }
                
                return $total;
	}
        
         public function getDayWiseTotalRev($serviceName, $date){
                $startDate = $date.' 00:00:00';
                $endDate = $date.' 23:59:59';
                
                $total = 0;
                
		$sql = "select CAST(receivedTime AS DATE) as recTime, count(distinct notificationID) as chargedCount from tbl_notification where serviceName = '{$serviceName}' and status = 'SUCCEEDED_PAYMENT' and (subscriptionID in (select msisdn from tbl_subscriber_profile where package = 'WEEKLY' and serviceName = '{$serviceName}') or subscriptionID in (select msisdn from tbl_subscriber_profile_history where package = 'WEEKLY' and serviceName = '{$serviceName}')) and receivedTime >= '{$startDate}' and receivedTime <= '{$endDate}' group by CAST(receivedTime AS DATE) order by CAST(receivedTime AS DATE)";
		$query 	= $this->db->query($sql);
                
                if($query->num_rows() > 0) {
                    $total = $total + ((int)$query->row()->chargedCount * 30);
                }
		
                $sql = "select CAST(receivedTime AS DATE) as recTime, count(distinct notificationID) as chargedCount from tbl_notification where serviceName = '{$serviceName}' and status = 'SUCCEEDED_PAYMENT' and (subscriptionID in (select msisdn from tbl_subscriber_profile where package = 'FIFTEEN_DAYS' and serviceName = '{$serviceName}') or subscriptionID in (select msisdn from tbl_subscriber_profile where package = 'FIFTEEN_DAYS' and serviceName = '{$serviceName}')) and receivedTime >= '{$startDate}' and receivedTime <= '{$endDate}' group by CAST(receivedTime AS DATE) order by CAST(receivedTime AS DATE)";
		$query 	= $this->db->query($sql);
                
                if($query->num_rows() > 0) {
                    $total = $total + ((int)$query->row()->chargedCount * 50);
                }
                
                $sql = "select CAST(receivedTime AS DATE) as recTime, count(distinct notificationID) as chargedCount from tbl_notification where serviceName = '{$serviceName}' and status = 'SUCCEEDED_PAYMENT' and (subscriptionID in (select msisdn from tbl_subscriber_profile where package = 'MONTHLY' and serviceName = '{$serviceName}') or subscriptionID in (select msisdn from tbl_subscriber_profile where package = 'MONTHLY' and serviceName = '{$serviceName}')) and receivedTime >= '{$startDate}' and receivedTime <= '{$endDate}' group by CAST(receivedTime AS DATE) order by CAST(receivedTime AS DATE)";
		$query 	= $this->db->query($sql);
                
                if($query->num_rows() > 0) {
                    $total = $total + ((int)$query->row()->chargedCount * 80);
                }
                
                return $total;
	}
}
?>