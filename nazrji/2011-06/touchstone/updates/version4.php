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

  
  // Get a list of reviews where group = 'Yes'
  $group_reviews = $mysqli->prepare("SELECT DISTINCT paperID FROM standards_setting WHERE group_review = 'Yes' AND paperID > 0");
  $group_reviews->execute();
  $group_reviews->store_result();
  $group_reviews->bind_result($paperID);
  while($group_reviews->fetch()) {
    $group_list = '';
    // Get a list of other ANGOFF reviews for the paper
    $individual_reviews = $mysqli->prepare("SELECT DISTINCT setterID, std_set FROM standards_setting WHERE paperID = ? AND method = 'Modified Angoff' AND group_review = 'No'");
    $individual_reviews->bind_param('i', $paperID);
    $individual_reviews->execute();
    $individual_reviews->store_result();
    $individual_reviews->bind_result($setterID, $std_set);
    while($individual_reviews->fetch()) {
      // Add to list of user IDs/dates <user_id>,<date>;<user_id>,<date>
      $group_list .= $setterID . ',' . str_replace(array(' ', '-', ':'), '', $std_set) . ';';
    }
    $individual_reviews->close();  
    $group_list = rtrim($group_list, ';');
    
    // Update the group review setting group field to name/date string
    if($group_list != ''){
      $update = $mysqli->prepare("UPDATE standards_setting SET group_review = ? WHERE paperID = ? AND method = 'Modified Angoff' AND group_review = 'Yes'");
      $update->bind_param('si', $group_list, $paperID);
      $update->execute();
      $update->close();
      echo "<div>UPDATE standards_setting SET group_review = '$group_list' WHERE paperID = $paperID AND method = 'Modified Angoff' AND group_review = 'Yes'</div>\n";
    }
  }
  $group_reviews->close();  
  
  //Close the database
  $mysqli->close();
  ob_end_flush();
  echo "\nFinished!\n";
?>