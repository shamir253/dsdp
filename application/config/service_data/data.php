<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// North Bound API
$config['getToken_api_northend'] = 'https://apigate.robi.com.bd/token';
$config['sendSms_api_northend'] = 'https://apigate.robi.com.bd/Ext/smsmessaging/v1/outbound/tel%3A%2B@msisdn/requests';
$config['charge_api_northend'] = 'https://apigate.robi.com.bd/Ext/SDP/DOBRenewal/v1/payment/@msisdn/transactions/amount';


// MIFE NorthBound
$config['SP_Username'] = 'MIFE_IngenuitiDSDP';
$config['SP_Password'] = 'lngeNuItlDSDP#209';
$config['Service_Name'] = 'Dream 11';
$config['Consumer_Key'] = '8EweNRfQf2no73WU63dj7_jDUF8a';
$config['Consumer_Secret'] = 'fQfAGibSCCX8as82NTfbJmiFDoMa';
// Authentication Basic: Base64Encode (Consumer_Key:Consumer_Secret)
$config['Auth_Basic'] = 'M04yNVZ6SHNjRV96eGZQdWxlaUN1VzE1dGo0YTpuTTg4d0tBRWlHdnZacHh1ZTVJX0xzdEwwN3Nh';
$config['Short_Code'] = '25063';

$config['subscription_msg'] = 'You have successfully purchased @package_name subscription. Total charged amount BDT @amount (+VAT+SD+SC)';
$config['unsubscription_msg'] = 'You have successfully unsubscribed from @package_name.';