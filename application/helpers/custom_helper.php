<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**			Start 		**/

function in_array_r($item , $array){
    for ($i=0; $i<count($item); $i++) {
        if (in_array($item[$i], $array))
            return true;
    }
    return false;
}
/**			End 		**/


if (!function_exists('var_dump_pre')) {

    function var_dump_pre($var = array()) 
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }

}

if (!function_exists('print_r_pre')) 
{

    function print_r_pre($var = array()) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        die();
    }
}

if (!function_exists('validate_phone')) {

    function validate_phone($var) 
    {
         $ci=& get_instance();
         $var = str_replace("-", "", str_replace(" ", "", str_replace(")", "", str_replace("(", "", $var))));

        if(strlen($var) == $ci->config->item("valid_phone_length"))
        {
            $prefix = substr($var, 0, 5);
            if(in_array($prefix, $ci->config->item("valid_phone_prefix")))
            {
                return true;
            }
        }
        return false;
    }
}

if ( ! function_exists('array_string_to_sub_string'))
{
	function array_string_to_sub_string($string, $array, $is_act_screen) 
	{
	  if(!$array)
	      return false;      
	      
	      //echo $is_act_screen;
	   /*if($string == "system") {
	     echo $string; echo '<br>'; var_dump_pre($array);die();    
	   }*/
	  foreach ($array as $value) {
	      
	  	/*if(strtolower($value['act_screen']) == "system")
	  	{
	  		print_r_pre($value);
	  	}*/
	  	if($is_act_screen)
	  	{
	      if (trim($string) == strtolower(trim($value['act_screen']))) return true;
	      
	  	}
	  	else 
	  	{	  		
	  		if (trim($string) == strtolower(trim($value['act_name']))) return true;
	  	}
	     
	  }
	  return false;
	}
}

if ( ! function_exists('is_menu_permitted'))
{
    function is_activity_permitted($menu_name, $permission_array = array(), $is_act_screen = 1)
    {
    	if(!$permission_array)
    		return false;
    	else if(array_string_to_sub_string(strtolower($menu_name),$permission_array, $is_act_screen) !== false)
    		return true;
    	else
    		return false;
    }

}

if (!function_exists('responsJson')) {
    function responsJson($var = array()) {
        header('Content-Type: application/json');
        return json_encode($var);
    }
}


if (!function_exists('var_dump_pre')) {

    function var_dump_pre($var = array()) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }

}

if (!function_exists('print_r_pre')) {

    function print_r_pre($var = array()) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        die();
    }
}

if ( ! function_exists('is_menu_permitted'))
{
    function is_menu_permitted($menu_name, $permission_array = array())
    {
        if(!$permission_array)
            return false;
        else if(array_string_to_sub_string(strtolower($menu_name),$permission_array) !== false)
            return true;
        else
            return false;
    }

}

if (!function_exists('permitted_content')) {
    function permitted_content($menu_name, $permission_array = array()) {
        if (!$permission_array)
            return false;

        $ret = array();
        foreach ($permission_array as $key => $result) {
            if ($result['menu_name'] == strtolower($menu_name)) {
                $ret[] = $result['content_name'];
            }
        }
        return $ret;
    }
}

if ( ! function_exists('array_string_to_sub_string'))
{
 function array_string_to_sub_string($string, $array) {
  if(!$array)
      return false;      
     //echo $string; echo '<br>'; var_dump_pre($array);die();     
  foreach ($array as $value) {
      if($value['check_full_path'] == '1'){
      if ($string == $value['menu_name']){
       return true;
      }
     }
      else if (strpos($string, $value['menu_name']) !== false){
      return true;
     }
  }
  return false;
 }
}

if ( ! function_exists('clean_string')){
  function clean_string($string,$clean_data) {
        $string = str_replace(' ', ' ', $string);
        $string = str_replace('-', '', $string);
        $string = preg_replace('/[^A-Za-z0-9\-ığşçöüÖÇŞİıĞ]/', $clean_data, $string);
        return preg_replace('/-+/', '-', $string);
    }
}

 
if (!function_exists('password_check')){
    function password_check($password,$retypepassword)
    {
        if (strlen($password) < 8) {
            return 'Passwords must meet complexity requirements';
        }else{
            preg_match_all ("/[A-Z]/", $password, $matches);
            $uppercase = count($matches[0]);

            preg_match_all ("/[a-z]/", $password, $matches);
            $lowercase = count($matches[0]);

            preg_match_all ("/\d/i", $password, $matches);
            $numbers = count($matches[0]);

            preg_match_all ("/[^A-z0-9]/", $password, $matches);
            $special = count($matches[0]);

            if ($uppercase <= 0) {
                return 'Passwords must meet complexity requirements';
            } elseif ($lowercase <= 0) {
                return 'Passwords must meet complexity requirements';
            } elseif ($numbers <= 0) {
                return 'Passwords must meet complexity requirements';
            } elseif ($special <= 0) {
                return 'Passwords must meet complexity requirements';
            } elseif ($retypepassword != $password) {
                return 'Passwords must meet complexity requirements';
            }
            else  return true;
        }
    }
}

if (!function_exists('generate_password')) {
    function generate_password(){
        //$low =substr(str_shuffle("qwertyuipasdfghjklzxcvbnm"),0,2);
        $up =substr(str_shuffle("ABCDEFGHIJKLMNPQRSTUVWXYZ"),0,4);
        $num =substr(str_shuffle("123456789"),0,2);
        $cr =substr(str_shuffle("@&*$%"),0,2);
        return $num.$up.$cr;
}
}


//----Date String Convert To Show Specific Format Start--//
if (!function_exists('CustomDateFormat')){
    function CustomDateFormat($date='')
    {
        $new_date='';
        if(!empty($date))
        {
            $new_date=date('Y-m-d H:i:s',strtotime($date));
        }

        return $new_date;
    }
}
//----Date String Convert To Show Specific Format End--//
  
if (!function_exists('weekends')) {
    function weekends(){
        $days = array('0'=>'Saturday','2'=>'Sunday','3'=>'Monday','4'=>'Tuesday','5'=>'Wednesday','6'=>'Thursday','7'=>'Friday');
        return $days;
    }
}

   

/* global time format convertion start */

if ( ! function_exists('number_digit_check_validate'))
{
    function number_digit_check_validate($str = '')
    {
        $returnANS = 0;
        if(ctype_digit($str))
        {
            
            $stringLength=strlen($str);
            if($stringLength==11)
            {
                $returnANS = 1;
            }
            else
            {
                $returnANS = 0;
            }
        }
        
        return $returnANS;
    }

}
if ( ! function_exists('get_minutes'))
{
    function get_minutes($starts,$ends) {  
        $start    = new DateTime($starts);
        $end      = new DateTime($ends);
        $interval = new DateInterval('PT30M');
        $period   = new DatePeriod($start, $interval, $end);
        $minutes  = array();
        $minute  = array();
        foreach ($period as $row => $dt)
        {
            $minutes[$row]['one'] = $dt->format('Y-m-d H:i:s');
            $minutes[$row]['two'] = $dt->format('Y-m-d H:i:s');
        }
          
       return $minutes;  
    }  
}


if ( ! function_exists('get_cuntry_code')){
    function get_cuntry_code($cid)
    {
         if($cid==1){
            return "65";
         }elseif ($cid==2) {
            return "88";
         }elseif ($cid==3) {
            return "91";
         }
    }
}

/* global time format convertion end  */


if ( ! function_exists('get_status_by_name')){
    function get_status_by_name($mid)
    {
         if($mid==0){
            return "<span style='color: #5bc0de'>Pending</span>";
         }elseif ($mid==1) {
            return "<span style='color: #01579B'>Processing</span>";
         }elseif ($mid==2) {
            return "<span style='color: #4CAF50'>Complete</span>";
         }elseif ($mid==3) {
            return "<span style='color: #D50000'>Cancelled</span>";
         }else{
            return "<span style='color: #D50000'>Refunded</span>";
         }
    }
}
if ( ! function_exists('get_all_statatus_list')){
    function get_all_statatus_list()
    {
        $arrayName = array('1' => 'New','2'=>'processing','3'=>'successful','5'=>'Fail' );
        return $arrayName;
    }
}

if ( ! function_exists('get_operator_code')){
    function get_operator_code($number)
    {
         if($number==88017){
            return "1";
         }elseif ($number==88019) {
            return "2";
         }elseif ($number==88018) {
            return "3";
         }elseif ($number==88016) {
            return "4";
         }elseif ($number==88015) {
            return "5";
         }
    }
}


if ( ! function_exists('get_time_duration')){
    function get_time_duration($startDate,$endDate)
    {
        $start_date = new DateTime($startDate);
        $end_date = new DateTime($endDate);
        $duration = date_diff($end_date, $start_date);

        if($duration->h==0){
            if($duration->i < 10)
            {
                return "This customer next payment can be done after 10.00 minutes of last successful payment with same amount . ( last request : ".$duration->i." Min, ".$duration->s." Sec)";
            }else{
                return true;
            }
        }else{
            return true;
        }
    }
}


if ( ! function_exists('getColor')){
    function getColor($mid)
    {
         if($mid==1){
            return "bg-primary";
         }elseif ($mid==2) {
            return "bg-warning";
         }elseif ($mid==3) {
            return "bg-success";
         }elseif ($mid==4) {
            return "bg-info ";
         }elseif ($mid==5) {
            return "bg-danger";
         }elseif ($mid==6) {
            return "bg-inverse p-10";
         }elseif ($mid==7) {
            return "bg-default p-10";
         }elseif ($mid==8) {
            return "bg-warning";
         }elseif ($mid==9) {
            return "bg-warning";
         }elseif ($mid==10) {
            return "bg-warning";
         }elseif ($mid==23) {
            return "bg-warning";
         }elseif ($mid==11) {
            return "bg-warning";
         }elseif ($mid==24) {
            return "bg-warning";
         }elseif ($mid==25) {
            return "bg-warning";
         }
    }
}
if ( ! function_exists('smsUrl')){
function smsUrl($number='',$msg='')
    {
        $user   = 'bms';
        $pass   = 'dhk@1234';
        $sender = 'care_robi';
        $url = "https://api.mobireach.com.bd/SendTextMessage?Username=".$user."&Password=".$pass."&From=".$sender."&To=".$number."&Message=".$msg;
        echo $url; die();
        $response = file_get_contents($url);
        $p = xml_parser_create();
        xml_parse_into_struct($p, $response, $vals, $index);
        xml_parser_free($p);
        $response_api_status = $vals[3]['type'];
        print_r_pre($vals);
       // return $url;
    }
  }

