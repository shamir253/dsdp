<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['default_controller'] = 'Welcome';
$route['Logincontroller/(:any)'] = 'Logincontroller/error';
$route['404_override'] = 'system/error';
$route['translate_uri_dashes'] = FALSE;


$route['getToken'] = 'Token/getToken';
$route['revokeToken/(:any)'] = "Token/revokeToken/$1";

//profile/getStatus?msisdn=88xxx&service=xxxx
$route['profile/getStatus'] = 'Profile/getStatus';
//profile/subscribe?msisdn=88xxx&service=xxxx&package=xxxx&channel=web/app
$route['profile/subscribe'] = 'Profile/subscribe';
//profile/unSubscribe?msisdn=88xxx&service=xxxx
$route['profile/unSubscribe'] = 'Profile/unSubscribe';
//profile/renewSubscription?msisdn=88xxx&service=xxxx
$route['profile/renewSubscription'] = 'Profile/renewSubscription';   // Called by Java
//profile/charge?msisdn=88xxx&service=xxxx&package=xxxx
$route['profile/charge'] = 'Profile/charge';
//profile/chargeWithTac?msisdn=88xxx&transID=asdfsad&tac=1234
$route['profile/chargeWithTac'] = 'Profile/chargeWithTac';


//callback/aoc?aocTransID=RTS76969
$route['callback/aoc'] = 'Callback/aoc';
// POST Data
$route['callback/notifyUnsubscribedStatus'] = 'Callback/notifyUnsubscribedStatus';
// POST Data
$route['callback/notifySubscribedStatus'] = 'Callback/notifySubscribedStatus';


//sms/sendSMS?sender=2128383&receiver=8801842002206&message=Hello%20MIFE&senderName=innover
$route['sms/sendSMS'] = 'Message/sendSMS/0';
//sms/sendSMSNow?sender=2128383&receiver=8801842002206&message=Hello%20MIFE&senderName=innover
$route['sms/sendSMSNow'] = 'Message/sendSMS/1';


$route['getToken'] = 'Profile/getToken';