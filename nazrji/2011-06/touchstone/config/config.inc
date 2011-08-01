<?php
/**
* 
* config file
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$ts_version = '4.0.1';
define('TOUCHSTONE', 'true');
define('DIR_SEPARATOR', '/');
$cfg_web_root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == DIR_SEPARATOR) ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . DIR_SEPARATOR;
$protocol = 'https://';

$news_msg = '';

// Local database
  $cfg_db_username = 'notts_nle';
  $cfg_db_passwd   = '';
  $cfg_db_database = 'touchstone';
  $cfg_db_host 	   = 'localhost';

// SMS Imports
  $cfg_sms_api = 'uon_saturn';
  
//LDAP
  $cfg_ldap_server        = 'iLDAP.nottingham.ac.uk';
  $cfg_ldap_search_dn     = 'OU=University,DC=intdir,DC=nottingham,DC=ac,DC=uk';
  $cfg_ldap_bind_rdn      = 'LDAP_Touchstone';
  $cfg_ldap_bind_password = 'T0uch5t0ne';
  $cfg_use_ldap           = true;

  
// Institutional email domains
// If using external authentication (e.g. LDAP) list the domains that will authenticate against the external system
// This will allow you to change the password of any users that do not match against those domains (e.g. external examiners)
$cfg_institutional_domains = array('rji.ac.uk', 'nottingham.ac.uk');
  
//Editor
  $cfg_editor_name = 'tinymce';
  $cfg_editor_javascript = "<script language=\"JavaScript\" src=\"/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n<script language=\"JavaScript\" src=\"/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_config.js\"></script>\n";

//Server specific configuration basaed on hostname.
switch (strtolower($_SERVER['HTTP_HOST'])) {
  case 'touchstone.nottingham.ac.uk':
    $cfg_install_type = '';
    error_reporting(0);           // Turn error reporting off for the live server 
    break;
  case 'touchstone.nottingham.ac.uk:8080':
    $cfg_db_database = 'staging';
    $cfg_install_type = '(Staging)';
    error_reporting(-1); 
    break;
  case 'touchstone2.nottingham.ac.uk':
    $cfg_install_type = '(backup)';
    error_reporting(-1); 
    break;
  case 'suivarro.nottingham.ac.uk':
    $cfg_install_type = '(dev)';
    error_reporting(-1); 
    break;
  case 'touchstone.local':
    $cfg_install_type = '(local)';
  	$cfg_db_passwd   = 'touchstone';
    error_reporting(-1); 
    break;
}

//Warnings
  $cfg_hour_warning = 10;       // Warning for summative exams
  
//Assistance
  $support_email = 'learning-team-support@nottingham.ac.uk';
  $emergency_support_numbers = array('07909 684985'=>'On Call member of staff','0115 846 6122'=>'Learning Technology Section');

//Global DEBUG OUTPUT
  if (isset($_SERVER['PHP_AUTH_USER']) AND ($_SERVER['PHP_AUTH_USER'] == 'cczab1' OR $_SERVER['PHP_AUTH_USER'] == 'brzsw3' OR $_SERVER['PHP_AUTH_USER'] == 'nazrji')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . 'touchstone/include/debug.inc';
  } else {
    $dbclass = 'mysqli';
  }
?>