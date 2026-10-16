<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Charging_model extends Common_model{
	function __construct(){
		parent::__construct();		
	}

    function addCallback($cnfmResult, $resultCode, $isDoubleConfrim, $transactionId, $body, $clickUrl) {
        $data = array(
             'cnfmResult'=>$cnfmResult,
             'resultCode'=>$resultCode,
             'isDoubleConfrim'=>$isDoubleConfrim,
             'transactionId'=>$transactionId,
             'requestBody'=>$body,
             'requestUrl'=>$clickUrl
        );
        
        //echo json_encode($data);

        $this->db->trans_start();
        $this->db->insert($this->callback_history, $data);
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    function addCharging($serviceName, $package, $requestId, $startDate, $endDate, $mobile, $email, $invoiceID, $errorCode, $errorMessage, $prevId, $channel, $ref) {
        $data = array(
             'serviceName'=>$serviceName,
             'package'=>$package,
             'requestId'=>$requestId,
             'startDate'=>$startDate,
             'endDate'=>$endDate,
             'mobile'=>$mobile,
            'email'=>$email,
            'invoiceID'=>$invoiceID,
            'errorCode'=>$errorCode,
            'errorMessage'=>$errorMessage,
            'prevId'=>$prevId,
            'channel'=>$channel,
            'reference'=>$ref
        );
        
        //echo json_encode($data);

        $this->db->trans_start();
        $this->db->insert($this->aoc_charging_details_table, $data);
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    public function insert_NBound_charging_log($data)
	{
		$this->db->insert($this->nbound_charging_details_table, $data);

		if ($this->db->affected_rows() > 0)
        	$ret = $this->db->insert_id();
		else 
			$ret = false;

		return $ret;
	}
    
    function addRenew($msisdn, $serviceName, $package, $requestId, $type, $transactionId, $status) {
        $data = array(
             'msisdn'=>$msisdn,
             'serviceName'=>$serviceName,
             'package'=>$package,
             'actionType'=>$type,
             'requestId'=>$requestId,
             'transactionId'=>$transactionId,
             'status'=>$status
        );
        
        //echo json_encode($data);

        $this->db->trans_start();
        $this->db->insert($this->aoc_charging_details_table, $data);
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    function updateCharging($requestId, $status, $subscriptionID, $createdAt, $errorCode, $errorMessage) {
        date_default_timezone_set('Asia/Dhaka');
        $now = date('Y-m-d H:i:s');
        
        $this->db->trans_start();
        $this->db->set('status', $status);
        $this->db->set('subscriptionID', $subscriptionID);
        $this->db->set('createdAt', $createdAt);
        $this->db->set('errorCode', $errorCode);
        $this->db->set('errorMessage', $errorMessage);
        $this->db->set('responseTime', $now );
        $this->db->where('requestId', $requestId);
        $this->db->update($this->aoc_charging_details_table);
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    public function getDetailsFromInvoiceId($invoiceID){
		$sql = "SELECT * FROM ".$this->aoc_charging_details_table." where invoiceID = '$invoiceID'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->result_array();
    }
    
    public function getDetailsFromReference($reference){
		$sql = "SELECT * FROM ".$this->aoc_charging_details_table." where reference = '$reference'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->result_array();
    }
            
     public function getDetailsFromCodeId($code_id){
		$sql = "SELECT * FROM ".$this->package_table." where code_id = '$code_id'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->result_array();
    }
    
    public function getDetailsFromRef($refNo){
		$sql = "SELECT * FROM ".$this->aoc_charging_details_table." where referenceNo = '$refNo'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
		return $query->result_array();
    }
    
    function checkPreviousSubscription($msisdn, $serviceName) {
            $sql = "SELECT * FROM tbl_subscriber_profile where msisdn = '$msisdn' and serviceName = '$serviceName'";
            $q = $this->db->query($sql);
            if($q->num_rows() > 0) {
                return true;
            }

            return false;
        }
        
    function checkPreviousSubscription1($msisdn, $serviceName) {
            $sql = "SELECT * FROM tbl_subscriber_profile_history where msisdn = '$msisdn' and serviceName = '$serviceName'";
            $q = $this->db->query($sql);
            if($q->num_rows() > 0) {
                return true;
            }

            return false;
        }    
        
    function addCallbackOperator($callbackUrl) {
        $data = array(
             'notifyUrl'=>$callbackUrl
        );
        
        //echo json_encode($data);

        $this->db->trans_start();
        $this->db->insert('SouthAfrica.dbo.tbl_operator_callback_history', $data);
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    function addChargingOperator($requestId, $refId, $operator, $telNo, $amount, $statusId, $statusTime, $statusMessage, $type) {
        $data = array(
             'msisdn'=>$telNo,
             'requestId'=>$requestId,
             'referenceNo'=>$refId,
             'operator'=>$operator,
             'amount'=>$amount,
            'statusId'=>$statusId,
            'statusTime'=>$statusTime,
            'statusMessage'=>$statusMessage,
            'type'=>$type
        );
        
        //echo json_encode($data);

        $this->db->trans_start();
        $this->db->insert('SouthAfrica.dbo.tbl_operator_charging', $data);
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    function addBillingReq($msisdn, $client, $result, $type, $reqUrl) {
        $data = array(
             'msisdn'=>$msisdn,
             'serviceName'=>$client,
             'result'=>$result,
             'type'=>$type,
            'reqUrl'=>$reqUrl
        );
        
        //echo json_encode($data);

        $this->db->trans_start();
        $this->db->insert('tbl_billing_req', $data);
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    function addNotification($message, $timeStamp, $subscriptionRequestId, $subscriptionId, $subscriptionStatus, $nextPaymentDate, $amount, $trxId
                , $trxDate
                , $paymentId
                , $paymentStatus
                , $dueDate
                , $firstPayment
                , $reverseTrxId
                , $refundedAmount
                , $reversTrxDate
                , $cancelledBy) {
        $data = array(
             'serviceName'=>$this->getServiceNameFromSubscriptionId($subscriptionId),
             'message'=>$message,
             'timeStamp'=>$timeStamp,
             'subscriptionRequestId'=>$subscriptionRequestId,
             'subscriptionId'=>$subscriptionId,
             'subscriptionStatus'=>$subscriptionStatus,
            'nextPaymentDate'=>$nextPaymentDate,
            'amount'=>$amount,
            'trxId'=>$trxId,
            'trxDate'=>$trxDate,
            'paymentId'=>$paymentId,
            'paymentStatus'=>$paymentStatus,
            'dueDate'=>$dueDate,
            'firstPayment'=>$firstPayment,
            'reverseTrxId'=>$reverseTrxId,
            'refundedAmount'=>$refundedAmount,
            'reversTrxDate'=>$reversTrxDate,
            'cancelledBy'=>$cancelledBy
        );
        
        //echo json_encode($data);

        $this->db->insert('tbl_notification', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }
    
    function getServiceNameFromSubscriptionId($subscriptionID){
                $sql = "SELECT * FROM tbl_subscriber_profile where msisdn = '{$subscriptionID}'";
		$query 	= $this->db->query($sql);
		//echo $this->db->last_query();die();
                if($query->num_rows() > 0) {
                    return $query->row()->serviceName;
                }
                
                return '';
	}
        
    function addNotificationHistory($notificationUrl, $payload) {
        $data = array(
             'notificationUrl'=>$notificationUrl,
             'payload'=>$payload
        );
        
        $this->db->trans_start();
        $this->db->insert('tbl_notification_url_log', $data);
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    function checkTotalChargedAmount($subscriptionID) {
            $sql = "SELECT count(distinct paymentID) as total FROM tbl_notification WHERE DATEPART(m, receivedTime) = DATEPART(m, DATEADD(m, -1, getdate())) AND DATEPART(yyyy, receivedTime) = DATEPART(yyyy, DATEADD(m, -1, getdate())) and subscriptionID = '$subscriptionID' and status='SUCCEEDED_PAYMENT'";
            $q = $this->db->query($sql);
            if($q->num_rows() > 0) {
                return $q->row()->total;
            }

            return '0';
        }   
        
    function checkTotalChargedAmountThisMonth($subscriptionID) {
            $sql = "SELECT count(distinct paymentID) as total FROM tbl_notification WHERE DATEPART(m, receivedTime) = DATEPART(m, DATEADD(m, 0, getdate())) AND DATEPART(yyyy, receivedTime) = DATEPART(yyyy, DATEADD(m, 0, getdate())) and subscriptionID = '$subscriptionID' and status='SUCCEEDED_PAYMENT'";
            $q = $this->db->query($sql);
            if($q->num_rows() > 0) {
                return $q->row()->total;
            }

            return '0';
        }       
}
?>