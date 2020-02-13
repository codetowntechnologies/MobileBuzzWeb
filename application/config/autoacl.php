<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Maximum number of segments that Ar-acl should check
$config['segment_max']	= 3;
// variable session role id
$config['sess_role_var'] = "adminAL";
// default role: this role will applied if there is no role found
$config['default_role'] = "User";
// Page that need to be controlled
$CI =& get_instance();

// Page that need The Very Private Page (VPP) access control
$config['vpp_control'] = array(
    $CI->config->item('adminName').'/' => array(                       // the "module/controller/method/" to protect
        'allowed'    => array(1),                    // the allowed user role_id array (e.g. user role is 0, Admin role is 1)
        'vpp_sess_name'  => 'adminAL',          // variable session key for Very Private Page (VPP)
        'vpp_key'        => 5,          // number of segment in the uri that contain the information about vpp_sess_name (e.g. user_id)
        'error_uri'  => '/'.$CI->config->item('adminName')."/login",  // the url to redirect to on failure
        'error_msg'  => "acl_view_login_denied",
    ),

);
