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

$ts_version = '4.0';
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
  $cfg_sms_sources = array('&lt;No lookup&gt;'=>'','UK'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=uk','Malaysia'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia','China'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=china');
  
//LDAP
  $cfg_ldap_server        = 'iLDAP.nottingham.ac.uk';
  $cfg_ldap_search_dn     = 'OU=University,DC=intdir,DC=nottingham,DC=ac,DC=uk';
  $cfg_ldap_bind_rdn      = 'LDAP_Touchstone';
  $cfg_ldap_bind_password = 'T0uch5t0ne';
  $cfg_use_ldap           = true;

//Editor
  $cfg_editor_name = 'tinymce';
  $cfg_editor_javascript = "<script language=\"JavaScript\" src=\"/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n<script language=\"JavaScript\" src=\"/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_config.js\"></script>\n";

//Server specific configuration basaed on hostname.
switch (strtolower($_SERVER['HTTP_HOST'])) {
  case 'touchstone.nottingham.ac.uk':
    $cfg_install_type = '';
    break;
  case 'touchstone.nottingham.ac.uk:8080':
    $cfg_db_database = 'staging';
    $cfg_install_type = '(Staging)';
    break;
  case 'touchstone2.nottingham.ac.uk':
    $cfg_install_type = '(backup)';
    break;
  case 'suivarro.nottingham.ac.uk':
    $cfg_install_type = '(dev)';
    break;
  case 'touchstone.local':
    $cfg_install_type = '(local)';
  	$cfg_db_passwd   = 'touchstone';
    break;
}

//Warnings
  $cfg_hour_warning = 10;       // Warning for summative exams
  error_reporting(-1);          // PHP error reporting 
  
//Assistance
  $support_email = 'learning-team-support@nottingham.ac.uk';
  $emergency_support_numbers = array('07909 684985'=>'On Call member of staff','0115 846 6122'=>'Learning Technology Section','07581 178449'=>'Dr Simon Wilkinson');

//Global DEBUG OUTPUT
  if (isset($_SERVER['PHP_AUTH_USER']) AND ($_SERVER['PHP_AUTH_USER'] == 'cczab1' OR $_SERVER['PHP_AUTH_USER'] == 'brzsw3' OR $_SERVER['PHP_AUTH_USER'] == 'nazrji')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . 'touchstone/include/debug.inc';
  } else {
    $dbclass = 'mysqli';
  }
?>