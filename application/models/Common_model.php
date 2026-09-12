<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model {
	
	protected $package_table = "dsdp.dbo.tbl_packages";
	protected $aoc_charging_details_table = "dsdp.dbo.tbl_charging";
    protected $tbl_subscriber_profile = "dsdp.dbo.tbl_subscriber_profile";
    protected $tbl_subscriber_profile_hisotry = "dsdp.dbo.tbl_subscriber_profile_hisotry";
    protected $callback_history = "dsdp.dbo.tbl_callback_history";

	protected $tbl_outbox = "dsdp.dbo.tbl_mife_outbox";

	function __construct(){
		 parent::__construct();	 
	}
}