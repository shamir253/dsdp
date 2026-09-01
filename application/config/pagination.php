<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['per_page'] = 10;
$config['uri_segment'] = 2;
$config['num_links'] = 2;

$config['full_tag_open'] = '<ul class="pagination pagination-sm">';
$config['full_tag_close'] = '</ul>';

$config['num_tag_open'] = '<li>';
$config['num_tag_close'] = '</li>';

$config['cur_tag_open'] = '<li class="active"><span>';
$config['cur_tag_close'] = '</span></li>';

$config['first_link'] = '&laquo F';
$config['first_link'] = FALSE;
$config['first_tag_open'] = '<li>';
$config['first_tag_close'] = '</li>';

$config['last_link'] = 'L &raquo';
$config['last_link'] = FALSE;
$config['last_tag_open'] = '<li>';
$config['last_tag_close'] = '</li>';

$config['prev_link'] = '&laquo;';
$config['prev_tag_open'] = '<li>';
$config['prev_tag_close'] = '</li>';

$config['next_link'] = '&raquo;';
$config['next_tag_open'] = '<li>';
$config['next_tag_close'] = '</li>';

/* End of file pagination.php */
/* Location: ./application/config/pagination.php */