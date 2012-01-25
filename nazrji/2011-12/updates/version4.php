<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../config/config.inc.php';

// Override $cfg_web_root in case we're in a subdirectory
require_once '../include/path_functions.inc.php';
$cfg_web_root = get_root_path() . '/';
$cfg_root_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $cfg_web_root);

require_once '../classes/installutils.class.php';
require_once '../classes/passwordutils.class.php';
require_once '../classes/lang.class.php';
require_once $cfg_web_root . 'classes/dbutils.class.php';

$version = '4.2';

set_time_limit(0);

function convert_year($old_year) {
  $new_year = 1;
  switch ($old_year) {
    case 'year1':
      $new_year = 1;
      break;
    case 'year2':
      $new_year = 2;
      break;
    case 'year3':
    case 'cp1':
      $new_year = 3;
      break;
    case 'year4':
    case 'cp2':
      $new_year = 4;
      break;
    case 'year5':
    case 'cp3':
    case 'f1':
    case 'graduate':
      $new_year = 5;
      break;
    default:
      $new_year = 1;
  }
  return $new_year;
}
?>
<html>
  <head>
    <title>TouchStone 4.x to Rogō update Script</title>
    <style type="text/css">
      html {padding:0em; margin:0em; width:100%}
      body {padding:0em; margin:0em; width:100%; font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black}
      h1 {font-size:140%; color:#001979}
      h2 {font-size:120%; color:#001979}
      .error {color:red; font-weight:bold}
      .warning {float:none; color:red; padding-left: .5em; vertical-align:top}
      label {float:left; width:7.5em; padding-left:0em; text-align:left}
      p {clear:both}
      .submit {margin-left:42%; padding-top:2em}
      table {border:none}
      table.topbar {font-weight:bold; width:100%; border-collapse:collapse}
      .topbar td {background-color:#F1F5FB}
      .header {margin-top:1.5em; margin-bottom:0.5em; width:97%; color:#1E3287}
      .header hr {border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:97%}
      td.line {width:98%}
      input {width:150px}
      form {padding:1em}
      form div {padding-left:2em}
    </style>
    <script language="text/javascript" type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
    <script language="text/javascript" type="text/javascript" src="../js/jquery.validate.min.js"></script>
  </head>
  <body>
  <table class="topbar"> 
    <tr> 
      <td><div style="font-size:26pt; font-weight:bold; color:#001979">&nbsp;<?php echo $string['systemupdate']; ?></div><div style="position:relative; left:48px; top:-6px; font-size:10pt; color:#001979">version 4.x to <?php echo $version; ?></div></td> 
      <td style="text-align:right; padding-top:10px; padding-right:10px"><img src="../artwork/rogo_logo.gif" width="137" height="61" alt="Logo" border="0" />&nbsp;&nbsp;</td> 
    </tr> 
    <tr> 
      <td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td> 
    </tr> 
  </table>
<?php
if (!isset($_POST['update'])) {
?>
    <script>
      $(document).ready(function(){
          $("#installForm").validate();
      });
      
      $(document).ready(function() {
        $('#useLdap').change(function() {
            $('#ldapOptions').toggle();
          });
      });
    </script>
    <?php
    if (!InstallUtils::configFileIsWriteable()) {
      ?>
       <h2><?php echo $string['updatefromversion']; ?> 4.x to <?php echo $version; ?></h2>
       <div><?php echo $string['warning1']; ?></div>
       <div><?php echo $string['warning1']; ?></div>
      <?php
    } else {
      ?>    
      <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
      <div><?php printf($string['msg1'], $version); ?></div>
        <table class="header"><tr><td><nobr><?php echo $string['databaseadminuser']; ?></nobr></td><td class="line"><hr /></td></tr></table> 
          <div><?php echo $string['msg2']; ?></div>
          <br />
          <div><label for="mysql_admin_user"><?php echo $string['dbusername']; ?></label> <input type="text" value="" name="mysql_admin_user" class="required" minlength="2" /> </div>
          <div><label for="mysql_admin_pass"><?php echo $string['dbpassword']; ?></label> <input type="password" value="" name="mysql_admin_pass"/></div>
     
       <div class="submit"> <input type="submit" name="update" value="<?php echo $string['startupdate']; ?>" /> </div>
     </form>
    <?php
   }
   ?>
   </body>
   </html>
  <?php

} else {
  if (!isset($cfg_db_charset)) {
    $cfg_db_charset = 'latin1';
  }
  
  $mysqli = DBUtils::get_mysqli_link($cfg_db_host , $_POST['mysql_admin_user'], $_POST['mysql_admin_pass'], $cfg_db_database, $cfg_db_charset, $dbclass);

  if ($mysqli->connect_error) {
    echo "<div>Failded to contect to mysql using " . $_POST['mysql_admin_user'] . '' .  $_POST['mysql_admin_pass'] . '</div>';
    echo "</body>";
    echo "</html>";
    exit;
  }
  
  echo "\n<blockquote>\n<h1>Starting update from version 4.x to $version</h1>\n<ol>";
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
    echo "<li>ALTER TABLE ebel ADD INDEX SETTER_AND_DATE (setterID, date_set)</li>\n";
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
    echo "<li>ALTER TABLE log_late DROP COLUMN year</li>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE log_late DROP COLUMN student_grade");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log_late DROP COLUMN student_grade</li>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE log_late DROP COLUMN ipaddress");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log_late DROP COLUMN ipaddress</li>\n";
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
    echo "<li>ALTER TABLE sys_errors ADD COLUMN fixed datetime</li>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN php_self text");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE sys_errors ADD COLUMN php_self text</li>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN query_string text");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE sys_errors ADD COLUMN query_string text</li>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN request_method enum('GET', 'HEAD', 'POST', 'PUT', 'DELETE')");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE sys_errors ADD COLUMN request_method enum('GET', 'HEAD', 'POST', 'PUT', 'DELETE')</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // Get a list of reviews where group = 'Yes'
  $group_reviews = $mysqli->prepare("SELECT DISTINCT paperID FROM standards_setting WHERE group_review = 'Yes' AND paperID > 0");
  $group_reviews->execute();
  $group_reviews->store_result();
  $group_reviews->bind_result($paperID);
  while ($group_reviews->fetch()) {
    $group_list = '';
    // Get a list of other ANGOFF reviews for the paper
    $individual_reviews = $mysqli->prepare("SELECT DISTINCT setterID, std_set FROM standards_setting WHERE paperID = ? AND method = 'Modified Angoff' AND group_review = 'No'");
    $individual_reviews->bind_param('i', $paperID);
    $individual_reviews->execute();
    $individual_reviews->store_result();
    $individual_reviews->bind_result($setterID, $std_set);
    while ($individual_reviews->fetch()) {
      // Add to list of user IDs/dates <user_id>,<date>;<user_id>,<date>
      $group_list .= $setterID . ',' . str_replace(array(' ', '-', ':'), '', $std_set) . ';';
    }
    $individual_reviews->close();  
    $group_list = rtrim($group_list, ';');
    
    // Update the group review setting group field to name/date string
    if ($group_list != ''){
      $update = $mysqli->prepare("UPDATE standards_setting SET group_review = ? WHERE paperID = ? AND method = 'Modified Angoff' AND group_review = 'Yes'");
      $update->bind_param('si', $group_list, $paperID);
      $update->execute();
      $update->close();
      echo "<li>UPDATE standards_setting SET group_review = '$group_list' WHERE paperID = $paperID AND method = 'Modified Angoff' AND group_review = 'Yes'</li>\n";
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
    echo "<li>ALTER TABLE modules ADD COLUMN selfenroll tinyint</li>\n";

    $adjust = $mysqli->prepare("UPDATE modules SET selfenroll=0");
    $adjust->execute();
    $adjust->close();
    echo "<li>UPDATE modules SET selfenroll=0</li>\n";
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
    echo "<li>ALTER TABLE modules ADD COLUMN schoolid int</li>\n";

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
    echo "<li>ALTER TABLE modules DROP COLUMN school</li>\n";
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
    echo "<li>ALTER TABLE users DROP COLUMN faculty</li>\n";
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
    echo "<li>CREATE TABLE admin_access (adminID int not null primary key auto_increment, userID int, schools_id int)</li>\n";
  }
  
  // 04/07/2011 - New table to handle forgotten password requests.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='password_tokens' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE password_tokens (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, token CHAR(16) NOT NULL, time DATETIME NOT NULL);");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE password_tokens (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, token CHAR(16) NOT NULL, time DATETIME NOT NULL);</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 06/07/2011 - New table users_metadata.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users_metadata' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE users_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, userID INT, moduleID int, type varchar(255), value varchar(255), calendar_year enum('2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20'));");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE users_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, userID INT, moduleID int, type varchar(255), value varchar(255), calendar_year enum('2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20'));</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 11/07/2011 - Add new column for retiring papers.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='retired'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE properties ADD COLUMN retired datetime");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE properties ADD COLUMN retired datetime</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 25/07/2011 - New table paper_metadata_security.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='paper_metadata_security' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE paper_metadata_security (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, paperID int, name varchar(255), value varchar(255))");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE paper_metadata_security (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, paperID int, name varchar(255), value varchar(255))</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 27/07/2011 - New table questions_metadata.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions_metadata' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE questions_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, questionID int, type varchar(255), value varchar(255))");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE questions_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, questionID int, type varchar(255), value varchar(255))</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 01/08/2011 - Add new column for paperID in the errors table.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sys_errors' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='paperID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN paperID int");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE sys_errors ADD COLUMN paperID int</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 01/08/2011 - Add new column for paperID in the errors table.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sys_errors' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='post_data'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN post_data text");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE sys_errors ADD COLUMN post_data text</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
    
  //ADD new role based MySQL users
  $result = $mysqli->prepare("SELECT user FROM mysql.user WHERE user = '" . $cfg_db_database . "_stu'");
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_user);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $cfg_db_username = $cfg_db_database . '_auth';
    $cfg_db_password = PasswordUtils::gen_password(16);
    $cfg_db_student_user = $cfg_db_database . '_stu';
    $cfg_db_student_passwd = PasswordUtils::gen_password(16);
    $cfg_db_staff_user = $cfg_db_database . '_staff';
    $cfg_db_staff_passwd = PasswordUtils::gen_password(16);
    $cfg_db_external_user = $cfg_db_database . '_ext';
    $cfg_db_external_passwd  = PasswordUtils::gen_password(16);
    $cfg_db_sysadmin_user = $cfg_db_database . '_sys';
    $cfg_db_sysadmin_passwd = PasswordUtils::gen_password(16);
    
    $priv_SQL = array();
    //create 'database user authentication user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_username . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_password . "'");
    echo "<li>NEW DB USER:: $cfg_db_username created</li>";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $cfg_db_database . ".users TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE, INSERT ON " . $cfg_db_database . ".password_tokens TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".sid TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_modules TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".schools TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_metadata_security TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, DELETE ON " . $cfg_db_database . ".password_tokens TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users_metadata TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".labs TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".admin_access TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT,INSERT ON " . $cfg_db_database . ".temp_users TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $cfg_db_database . ".sys_errors TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";    
    
    
    //create 'database user student user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_student_user . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_student_passwd . "'");
    echo "<li>NEW DB USER:: $cfg_db_student_user created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_help TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".papers TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".options TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".feedback_release TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".ip_addresses TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".modules TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".objectives TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_metadata_security TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".relationships TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".student_modules TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".schools TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users_metadata TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".labs TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".question_exclude TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".sessions TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".sid TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".users TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_log TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_searches TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_tutorial_log TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log0 TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log1 TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log2 TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log3 TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4 TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4_overall TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log5 TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_late TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_metadata TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".temp_users TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $cfg_db_database . ".sys_errors TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";    
    $priv_SQL[] = "FLUSH PRIVILEGES";
 
    //create 'database user external user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_external_user . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_external_passwd . "'");
    echo "<li>NEW DB USER:: $cfg_db_external_user created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".papers TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".options TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".teams TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log0 TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log1 TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log2 TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log3 TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4 TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4_overall TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log5 TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_late TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_metadata TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".review_comments TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $cfg_db_database . ".sys_errors TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";  
    $priv_SQL[] = "FLUSH PRIVILEGES";
    
    //create 'database user staff user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_staff_user . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_staff_passwd . "'");
    echo "<li>NEW DB USER:: $cfg_db_staff_user created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".* TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".users TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".users_metadata TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sid TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".password_tokens TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".special_needs TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".student_modules TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".student_notes TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".papers TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".questions TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".questions_metadata TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".options TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".properties TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".feedback_release TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".paper_metadata_security TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".paper_notes TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".standards_setting TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".ebel TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".question_exclude TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".keywords_question TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".keywords_user TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".objectives TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".relationships TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".review_comments TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".recent_papers TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";  
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".folders TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".teams TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_log TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_searches TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_tutorial_log TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log0 TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log1 TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log2 TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log3 TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log4 TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log4_overall TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log5 TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_late TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_metadata TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".textbox_marking TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".textbox_remark TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".track_changes TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".temp_users TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";  
    
    $mysqli->query("CREATE USER  '" . $cfg_db_sysadmin_user . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_sysadmin_passwd . "'");
    echo "<li>NEW DB USER:: $cfg_db_sysadmin_user created</li>";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, DROP  ON " . $cfg_db_database . ".* TO '". $cfg_db_sysadmin_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    
    foreach ($priv_SQL as $sql) {
      $mysqli->query($sql);
      if ($mysqli->errno != 0) {
        echo '<li class="error">ERROR: could not set permissions ' . $sql . '</li>';
      }  
    }
    ////////////////////////////////////////////////////////////////////////////
    //
    //  update the config file!!
    //
    $new_cfg_str[] =  "// Local database\n";
    $new_cfg_str[] =  "  \$cfg_db_username = '$cfg_db_username';\n";
    $new_cfg_str[] =  "  \$cfg_db_passwd = '$cfg_db_password';\n";
    $new_cfg_str[] =  "  \$cfg_db_database = '$cfg_db_database';\n";
    $new_cfg_str[] =  "  \$cfg_db_host 	  = '$cfg_db_host';\n";
    $new_cfg_str[] =  "// student db user \n";
    $new_cfg_str[] =  "  \$cfg_db_student_user = '$cfg_db_student_user';\n";
    $new_cfg_str[] =  "  \$cfg_db_student_passwd = '$cfg_db_student_passwd';\n";
    $new_cfg_str[] =  "// staff db user\n";
    $new_cfg_str[] =  "  \$cfg_db_staff_user = '$cfg_db_staff_user';\n";
    $new_cfg_str[] =  "  \$cfg_db_staff_passwd = '$cfg_db_staff_passwd';\n";
    $new_cfg_str[] =  "// external examiner db user\n";
    $new_cfg_str[] =  "  \$cfg_db_external_user = '$cfg_db_external_user';\n";
    $new_cfg_str[] =  "  \$cfg_db_external_passwd = '$cfg_db_external_passwd';\n";
    $new_cfg_str[] =  "// sysdamin db user\n";
    $new_cfg_str[] =  "  \$cfg_db_sysadmin_user = '$cfg_db_sysadmin_user';\n";
    $new_cfg_str[] =  "  \$cfg_db_sysadmin_passwd = '$cfg_db_sysadmin_passwd';\n";
    
    $cfg = file($cfg_web_root . 'config/config.inc.php');

    //remove refrances to old vars
    $cfg_new = array();
    $remove_array = array('Local database', 'cfg_db_username', 'cfg_db_passwd', 'cfg_db_database', 'cfg_db_host');
    foreach ($cfg as $line) {
       $remove = false;
       foreach ($remove_array as $needle) {
         if (stripos($line,$needle) !== false) {
            $remove = true;
            break 1;
         }
       }
       if (!$remove) {
         $cfg_new[] = $line;
       }
    }
        
    //add the new config chunk
    array_splice($cfg_new,18,0,$new_cfg_str);
        
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root. 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old1.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    ///////////////////////  update the config file!! //////////////////////////////////////
    
  } // END Create DB user
  
  // 26/08/2011 - Add date and time formats to config file.
  $new_cfg_str[] =  "// Date formats in MySQL DATE_FORMAT format\n";
  $new_cfg_str[] =  "  \$cfg_short_date = '%m/%d/%y';\n";
  $new_cfg_str[] =  "  \$cfg_long_date_time = '%m/%d/%Y %H:%i';\n";
  $new_cfg_str[] =  "  \$cfg_timezone = 'Europe/London';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'Date formats in MySQL DATE_FORMAT') !== false) {
      $found = true;
    }
  }

  if (!$found) {
    array_splice($cfg,36,0,$new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old2.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<div>Added date and time formats to config file.</div>\n";
    ob_flush();
    flush();
  }
  
  // 05/09/2011 - Add company name config file.
  $new_cfg_str = array();
  $new_cfg_str[] =  "\$cfg_company = 'The University of Nottingham';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_company') !== false) {
      $found = true;
    }
  }
  
  if (!$found) {
    array_splice($cfg,16,0,$new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added company name config file.</li>\n";
    ob_flush();
    flush();
  }
  
  // 01/08/2011 - Change to database structure for more flexible marking
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='display_method'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE questions CHANGE COLUMN score_method display_method text");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE questions CHANGE COLUMN score_method display_method text</li>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE questions ADD COLUMN score_method enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark')");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE questions ADD COLUMN score_method enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark')</li>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("UPDATE questions SET score_method = 'Mark per Option' WHERE q_type != 'Calculation'");
    $adjust->execute();
    $adjust->close();
    
    $adjust = $mysqli->prepare("UPDATE questions SET score_method = 'Mark per Question' WHERE q_type = 'Calculation'");
    $adjust->execute();
    $adjust->close();
    
    // Update the BonusMark setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='BonusMark'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Bonus Mark' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    // Update the StrictOrder setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='StrictOrder'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Mark per Option' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    // Update the AllItemsCorrect setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='AllItemsCorrect'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Mark per Question' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    
    // Update the SelectedPositive setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='SelectedPositive'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Mark per Option' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    // Update the OrderNeighbours setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='OrderNeighbours'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Allow partial Marks' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    $adjust = $mysqli->prepare("ALTER TABLE options CHANGE COLUMN marks marks_correct float");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE options CHANGE COLUMN marks marks_correct float</li>\n";
    ob_flush();
    flush();

    $adjust = $mysqli->prepare("ALTER TABLE options ADD COLUMN marks_incorrect float");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE options ADD COLUMN marks_incorrect float</li>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE options ADD COLUMN marks_partial float");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE options ADD COLUMN marks_partial float</li>\n";
    ob_flush();
    flush();

    $adjust = $mysqli->prepare("UPDATE options SET marks_incorrect=0");
    $adjust->execute();
    $adjust->close();

    $adjust = $mysqli->prepare("UPDATE options SET marks_partial=0");
    $adjust->execute();
    $adjust->close();
    
    // Update options for negative marking
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='TF_NegativeAbstain' OR display_method='YN_NegativeAbstain'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE options SET marks_incorrect=-1 WHERE o_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();

    // Update options for half-negative marking
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='TF_NegativeAbstainHalf'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE options SET marks_incorrect=-0.5 WHERE o_id=$q_id");
      $adjust->execute();
      $adjust->close();

      $adjust = $mysqli->prepare("UPDATE questions SET display_method='TF_NegativeAbstain' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
  }
  $result->close();

  // 01/08/2011 - Change to database structure for more flexible marking
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='schools' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='facultyID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE schools ADD COLUMN facultyID int");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE schools ADD COLUMN facultyID int</li>\n";
    ob_flush();
    flush();
    
    // Populate the new field with Faculty IDs
    $q_data = $mysqli->prepare("SELECT id, name FROM faculty");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($faculty_id, $faculty_name);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE schools SET facultyID=$faculty_id WHERE faculty='$faculty_name'");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    $adjust = $mysqli->prepare("ALTER TABLE schools DROP COLUMN faculty");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE schools DROP COLUMN faculty</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 10/08/2011 - Add new column for negative marking setting for modules.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='neg_marking'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN neg_marking TINYINT(1)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE modules ADD COLUMN neg_marking TINYINT(1)</li>\n";
    ob_flush();
    flush();
    $adjust = $mysqli->prepare("UPDATE modules SET neg_marking=1");
    $adjust->execute();
    $adjust->close();
  }
  $result->close();

  // 08/09/2011 - Add field to Modules table to hold which Ebel grid template to use.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='ebel_grid_template'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN ebel_grid_template int");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE modules ADD COLUMN ebel_grid_template int</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 15/08/2011 - Add new table to hold Ebel grid templates.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='ebel_grid_templates' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE ebel_grid_templates (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, EE tinyint, EI tinyint, EN tinyint, ME tinyint, MI tinyint, MN tinyint, HE tinyint, HI tinyint, HN tinyint, EE2 tinyint, EI2 tinyint, EN2 tinyint, ME2 tinyint, MI2 tinyint, MN2 tinyint, HE2 tinyint, HI2 tinyint, HN2 tinyint, name varchar(255))");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE ebel_grid_templates (id INT NOT NULL PRIMARY_KEY AUTO INCREMENT, EE tinyint, EI tinyint, EN tinyint, ME tinyint, MI tinyint, MN tinyint, HE tinyint, HI tinyint, HN tinyint, EE2 tinyint, EI2 tinyint, EN2 tinyint, ME2 tinyint, MI2 tinyint, MN2 tinyint, HE2 tinyint, HI2 tinyint, HN2 tinyint, name varchar(255))</li>\n";
    ob_flush();
    flush();
    
    if (strpos(strtolower($_SERVER['HTTP_HOST']), 'nottingham.ac.uk') !== false) {
      $sql = array();
      $sql[] = "INSERT INTO `ebel_grid_templates` (id,EE,EI,EN,ME,MI,MN,HE,HI,HN,EE2,EI2,EN2,ME2,MI2,MN2,HE2,HI2,HN2,name) VALUES (1,65,60,55,60,55,50,55,50,45,0,0,0,0,0,0,0,0,0,'BMedSci')";
      $sql[] = "INSERT INTO `ebel_grid_templates` (id,EE,EI,EN,ME,MI,MN,HE,HI,HN,EE2,EI2,EN2,ME2,MI2,MN2,HE2,HI2,HN2,name) VALUES (2,80,60,55,55,50,35,45,35,30,0,0,0,0,0,0,0,0,0,'BMBS')";
      $sql[] = "UPDATE modules SET ebel_grid_template=1 WHERE vle_api = 'NLE'";
      $sql[] = "UPDATE modules SET ebel_grid_template=2 WHERE moduleid IN ('A13CLP','A14CHH','A14DOO','A14HCE','A14ONG','A14PSY','A14ACE')";
      
      foreach ($sql as $q) {
        $adjust = $mysqli->prepare($q);
        $adjust->execute();
        $adjust->close();
      }
      echo "<li>Populating ebel_grid_templates with Nottingham data.";
    }

  }
  $result->close();

  // 01/09/2011 - Fix 'question' foreign key field in 'papers' not being big enough to hold a question ID!
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='papers' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='question'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();
  
  if (strpos($column_type,'smallint') !== false) {
    $result = $mysqli->prepare("ALTER TABLE papers CHANGE COLUMN question question INT(4) UNSIGNED NOT NULL DEFAULT 0;");
    $result->execute();
    $result->close();
    echo "<li>ALTER TABLE papers CHANGE COLUMN question question INT(4) UNSIGNED NOT NULL DEFAULT 0;</li>\n";
    ob_flush();
    flush();
  }
  
  // 13/09/2011 - Convert MRQs of type '1 Mark per option with negative marking' to Dichotomous
  // They are functionally equivalent but this MRQ type doesn't fit well into the new marking scheme
  // Also needs to update the logs, although in practice there are _very_ few of these questions on live papers
  $result = $mysqli->prepare("SELECT q_id FROM questions WHERE q_type='mrq' AND display_method='AllNegative'");
  $result->execute();
  $result->store_result();
  $result->bind_result($questionID);
  while ($result->fetch()) {
    // Update Logs
    $check = $mysqli->prepare("SELECT * FROM log0 WHERE q_id=? AND user_answer LIKE '%y%'");
    $check->bind_param('i', $questionID);
    $check->execute();
    $check->store_result();
    $check->fetch();
    if ($check->num_rows() > 0) {
      $update_log0_t = $mysqli->prepare("UPDATE log0 SET user_answer=REPLACE(user_answer, 'y', 't') WHERE q_id=? AND user_answer LIKE '%y%'");
      $update_log0_t->bind_param('i', $questionID);
      $update_log0_t->execute();
      $update_log0_t->close();
      echo "<li>UPDATE log0 SET user_answer=REPLACE(user_answer, 'y', 't') WHERE q_id={$questionID} AND user_answer LIKE '%y%'</li>\n";
      ob_flush();
      flush();
    }
    $check->close();
    
    $check = $mysqli->prepare("SELECT * FROM log0 WHERE q_id=? AND user_answer LIKE '%n%'");
    $check->bind_param('i', $questionID);
    $check->execute();
    $check->store_result();
    $check->fetch();
    if ($check->num_rows() > 0) {
      $update_log0_f = $mysqli->prepare("UPDATE log0 SET user_answer=REPLACE(user_answer, 'n', 'f') WHERE q_id=? AND user_answer LIKE '%n%'");
      $update_log0_f->bind_param('i', $questionID);
      $update_log0_f->execute();
      $update_log0_f->close();
      echo "<li>UPDATE log0 SET user_answer=REPLACE(user_answer, 'n', 'f') WHERE q_id={$questionID} AND user_answer LIKE '%n%'</li>\n";
      ob_flush();
      flush();
    }
    $check->close();
    
    $check = $mysqli->prepare("SELECT * FROM log2 WHERE q_id=? AND user_answer LIKE '%y%'");
    $check->bind_param('i', $questionID);
    $check->execute();
    $check->store_result();
    $check->fetch();
    if ($check->num_rows() > 0) {
      $update_log2_t = $mysqli->prepare("UPDATE log2 SET user_answer=REPLACE(user_answer, 'y', 't') WHERE q_id=? AND user_answer LIKE '%y%'");
      $update_log2_t->bind_param('i', $questionID);
      $update_log2_t->execute();
      $update_log2_t->close();
      echo "<li>UPDATE log2 SET user_answer=REPLACE(user_answer, 'y', 't') WHERE q_id={$questionID} AND user_answer LIKE '%y%'</li>\n";
      ob_flush();
      flush();
    }
    $check->close();
  
    $check = $mysqli->prepare("SELECT * FROM log2 WHERE q_id=? AND user_answer LIKE '%n%'");
    $check->bind_param('i', $questionID);
    $check->execute();
    $check->store_result();
    $check->fetch();
    if ($check->num_rows() > 0) {
      $update_log2_f = $mysqli->prepare("UPDATE log2 SET user_answer=REPLACE(user_answer, 'n', 'f') WHERE q_id=? AND user_answer LIKE '%n%'");
      $update_log2_f->bind_param('i', $questionID);
      $update_log2_f->execute();
      $update_log2_f->close();
      echo "<li>UPDATE log2 SET user_answer=REPLACE(user_answer, 'n', 'f') WHERE q_id={$questionID} AND user_answer LIKE '%n%'</li>\n";
      ob_flush();
      flush();
    }
    $check->close();
    
    $update_o_t = $mysqli->prepare("UPDATE options SET correct='t', marks_correct=1, marks_incorrect=-1 WHERE o_id=? AND correct='y'");
    $update_o_t->bind_param('i', $questionID);
    $update_o_t->execute();
    $update_o_t->close();
    ob_flush();
    flush();
    
    $update_o_f = $mysqli->prepare("UPDATE options SET correct='f', marks_correct=1, marks_incorrect=-1 WHERE o_id=? AND correct='n'");
    $update_o_f->bind_param('i', $questionID);
    $update_o_f->execute();
    $update_o_f->close();
    ob_flush();
    flush();
    
    $update_q = $mysqli->prepare("UPDATE questions SET q_type='dichotomous', display_method='TF_Positive', score_method='Mark per Option' WHERE q_id=?");
    $update_q->bind_param('i', $questionID);
    $update_q->execute();
    $update_q->close();
    ob_flush();
    flush();
  }
  $result->close();

  // 20/09/2011 - set marks for fill-in-the-blank question tyoe
  $adjust = $mysqli->prepare("UPDATE options SET marks_correct=1, marks_incorrect=0 WHERE o_id IN (SELECT q_id FROM questions WHERE q_type='blank') AND (marks_correct IS NULL OR marks_correct=0)");
  $adjust->execute();
  $adjust->close();
  ob_flush();
  flush();
  
  // 15/09/2011 Update calculation questions so that they have two tolerances, one for full marks the other for partial
  $result = $mysqli->prepare("SELECT q_id, display_method FROM questions WHERE q_type='calculation'");
  $result->execute();
  $result->store_result();
  $result->bind_result($questionID, $display_method);
  while ($result->fetch()) {
    $old_method_parts = explode(',', $display_method);
    if (count($old_method_parts) == 3) {
      $new_method_parts = array($old_method_parts[0], $old_method_parts[1], 0, $old_method_parts[2]);
      $new_method = implode(',', $new_method_parts);
      
      $update_q = $mysqli->prepare("UPDATE questions SET display_method=? WHERE q_id=?");
      $update_q->bind_param('si', $new_method, $questionID);
      $update_q->execute();
      $update_q->close();
      ob_flush();
      flush();
    }
  }
  $result->close();
  
  // 22/09/2011 - remove timedate question type
  $check = $mysqli->prepare("SELECT * FROM questions WHERE q_type='timedate'");
  $check->execute();
  $check->store_result();
  $check->fetch();
  if ($check->num_rows() > 0) {
    $adjust = $mysqli->prepare("UPDATE questions SET q_type='textbox', display_method='40x1' WHERE q_type='timedate'");
    $adjust->execute();
    $adjust->close();
    ob_flush();
    flush();
  }
  
  // 01/09/2011 - Remove the time/date question type
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='q_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();
  if ($column_type == "enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','timedate','info','extmatch','random','sct','keyword_based')") {
    $adjust = $mysqli->prepare("ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based')");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based')</li>\n";
    ob_flush();
    flush();
  }

  //26/09/2011
  $check = $mysqli->prepare("SELECT leadin FROM questions WHERE leadin LIKE '%[tex]%[/tex]%'");
  $check->execute();
  $check->store_result();
  $check->fetch();
  if ($check->num_rows() > 0) {
    $sql = array();
    $sql[] = "UPDATE questions set leadin = REPLACE(REPLACE(leadin,'[tex]','<div class=\"mee\">'),'[/tex]','</div>') WHERE leadin LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE questions set theme = REPLACE(REPLACE(theme,'[tex]','<span class=\"mee\">'),'[/tex]','</span>') WHERE theme LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE questions set scenario = REPLACE(REPLACE(scenario,'[tex]','<span class=\"mee\">'),'[/tex]','</span>') WHERE scenario LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE questions set correct_fback = REPLACE(REPLACE(correct_fback,'[tex]','<span class=\"mee\">'),'[/tex]','</span>') WHERE correct_fback LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE questions set incorrect_fback = REPLACE(REPLACE(incorrect_fback,'[tex]','<span class=\"mee\">'),'[/tex]','</span>') WHERE incorrect_fback LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE questions set notes = REPLACE(REPLACE(notes,'[tex]','<span class=\"mee\">'),'[/tex]','</span>') WHERE notes LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE questions set scenario_plain = REPLACE(REPLACE(scenario_plain,'[tex]',''),'[/tex]','') WHERE scenario_plain LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE questions set leadin_plain = REPLACE(REPLACE(leadin_plain,'[tex]',''),'[/tex]','') WHERE leadin_plain LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE options set option_text = REPLACE(REPLACE(option_text,'[tex]','<span class=\"mee\">'),'[/tex]','</span>') WHERE option_text LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE options set feedback_right = REPLACE(REPLACE(feedback_right,'[tex]','<span class=\"mee\">'),'[/tex]','</span>') WHERE feedback_right LIKE '%[tex]%[/tex]%'";
    $sql[] = "UPDATE options set feedback_wrong = REPLACE(REPLACE(feedback_wrong,'[tex]','<span class=\"mee\">'),'[/tex]','</span>') WHERE feedback_wrong LIKE '%[tex]%[/tex]%'";
    foreach ($sql as $q) {
      $adjust = $mysqli->prepare($q);
      $adjust->execute();
      $adjust->close();
      //echo "<div>Replacing [tex] " . htmlspecialchars($q) . "</div>";
      ob_flush();
      flush();
    }
  }

  // 30/09/2011 - Update to the format of Labelling questions
  $result = $mysqli->prepare("SELECT o.o_id, o.correct FROM options o INNER JOIN questions q ON o.o_id=q.q_id WHERE q.q_type='labelling' AND (o.correct NOT LIKE '%single;label%' AND o.correct NOT LIKE '%multiple;label%' AND o.correct NOT LIKE '%single;menu%')");
  $result->execute();
  $result->store_result();
  $result->bind_result($o_id, $correct);
  while ($result->fetch()) {
    $parts = explode(';', $correct);
    if (count($parts) > 1) {
      $new_correct = $parts[0] . ';' . $parts[1] . ';' . $parts[2] . ';' . $parts[3] . ';' . $parts[4] . ';' . $parts[5] . ';' . $parts[6] . ';0;0;';
      if ($parts[7] == 'single') {
        $new_correct .= 'single;label';
      } elseif ($parts[7] == 'multiple') {
        $new_correct .= 'multiple;label';
      } else {
        $new_correct .= 'single;menu';
      }
      for ($i=8; $i<count($parts); $i++) {
        $new_correct .= ';' . $parts[$i];
      }
      
      $adjust = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
      $adjust->bind_param('si', $new_correct, $o_id);
      $adjust->execute();
      $adjust->close();
    }
  }
  if ($result->num_rows > 0) echo "<li>Updated the format of Labelling questions</li>";
  $result->close();
  
  //ADD new role based MySQL users - 10/10/2011
  $result = $mysqli->prepare("SELECT user FROM mysql.user WHERE user = '" . $cfg_db_database . "_sct'");
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_user);
  $result->fetch();
  if ($result->num_rows() == 0) {
    
    $cfg_db_sct_username = $cfg_db_database . '_sct';
    $cfg_db_sct_password = PasswordUtils::gen_password(16);
        
    $priv_SQL = array();
    //create 'database user SCT user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_sct_username . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_sct_password . "'");
    echo "<li>NEW DB USER:: $cfg_db_sct_username created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".papers TO '". $cfg_db_sct_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions TO '". $cfg_db_sct_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions_metadata TO '". $cfg_db_sct_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".options TO '". $cfg_db_sct_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '". $cfg_db_sct_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_metadata_security TO '". $cfg_db_sct_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_notes TO '". $cfg_db_sct_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sct_reviews TO '". $cfg_db_sct_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    
    foreach ($priv_SQL as $sql) {
      $mysqli->query($sql);
      if ($mysqli->errno != 0) {
        echo '<li class="error">ERROR: could not set permissions ' . $sql . '</li>';
      }  
    }
    ////////////////////////////////////////////////////////////////////////////
    //
    //  update the config file!!
    //

    $new_cfg_str = array();
    $new_cfg_str[] =  "// SCT db user\n";
    $new_cfg_str[] =  "  \$cfg_db_sct_user = '$cfg_db_sct_username';\n";
    $new_cfg_str[] =  "  \$cfg_db_sct_passwd = '$cfg_db_sct_password';\n";
    
    $cfg = file($cfg_web_root . 'config/config.inc.php');

    //add the new config chunk
    array_splice($cfg, 36, 0, $new_cfg_str);
    
    
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old1.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    ///////////////////////  update the config file!! //////////////////////////////////////
    
  } // END Create SCT user
  
  $result = $mysqli->prepare("SELECT user FROM mysql.user WHERE user = '" . $cfg_db_database . "_inv'");
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_user);
  $result->fetch();
  if ($result->num_rows() == 0) {
    
    $cfg_db_inv_username = $cfg_db_database . '_inv';
    $cfg_db_inv_password = PasswordUtils::gen_password(16);
        
    $priv_SQL = array();
    //create 'database user SCT user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_inv_username . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_inv_password . "'");
    echo "<li>NEW DB USER:: $cfg_db_inv_username created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_modules TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".sid TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".ip_addresses TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".labs TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".student_notes TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".paper_notes TO '". $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    
    foreach ($priv_SQL as $sql) {
      $mysqli->query($sql);
      if ($mysqli->errno != 0) {
        echo '<li class="error">ERROR: could not set permissions ' . $sql . '</li>';
      }  
    }
    ////////////////////////////////////////////////////////////////////////////
    //
    //  update the config file!!
    //

    $new_cfg_str = array();
    $new_cfg_str[] =  "// Invigilator user\n";
    $new_cfg_str[] =  "  \$cfg_db_inv_user = '$cfg_db_inv_username';\n";
    $new_cfg_str[] =  "  \$cfg_db_inv_passwd = '$cfg_db_inv_password';\n";
    
    $cfg = file($cfg_web_root . 'config/config.inc.php');

    //add the new config chunk
    array_splice($cfg, 36, 0, $new_cfg_str);
    
    
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old1.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    ///////////////////////  update the config file!! //////////////////////////////////////
    
  } // END Create DB user
  
  // 12/10/2011 - Add encrypted name for a paper.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='crypt_name'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE properties ADD COLUMN crypt_name varchar(32)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE properties ADD COLUMN crypt_name varchar(32)</li>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE properties ADD INDEX crypt_name_idx (crypt_name)");
    $adjust->execute();
    $adjust->close();
    
    $result2 = $mysqli->prepare("SELECT property_id, UNIX_TIMESTAMP(created), paper_ownerID FROM properties");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($property_id, $created, $paper_ownerID);

    $update = $mysqli->prepare("UPDATE properties SET crypt_name=? WHERE property_id=?");
    while ($result2->fetch()) {
      $hash = $property_id . $created . $paper_ownerID;
      $update->bind_param('si', $hash, $property_id);
      $update->execute();
    }
    $update->close();

    $result2->close();
  }
  $result->close();

  // 18/10/2011 - Add type to feedback_release.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='feedback_release' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE feedback_release ADD COLUMN type enum('objectives','questions')");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE feedback_release ADD COLUMN type enum('objectives','questions')</li>\n";
    ob_flush();
    flush();
    
    $update = $mysqli->prepare("UPDATE feedback_release SET type='objectives'");
    $update->execute();
    $update->close();
  }
  $result->close();
  
  // 24/10/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log4_overall' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('year1','year2','year3','year4','year5','year6','cp1','cp2','cp3','f1','graduate')") {
    $adjust = $mysqli->prepare("ALTER TABLE log4_overall ADD COLUMN yearofstudy tinyint");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log4_overall ADD COLUMN yearofstudy tinyint</li>\n";
    ob_flush();
    flush();

    $convert_years = array('year1'=>1,'year2'=>2,'year3'=>3,'year4'=>4,'year5'=>5,'year6'=>6,'cp1'=>3,'cp2'=>4,'cp3'=>5,'f1'=>5,'graduate'=>6);
    foreach ($convert_years as $old_year=>$new_year) {
      $adjust = $mysqli->prepare("UPDATE log4_overall SET yearofstudy=$new_year WHERE year='$old_year'");
      $adjust->execute();
      $adjust->close();
    }
    
    $adjust = $mysqli->prepare("ALTER TABLE log4_overall DROP COLUMN year");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log4_overall DROP COLUMN year</li>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE log4_overall CHANGE COLUMN yearofstudy year tinyint");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log4_overall CHANGE COLUMN yearofstudy year tinyint</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 27/10/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='title'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('Dr','Miss','Mr','Mrs','Ms','Professor')") {
    $adjust = $mysqli->prepare("ALTER TABLE users CHANGE COLUMN title title varchar(30)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE users CHANGE COLUMN title title varchar(30)</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 18/10/2011 - Add type to feedback_release.
  $result = $mysqli->prepare("SELECT * FROM questions WHERE q_type='calculation' AND score_method!='Allow Partial Marks'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows > 0) {
    $adjust = $mysqli->prepare("UPDATE questions SET score_method='Allow Partial Marks' WHERE q_type='calculation' AND score_method!='Allow Partial Marks'");
    $adjust->execute();
    $adjust->close();
    echo "<li>UPDATE questions SET score_method='Allow Partial Marks' WHERE q_type='calculation' AND score_method!='Allow Partial Marks'</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 02/11/2011 - Set the modules who do not have negative marking.
  $result = $mysqli->prepare("UPDATE modules SET neg_marking=0 WHERE vle_api='NLE'");
  $result->execute();
  $result->close();
  
  // 02/11/2011 - Clear the sys_error table for the new version.
  $result = $mysqli->prepare("TRUNCATE sys_errors");
  $result->execute();
  $result->close();
  echo "<li>TRUNCATE sys_errors</li>\n";

  // 09/11/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='labs' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='campus'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('University Park','Jubilee','King''s Meadow','Derby','Malaysia','Ningbo','Sutton Bonington','Other')") {
    $adjust = $mysqli->prepare("ALTER TABLE labs CHANGE COLUMN campus campus varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE labs CHANGE COLUMN campus campus varchar(255)</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 09/11/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sms_imports' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='import_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('manual','SATURN UK','SATURN Malaysia','SATURN China','ARC')") {
    $adjust = $mysqli->prepare("ALTER TABLE sms_imports CHANGE COLUMN import_type import_type varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE sms_imports CHANGE COLUMN import_type import_type varchar(255)</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 07/12/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log6' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE log6 (id int not null primary key auto_increment, paperID smallint, reviewerID mediumint, peerID mediumint, started datetime, q_id int, rating tinyint)");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE log6 (id int not null primary key auto_increment, paperID smallint, reviewerID mediumint, peerID mediumint, started datetime, q_id int, rating tinyint)</li>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE properties CHANGE COLUMN paper_type paper_type enum('0','1','2','3','4','5','6')");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE properties CHANGE COLUMN paper_type paper_type enum('0','1','2','3','4','5','6')</li>\n";
  }
  $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log6 TO '" . $cfg_db_student_user . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log6 TO '" . $cfg_db_student_user . "'@'". $cfg_db_host . "'</li>\n";
    
  $sql = "GRANT SELECT ON " . $cfg_db_database . ".log6 TO '" . $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT SELECT ON " . $cfg_db_database . ".log6 TO '" . $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";
  $result->close();
  
  // 08/09/2011 - Add auth_user column to sys_errors
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sys_errors' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='auth_user'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN auth_user VARCHAR(45) DEFAULT NULL AFTER userID");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE sys_errors ADD COLUMN auth_user VARCHAR(45) DEFAULT NULL AFTER userID</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 13/01/2012 - Add deleted column to Faculty table
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='faculty' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='deleted'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE faculty ADD COLUMN deleted datetime");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE faculty ADD COLUMN deleted datetime</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 13/01/2012 - Add deleted column to Degrees table
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='degrees' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='deleted'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $result2 = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='degrees' AND TABLE_SCHEMA='$cfg_db_database'");    // Check to see if Degrees exists, more recently renamed Courses.
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($table_name);
    $result2->fetch();
    if ($result2->num_rows() > 0) {
      $adjust = $mysqli->prepare("ALTER TABLE degrees ADD COLUMN deleted datetime");
      $adjust->execute();
      $adjust->close();
      echo "<li>ALTER TABLE degrees ADD COLUMN deleted datetime</li>\n";
      ob_flush();
      flush();
    }
  }
  $result->close();

  // 13/01/2012 - Add new character set to configuration file.
  $new_cfg_str[] =  "  \$cfg_db_charset = 'latin1';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');

  //remove refrances to old vars
  $cfg_new = array();
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_db_charset') !== false) {
      $found = true;
    }
    $cfg_new[] = $line;
  }
  
  if (!$found) {
    //add the new config chunk
    array_splice($cfg_new,25,0,$new_cfg_str);
        
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old1.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added database charset.</li>\n";
  }
  
  // 16/01/2012 - Rename Degrees table to Courses table
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='degrees' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {
    $adjust = $mysqli->prepare("RENAME TABLE degrees TO courses");
    $adjust->execute();
    $adjust->close();
    echo "<li>RENAME TABLE degrees TO courses</li>\n";
    ob_flush();
    flush();

    $adjust = $mysqli->prepare("ALTER TABLE courses CHANGE COLUMN degree name varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE courses CHANGE COLUMN degree name varchar(255)</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 19/01/2012 - Add deleted column to Schools table
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='schools' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='deleted'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE schools ADD COLUMN deleted datetime");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE schools ADD COLUMN deleted datetime</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 19/01/2012 - Update the version number
  $cfg_new = array();
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  foreach ($cfg as $line) {
    if (strpos($line,'ts_version') !== false) {
      $cfg_new[] = "\$ts_version = '$version';\n";
    } else {
      $cfg_new[] = $line;
    }
  }
  
  if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === false) {
    echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
  }


  // 19/01/2012 - Add root path functions to config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "if (empty(\$root)) \$root = str_replace('/config', '/', str_replace('\\\\', '/', dirname(__FILE__)));\n";
  $new_cfg_str[] = "require \$root . '/include/path_functions.inc.php';\n\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'dirname(__FILE__)') !== false) {
      $found = true;
    }
  }

  if (!$found) {
    array_splice($cfg,11,0,$new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old4.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added root path functions to config file.</li>\n";
    ob_flush();
    flush();
  }

  // 19/01/2012 - Add URL root to config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "\$cfg_web_root = get_root_path() . '/';\n";
  $new_cfg_str[] = "\$cfg_root_path = rtrim('/' . str_replace(\$_SERVER['DOCUMENT_ROOT'], '', \$cfg_web_root), '/');\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_root_path') !== false) {
      $found = true;
    }
  }

  if (!$found) {
    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line,'cfg_web_root =') !== false) {
        $found = true;
        break;
      }
      $index++;
    }

    if ($found) {
      unset($cfg[$index]);
      $cfg = array_values($cfg);
      array_splice($cfg, $index, 0, $new_cfg_str);
    } else {
      array_splice($cfg, 17, 0, $new_cfg_str);
    }

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old5.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added URL root to config file.</li>\n";
    ob_flush();
    flush();
  }


  // 19/01/2012 - Add root path for JavaScript to config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "// Root path for JS\n";
  $new_cfg_str[] = "\$cfg_js_root = <<< SCRIPT\n";
  $new_cfg_str[] = "<script type=\"text/javascript\">\n";
  $new_cfg_str[] = "if (typeof cfgRootPath == 'undefined') {\n";
  $new_cfg_str[] = "var cfgRootPath = '\$cfg_root_path';\n";
  $new_cfg_str[] = "}\n";
  $new_cfg_str[] = "</script>\n";
  $new_cfg_str[] = "SCRIPT;\n\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'Root path for JS') !== false) {
      $found = true;
    }
  }

  if (!$found) {
    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line,'//Editor') !== false) {
        $found = true;
        break;
      }
      $index++;
    }

    if ($found) {
      array_splice($cfg, $index, 0, $new_cfg_str);
    } else {
      $cfg[] = "\n";
      $cfg = array_merge($cfg, $new_cfg_str);
    }

    // And change the editor JS include
    $new_cfg_str = array();
    $new_cfg_str[] = "\$cfg_editor_javascript = <<< SCRIPT\n";
    $new_cfg_str[] = "\$cfg_js_root\n";
    $new_cfg_str[] = "<script type=\"text/javascript\" src=\"\$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n";
    $new_cfg_str[] = "<script type=\"text/javascript\" src=\"\$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_config.js\"></script>\n";
    $new_cfg_str[] = "SCRIPT;\n";

    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line,'cfg_editor_javascript =') !== false) {
        $found = true;
        break;
      }
      $index++;
    }

    if ($found) {
      unset($cfg[$index]);

      // Editor JS string was sometimes split over multiple lines. Check and remove if this is the case
      if (substr(trim($cfg[$index+2]), 0, 2) == '";') {
        unset($cfg[$index+2]);
      }
      if (substr(trim($cfg[$index+1]), 0, 16) == '<script language') {
        unset($cfg[$index+1]);
      }

      $cfg = array_values($cfg);
      array_splice($cfg, $index, 0, $new_cfg_str);
    } else {
      $cfg[] = "\n";
      array_merge($cfg, $new_cfg_str);
    }

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old6.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added root path for JavaScript to config file.</li>\n";
    ob_flush();
    flush();
  }

  // 19/01/2012 - Add default install type to config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "  default:\n";
  $new_cfg_str[] = "    \$cfg_install_type = '';\n";
  $new_cfg_str[] = "    error_reporting(0);\n";
  $new_cfg_str[] = "    break;\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  $last_break = 0;
  $index = 0;
  foreach ($cfg as $line) {
    if (strpos($line,'default:') !== false) {
      $found = true;
    }
    if (strpos($line,'break;') !== false) {
      $last_break = $index;
    }
    $index++;
  }

  if (!$found) {
    array_splice($cfg, $last_break+1, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old7.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added default install type to config file.</li>\n";
    ob_flush();
    flush();
  }


  // End ------------------------------------------------------------------
  echo "</ol>\n";
  
  //Close the database
  $mysqli->close();
  ob_end_flush();
  echo "\n<h2>" . $string['actionrequired'] . "</h2>\n<ol>";
  echo "\n<li>" . $string['readonly'] . "</li>\n";
  echo "</ol>\n<div>" . $string['finished'] . "</div>\n<blockquote>\n";
}
?>