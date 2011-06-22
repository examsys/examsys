<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  if (!defined('STDIN')) {
//    exit;
  }
  require_once '../config/config.inc';
  set_time_limit(0);
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
  echo "\nStarting update from version 4.0 to 4.1\n";
  ob_start();
  
  // 15/06/2011
  // Add index to improve performance for standards setting index page
  $result = $mysqli->prepare("SHOW INDEX FROM ebel");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    $adjust = $mysqli->prepare("ALTER TABLE ebel ADD INDEX SETTER_AND_DATE (setterID, date_set)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE ebel ADD INDEX SETTER_AND_DATE (setterID, date_set)</div>\n";
    ob_flush();
    flush();
  }
  
  // 16/06/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log_late' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 1) {
    $adjust = $mysqli->prepare("ALTER TABLE log_late DROP COLUMN year");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log_late DROP COLUMN year</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE log_late DROP COLUMN student_grade");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log_late DROP COLUMN student_grade</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE log_late DROP COLUMN ipaddress");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log_late DROP COLUMN ipaddress</div>\n";
  }

  // 16/06/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sys_errors' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='fixed'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN fixed datetime");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN fixed datetime</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN php_self text");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN php_self text</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN query_string text");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN query_string text</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN request_method enum('GET', 'HEAD', 'POST', 'PUT', 'DELETE')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN request_method enum('GET', 'HEAD', 'POST', 'PUT', 'DELETE')</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  //Close the database
  $mysqli->close();
  ob_end_flush();
  echo "\nFinished!\n";
?>