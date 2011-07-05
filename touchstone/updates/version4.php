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
  //require '../config/config.inc';
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
  
  // 29/06/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='selfenroll'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN selfenroll tinyint");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules ADD COLUMN selfenroll tinyint</div>\n";

    $adjust = $mysqli->prepare("UPDATE modules SET selfenroll=0");
    $adjust->execute();
    $adjust->close();
    echo "<div>UPDATE modules SET selfenroll=0</div>\n";
  }
  
  // 30/06/2011 - Change schools from text to integers
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='schoolid'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Add new integer column
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN schoolid int");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules ADD COLUMN schoolid int</div>\n";

    // Look up existing school names
    $schools = array();
    $sch_data = $mysqli->prepare("SELECT id, school FROM schools");
    $sch_data->execute();
    $sch_data->store_result();
    $sch_data->bind_result($schoolid, $school_name);
    while ($sch_data->fetch()) {
      $schools[$school_name] = $schoolid; 
    }
    $sch_data->close();
    
    // Populate the new field
    foreach($schools as $school_name=>$schoolid) {
      $adjust = $mysqli->prepare("UPDATE modules SET schoolid=? WHERE school=?");
      $adjust->bind_param('is', $schoolid, $school_name);
      $adjust->execute();
      $adjust->close();
    }
    // Drop the old textual column
    $adjust = $mysqli->prepare("ALTER TABLE modules DROP COLUMN school");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules DROP COLUMN school</div>\n";
  }
  

  // 04/07/2011 - Drop 'Faculty' column from users.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='faculty'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 1) {
    $adjust = $mysqli->prepare("ALTER TABLE users DROP COLUMN faculty");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE users DROP COLUMN faculty</div>\n";
  }
  
  // 04/07/2011 - Create new 'admin_access' table to hold which modules 'Admin' can access.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='admin_access' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='adminID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE admin_access (adminID int not null primary key auto_increment, userID int, schools_id int)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE admin_access (adminID int not null primary key auto_increment, userID int, schools_id int)</div>\n";
  }
  
  // 04/07/2011 - New table to handle forgotten password requests.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='password_tokens' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE password_tokens (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, token CHAR(16) NOT NULL, time DATETIME NOT NULL);");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE password_tokens (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, token CHAR(16) NOT NULL, time DATETIME NOT NULL);</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  
  //Close the database
  $mysqli->close();
  ob_end_flush();
  echo "\nFinished!\n";
?>