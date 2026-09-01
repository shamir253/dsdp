<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['message_success'] = '<div class="alert alert-block alert-success">
	<a class="close" data-dismiss="alert" href="#">×</a>
	<h4 class="alert-heading"><i class="fa fa-check-square-o"></i> Success!</h4>
	<p>
		%s
	</p>
</div>';

/*$config['message_error'] = '<div class="alert alert-danger fade in" xmlns="http://www.w3.org/1999/html">
	<a class="close" data-dismiss="alert" href="#">×</a>
	<strong"><i class="fa-fw fa fa-times"/> Failed!</strong>	
    %s	
    </div>';*/

$config['message_error'] = '<div class="alert alert-block alert-danger">
    <a class="close" data-dismiss="alert" href="#">×</a>
    <h4 class="alert-heading"><i class="fa fa-close"></i> Failed!</h4>
    <p>
    %s
    </p>
</div>';

$config['msg_invalid_login'] = 'Invalid Username or Password.';
$config['valid_phone_prefix'] = array("88017", "88018", "88019", "88015", "88016");
$config['valid_phone_length'] = 13;

$config['system_default_role_id'] = 1;
$config['dealer_default_role_id'] = 2;
$config['mms_default_role_id'] = 3;

$config['app_title'] = "Admin || Dashboard";

$config['global_action'] = array("login", "logout");

// Menu permission keep on session or not?
$config['menu_permission_on_session'] = false;

$config['admin_home_url'] = "org/dashboard";






