<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config = array(
      'create/user' => array(
                array(
                        'field' => 'name',
                        'label' => 'Name',
                        'rules' => 'trim|required'
                ),
                array(
                        'field' => 'email',
                        'label' => 'E-mail',
                		'rules' => 'trim|required|valid_email|callback_chk_user_email'                         		     		                   
                ),
                array(
                        'field' => 'phone',
                        'label' => 'Phone Number',
                        'rules' => 'trim|required|callback_chk_user_phone'
                ),
                array(
                        'field' => 'username',
                        'label' => 'Username',
                        'rules' => 'trim|required|callback_chk_user_name'
                ),
                array(
                        'field' => 'password',
                        'label' => 'Password',
                        'rules' => 'trim|required|min_length[8]'
                ),
                array(
                        'field' => 'cnfpassword',
                        'label' => 'Password Confirmation',
                        'rules' => 'trim|matches[password]'
                )
        ),
      'update/user' => array(
                array(
                        'field' => 'name',
                        'label' => 'Name',
                        'rules' => 'trim|required'
                ),
                array(
                        'field' => 'email',
                        'label' => 'Email',
                		'rules' => 'trim|required|valid_email|callback_chk_user_email'                         		     		                   
                ),
                array(
                        'field' => 'phone',
                        'label' => 'Phone Number',
                        'rules' => 'trim|required|callback_chk_user_phone'
                )
        ),        
      'create/role' => array(
                array(
                        'field' => 'name',
                        'label' => 'Role Name',
                        'rules' => 'trim|required|callback_chk_role_name'
                )
        ),  
     'create/activity' => array(
        		array(
                        'field' => 'act_name',
                        'label' => 'Activity Name',
                        'rules' => 'trim|required'
                ),
                array(
                        'field' => 'act_screen',
                        'label' => 'Activity Screen Name',
                        'rules' => 'trim|required|callback_chk_act_screen'
                )      
        ),
     'map/user-role' => array(        		
                array(
                        'field' => 'user_id',
                        'label' => 'Username',
                        'rules' => 'trim|required'
                )      
        ),
     'map/role-activity' => array(        		
                array(
                        'field' => 'role_id',
                        'label' => 'Role Name',
                        'rules' => 'trim|required'
                )      
        ),
      'change/password' => array(        		
                array(
                        'field' => 'current_password',
                        'label' => 'Current_Password',
                        'rules' => 'trim|required|callback_chk_current_password'
                ),
                array(
                        'field' => 'new_password',
                        'label' => 'Password',
                        'rules' => 'trim|required|min_length[8]'
                ),
                array(
                        'field' => 'new_conf_password',
                        'label' => 'Password Confirmation',
                        'rules' => 'trim|matches[new_password]'
                )    
        ),
);