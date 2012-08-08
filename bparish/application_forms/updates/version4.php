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

require_once '../classes/installutils.class.php';
require_once '../include/auth.inc';
require_once '../classes/lang.class.php';
require_once $cfg_web_root . 'classes/dbutils.class.php';

$version = '4.3';

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

function gen_random_salt() {
  $salt = '';
  $characters = 'abcdefghijklmnopqrstuvwxzyABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  
  for ($i=0; $i<16; $i++) {
    $salt .= substr($characters, rand(0,61), 1);
  }
  
  return $salt;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
    <title>Rogo <?php echo $rogo_version . ' to ' . $version; ?> update Script</title>
    <link rel="stylesheet" type="text/css" href="../css/header.css" />
    <style type="text/css">
      html {padding:0em; margin:0em; width:100%}
      body {padding:0em; margin:0em; width:100%; font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black}
      h1 {font-size:140%; color:#1F497D}
      h2 {font-size:120%; color:#1F497D}
      .error {color:red; font-weight:bold}
      .warning {float:none; color:red; padding-left: .5em; vertical-align:top}
      label {float:left; width:150px; padding-left:0em; text-align:left}
      p {clear:both}
      .submit {text-align:center; padding-top:2em}
      table {border:none}
      .h {margin-top:1.5em; margin-bottom:0.5em; width:100%; color:#1E3287}
      .h hr {border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%}
      td.line {width:98%}
      input[type=text], input[type=password] {width:140px}
      form {padding:1em}
      form div {padding-left:2em}
    </style>
    <script language="text/javascript" type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
    <script language="text/javascript" type="text/javascript" src="../js/jquery.validate.min.js"></script>
  </head>
  <body>
  <table class="header"> 
    <tr>
      <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
      <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0" style="float:left; padding-right:8px" />
      <div style="color:#1F497D; font-size:28pt; font-weight:bold">Rogo</div>
      <div style="color:#1F497D; font-size:9pt">Update Utility (<?php echo $rogo_version . ' to ' . $version; ?>)</div>
      </th>
      <th style="text-align:right; padding-right:10px">
      <img src="../artwork/software_64.png" width="64" height="64" alt="Upgrade Icon" border="0" />
      </th>
    </tr>
    <tr> 
      <th colspan="2" class="bevel"></th> 
    </tr> 
  </table>
<?php
if (!isset($_POST['update'])) {
?>
    <script type="text/javascript">
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
       <h2><?php echo $string['updatefromversion'] . ' ' . $rogo_version . ' to ' . $version; ?></h2>
       <div><?php echo $string['warning1']; ?></div>
       <div><?php echo $string['warning1']; ?></div>
      <?php
    } else {
      ?>    
      <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
      <div><?php printf($string['msg1'], $version); ?></div>
        <table class="h"><tr><td><nobr><?php echo $string['databaseadminuser']; ?></nobr></td><td class="line"><hr /></td></tr></table> 
          <div><?php echo $string['msg2']; ?></div>
          <br />
          <div><label for="mysql_admin_user"><?php echo $string['dbusername']; ?></label> <input type="text" value="" name="mysql_admin_user" class="required" minlength="2" /> </div>
          <div><label for="mysql_admin_pass"><?php echo $string['dbpassword']; ?></label> <input type="password" value="" name="mysql_admin_pass" /></div>

          <table class="h"><tr><td><nobr><?php echo $string['onlinehelpsystems']; ?></nobr></td><td class="line"><hr /></td></tr></table>
          <div><label for="update_staff_help"><?php echo $string['updatestaffhelp']; ?></label> <input type="checkbox" value="" name="update_staff_help" checked="checked" /></div>
          <div><label for="update_student_help"><?php echo $string['updatestudenthelp']; ?></label> <input type="checkbox" value="" name="update_student_help" checked="checked" /></div>
     
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
  
  echo "\n<blockquote>\n<h1>" . $string['startingupdate'] . "</h1>\n<ol>";
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
    ob_flush();
    flush();
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
      ob_flush();
      flush();
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
    ob_flush();
    flush();
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
    ob_flush();
    flush();
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
    ob_flush();
    flush();
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
    ob_flush();
    flush();
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
    $cfg_db_password = gen_password(16);
    $cfg_db_student_user = $cfg_db_database . '_stu';
    $cfg_db_student_passwd = gen_password(16);
    $cfg_db_staff_user = $cfg_db_database . '_staff';
    $cfg_db_staff_passwd = gen_password(16);
    $cfg_db_external_user = $cfg_db_database . '_ext';
    $cfg_db_external_passwd  = gen_password(16);
    $cfg_db_sysadmin_user = $cfg_db_database . '_sys';
    $cfg_db_sysadmin_passwd = gen_password(16);
    
    $priv_SQL = array();
    //create 'database user authentication user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_username . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_password . "'");
    echo "<li>NEW DB USER:: $cfg_db_username created</li>";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $cfg_db_database . ".users TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".sid TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_modules TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".schools TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_metadata_security TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE, INSERT, DELETE ON " . $cfg_db_database . ".password_tokens TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
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
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".papers TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".options TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".teams TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_help TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'";
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
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".papers TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
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
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log_late TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_metadata TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".textbox_marking TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".textbox_remark TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".track_changes TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".temp_users TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";  
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sessions TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";

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
    
    //Old users will be missing permision to delete from textbox_marking and textbox_remark just add them in
    $priv_SQL = Array();
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".textbox_marking TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".textbox_remark TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
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
      ob_flush();
      flush();
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
    $cfg_db_sct_password = gen_password(16);
        
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
  
  $cfg_db_inv_username = $cfg_db_database . '_inv';

  $result = $mysqli->prepare("SELECT user FROM mysql.user WHERE user = '" . $cfg_db_database . "_inv'");
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_user);
  $result->fetch();
  if ($result->num_rows() == 0) {
    
    $cfg_db_inv_password = gen_password(16);
        
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

        @ob_flush();
        @flush();


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
  ob_flush();
  flush();

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

  ob_flush();
  flush();

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
      $cfg_new[] = "\$rogo_version = '$version';\n";
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

  // 26/01/2012 - Add true/false question type
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='q_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();
  if ($column_type == "enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based')") {
    $adjust = $mysqli->prepare("ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false')");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false')</li>\n";
    ob_flush();
    flush();
  }

  // 27/01/2012
  //$priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO 'notts_login'@'". self::$cfg_db_host . "'";
  //  $priv_SQL[] = "GRANT SELECT, INSERT, DELETE ON " . $dbname . ".password_tokens TO 'notts_login'@'". self::$cfg_db_host . "'";

    @ob_flush();
    @flush();


  // 06/02/2012 - Change schools from text to integers in courses table
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='courses' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='schoolid'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Add new integer column
    $adjust = $mysqli->prepare("ALTER TABLE courses ADD COLUMN schoolid int");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE courses ADD COLUMN schoolid int</li>\n";

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
      $adjust = $mysqli->prepare("UPDATE courses SET schoolid=? WHERE school=?");
      $adjust->bind_param('is', $schoolid, $school_name);
      $adjust->execute();
      $adjust->close();
    }
    // Drop the old textual column
    $adjust = $mysqli->prepare("ALTER TABLE courses DROP COLUMN school");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE courses DROP COLUMN school</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 19/01/2012 - Add LDAP user search prefix to config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "  \$cfg_ldap_user_prefix   = 'sAMAccountName='; // Nottingham specific.  Please change.\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  $ldap_pass_location = 0;
  $index = 0;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_ldap_user_prefix') !== false) {
      $found = true;
    }
    if (strpos($line,'cfg_ldap_bind_password') !== false) {
      $ldap_pass_location = $index;
    }
    $index++;
  }

  if (!$found) {
    array_splice($cfg, $ldap_pass_location+1, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old8.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added LDAP user search prefix to config file.\n";
    echo "<br /><strong>If you use LDAP authentication then you will need to change the value <code>\$cfg_ldap_user_prefix</code> in <code>/config/config.inc.php</code></strong></li>\n";
    ob_flush();
    flush();
  }

  // 24/02/2012 - Add new page character set to configuration file.
  $new_cfg_str =  array("\$cfg_page_charset = 'UTF-8';\n");
  $cfg = file($cfg_web_root . 'config/config.inc.php');

  //remove refrances to old vars
  $cfg_new = array();
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_page_charset') !== false) {
      $found = true;
    }
    $cfg_new[] = $line;
  }

  if (!$found) {
    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line, '$protocol') !== false) {
        $found = true;
        break;
      }
      $index++;
    }

    if (!$found) $index = 20;

    //add the new config chunk
    array_splice($cfg_new, $index + 1, 0, $new_cfg_str);

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old8.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added page charset to configuration file.</li>\n";
  }

  ob_flush();
  flush();

  // 05/03/2012 - Add announcements table
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='announcements' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE announcements (id int not null primary key auto_increment, title varchar(255), staff_msg text, student_msg text, icon varchar(255), startdate datetime, enddate datetime, deleted datetime)");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE announcements (id int not null primary key auto_increment, title varchar(255), staff_msg text, student_msg text, icon varchar(255), startdate datetime, enddate datetime, deleted datetime, deleted datetime)</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  $sql = "GRANT SELECT ON " . $cfg_db_database . ".announcements TO '" . $cfg_db_student_user . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT SELECT ON " . $cfg_db_database . ".announcements TO '" . $cfg_db_student_user . "'@'". $cfg_db_host . "'</li>\n";

  $sql = "GRANT SELECT ON " . $cfg_db_database . ".log2 TO '" . $cfg_db_inv_username . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT SELECT ON " . $cfg_db_database . ".log2 TO '" . $cfg_db_inv_username . "'@'". $cfg_db_host . "'</li>\n";

  $sql = "GRANT SELECT ON " . $cfg_db_database . ".standards_setting TO '" . $cfg_db_student_user . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT SELECT ON " . $cfg_db_database . ".standards_setting TO '" . $cfg_db_student_user . "'@'". $cfg_db_host . "'</li>\n";

  $sql = "GRANT UPDATE ON " . $cfg_db_database . ".password_tokens TO '" . $cfg_db_username . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT UPDATE ON " . $cfg_db_database . ".password_tokens TO '" . $cfg_db_username . "'@'". $cfg_db_host . "'</li>\n";

  $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sessions TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sessions TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";

  ob_flush();
  flush();

  // 12/03/2012 - Fix any uses of old calculator or new basic calculator as we are not shipping that yet
  $result = $mysqli->prepare("SELECT COUNT(property_id) FROM properties WHERE (calculator = 2 OR calculator = -1)");
  $result->execute();
  $result->store_result();
  $result->bind_result($rows);
  $result->fetch();
  if ($rows > 0) {
    $adjust = $mysqli->prepare("UPDATE properties SET calculator=1 WHERE (calculator = 2 OR calculator = -1)");
    $adjust->execute();
    $adjust->close();
    echo "<li>UPDATE properties SET calculator=1 WHERE (calculator = 2 OR calculator = -1)</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // Adding missing indexes 
  $result = $mysqli->prepare("SHOW INDEX FROM users WHERE Key_name = 'idx_roles'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 0) {
    echo "<li>CREATE INDEX idx_roles ON users (roles)</li>\n";
    if (!$mysqli->real_query("CREATE INDEX idx_roles ON users (roles)")) {
      echo "<li>" . $mysqli->error . "</li>\n";
    }
  }

  $result->close();
  $result = $mysqli->prepare("SHOW INDEX FROM standards_setting WHERE Key_name = 'idx_std_set'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 0) {
    echo "<li>CREATE INDEX idx_std_set ON standards_setting (std_set)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_std_set ON standards_setting (std_set)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
    echo "<li>CREATE INDEX idx_setterID ON standards_setting (setterID)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_setterID ON standards_setting (setterID)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
  }    
  $result->close();
  
  $result = $mysqli->prepare("SHOW INDEX FROM log_metadata WHERE Key_name = 'idx_log_metadata_student_grade'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 0) {
    echo "<li>CREATE INDEX idx_log_metadata_student_grade ON log_metadata (paperID)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_log_metadata_student_grade ON log_metadata (student_grade)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
    echo "<li>CREATE INDEX idx_log_metadata_paperID ON log_metadata (paperID)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_log_metadata_paperID ON log_metadata (paperID)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
  } 
  $result->close();
  
  $result = $mysqli->prepare("SHOW INDEX FROM log0 WHERE Key_name = 'idx_log0_screen'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 0) {
    echo "<li>CREATE INDEX idx_log0_screen ON log0 (screen)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_log0_screen ON log0 (screen)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
    echo "<li>CREATE INDEX idx_log1_screen ON log1 (screen)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_log1_screen ON log1 (screen)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
    echo "<li>CREATE INDEX idx_log2_screen ON log2 (screen)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_log2_screen ON log2 (screen)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
    echo "<li>CREATE INDEX idx_log3_screen ON log3 (screen)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_log3_screen ON log3 (screen)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
  } 
  $result->close();
  
  $result = $mysqli->prepare("SHOW INDEX FROM courses WHERE Key_name = 'idx_courses_name'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 0) {
    echo "<li>CREATE INDEX idx_courses_name ON courses (name)</li>\n";
    if(!$mysqli->real_query("CREATE INDEX idx_courses_name ON courses (name)")) {
        echo "<li>" . $mysqli->error . "</li>\n";
    }
  } 
  $result->close();

    @ob_flush();
    @flush();


  // 19/03/2012 - Add 'reference_material' and 'paper_reference' tables
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='reference_material' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Table to hold Reference material
    $adjust = $mysqli->prepare("CREATE TABLE reference_material (id int not null primary key auto_increment, title varchar(255), content text,  width  SMALLINT UNSIGNED, created datetime, deleted datetime)");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE reference_material (id int not null primary key auto_increment, title varchar(255), content text, width  SMALLINT UNSIGNED, created datetime, deleted datetime)</li>\n";
    ob_flush();
    flush();
    
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_material TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_material TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_material TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT ON " . $cfg_db_database . ".reference_material TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'</li>\n";

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_material TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT ON " . $cfg_db_database . ".reference_material TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'</li>\n";

    // Table to hold Reference modules
    $adjust = $mysqli->prepare("CREATE TABLE reference_modules (id int not null primary key auto_increment, refID mediumint unsigned, moduleID mediumint unsigned)");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE reference_material (id int not null primary key auto_increment, title varchar(255), content text, created datetime, deleted datetime)</li>\n";
    ob_flush();
    flush();
    
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_modules TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_modules TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_modules TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT ON " . $cfg_db_database . ".reference_modules TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'</li>\n";

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_modules TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT ON " . $cfg_db_database . ".reference_modules TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'</li>\n";

    // Table to assign Reference material to papers
    $adjust = $mysqli->prepare("CREATE TABLE reference_papers (id int not null primary key auto_increment, paperID mediumint, refID mediumint)");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE reference_papers (id int not null primary key auto_increment, paperID mediumint, refID mediumint)</li>\n";
    ob_flush();
    flush();

    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_papers TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_papers TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_papers TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT ON " . $cfg_db_database . ".reference_papers TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'</li>\n";

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_papers TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT ON " . $cfg_db_database . ".reference_papers TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'</li>\n";
  }
  $result->close();

  // 05/04/2012 - Enlarge the size of the integer for property_id in properties table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='property_id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'smallint') {
    $adjust = $mysqli->prepare("ALTER TABLE properties CHANGE COLUMN property_id property_id mediumint unsigned primary key auto_increment");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE properties CHANGE COLUMN property_id property_id mediumint unsigned primary key auto_increment</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for paper in papers table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='papers' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='paper'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'smallint') {
    $adjust = $mysqli->prepare("ALTER TABLE papers CHANGE COLUMN paper paper mediumint unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE paper CHANGE COLUMN paper paper mediumint unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for id in users table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'smallint') {
    $adjust = $mysqli->prepare("ALTER TABLE users CHANGE COLUMN id id int unsigned primary key auto_increment");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE users CHANGE COLUMN id id int unsigned primary key auto_increment</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in sid table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sid' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE sid CHANGE COLUMN userID userID int unsigned primary key");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE sid CHANGE COLUMN userID userID int unsigned primary key</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for memberID in teams table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='teams' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='memberID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE teams CHANGE COLUMN memberID memberID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE teams CHANGE COLUMN memberID memberID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in log0 table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log0' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE log0 CHANGE COLUMN userID userID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log0 CHANGE COLUMN userID userID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in log1 table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log1' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE log1 CHANGE COLUMN userID userID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log1 CHANGE COLUMN userID userID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in log2 table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log2' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE log2 CHANGE COLUMN userID userID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log2 CHANGE COLUMN userID userID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in log3 table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log3' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE log3 CHANGE COLUMN userID userID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log3 CHANGE COLUMN userID userID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in log4 table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log4' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE log4 CHANGE COLUMN userID userID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log4 CHANGE COLUMN userID userID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in log4_overall table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log4_overall' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE log4_overall CHANGE COLUMN userID userID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log4_overall CHANGE COLUMN userID userID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 05/04/2012 - Enlarge the size of the integer for userID in log5 table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log5' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE log5 CHANGE COLUMN userID userID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log5 CHANGE COLUMN userID userID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in log6 table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log6' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='peerID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE log6 CHANGE COLUMN peerID peerID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log6 CHANGE COLUMN peerID peerID int unsigned</li>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE log6 CHANGE COLUMN reviewerID reviewerID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE log6 CHANGE COLUMN reviewerID reviewerID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 05/04/2012 - Enlarge the size of the integer for ownerID in questions table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='ownerID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE questions CHANGE COLUMN ownerID ownerID int");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE questions CHANGE COLUMN ownerID ownerID int</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for paper_ownerID in properties table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='paper_ownerID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE properties CHANGE COLUMN paper_ownerID paper_ownerID int");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE properties CHANGE COLUMN paper_ownerID paper_ownerID int</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for editor in track_changes table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='track_changes' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='editor'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE track_changes CHANGE COLUMN editor editor int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE track_changes CHANGE COLUMN editor editor int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for userID in special_needs table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='special_needs' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='userID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE special_needs CHANGE COLUMN userID userID int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE special_needs CHANGE COLUMN userID userID int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 05/04/2012 - Enlarge the size of the integer for reviewer in review_comments table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='review_comments' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='reviewer'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type == 'mediumint') {
    $adjust = $mysqli->prepare("ALTER TABLE review_comments CHANGE COLUMN reviewer reviewer int unsigned");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE review_comments CHANGE COLUMN reviewer reviewer int unsigned</li>\n";
    ob_flush();
    flush();
  }
  $result->close();


  // 19/04/2012 - Add 'state' tables
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='state' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Table to hold Reference material
    $adjust = $mysqli->prepare("CREATE TABLE state (userID int unsigned, state_name varchar(255), content varchar(255), page varchar(255))");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE state (userID int unsigned, state_name varchar(255), content varchar(255), page varchar(255))</li>\n";
    $adjust = $mysqli->prepare("ALTER TABLE state ADD UNIQUE idx_user_state (userID, state_name, page)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE state ADD UNIQUE idx_user_state (userID, state_name, page)</li>\n";
    ob_flush();
    flush();
    
    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '". $cfg_db_sysadmin_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '". $cfg_db_sysadmin_user . "'@'". $cfg_db_host . "'</li>\n";

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'</li>\n";

  }

    @ob_flush();
    @flush();


  // 24/04/2012 - Add default timezone config file.
  $new_cfg_str = array();
  $new_cfg_str[] =  "  date_default_timezone_set(\$cfg_timezone);\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  $target_line = 53;
  $cur_line = 0;
  foreach ($cfg as $line) {
    if (strpos($line,'date_default_timezone_set') !== false) {
      $found = true;
    }
    if (strpos($line,'cfg_timezone') !== false) {
      $target_line = $cur_line + 1;
    }
    $cur_line++;
  }
  
  if (!$found) {
    array_splice($cfg,$target_line,0,$new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Add default timezone config file.</li>\n";
    ob_flush();
    flush();
  }

    @ob_flush();
    @flush();


  // 24/04/2012 - Add temp directory specification to config file.
  $new_cfg_str = array();
  $new_cfg_str[] =  "\$cfg_tmpdir = '/tmp/';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_tmpdir') !== false) {
      $found = true;
    }
  }
  
  if (!$found) {
    array_splice($cfg,22,0,$new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old4.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Add temp directory to config file.</li>\n";
    ob_flush();
    flush();
  }

    @ob_flush();
    @flush();


  // 25/04/2012 - Remove define lines not used.
  $new_cfg_str = array();
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,"define('TOUCHSTONE'") === false and strpos($line,"define('DIR_SEPARATOR'") === false and strpos($line,"\$news") === false) {
      $new_cfg_str[] = $line;
    } else {
      $found = true;
    }
  }
  
  $cfg = $new_cfg_str;
  
  if ($found) {
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old4.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Removed unneccessary lines from configuration (defines and \$news).</li>\n";
    ob_flush();
    flush();
  }


  // 02/05/2012 - Update the online help files.
  if (isset($_POST['update_staff_help'])) {
    $adjust = $mysqli->prepare("TRUNCATE staff_help");
    $adjust->execute();
    $adjust->close();
    echo "<li>TRUNCATE staff_help</li>\n";
  
    $query = file_get_contents('../install/staff_help.sql');
    $mysqli->query($query);
    echo "<li>LOADED staff_help</li>\n";
  }
  if (isset($_POST['update_student_help'])) {
    $adjust = $mysqli->prepare("TRUNCATE student_help");
    $adjust->execute();
    $adjust->close();
    echo "<li>TRUNCATE student_help</li>\n";
    
    $query = file_get_contents('../install/student_help.sql');
    $mysqli->query($query);
    echo "<li>LOADED student_help</li>\n";
  }

  // 02/05/2012 - Update the version number
  $cfg_new = array();
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  foreach ($cfg as $line) {
    if (strpos($line,'rogo_version') !== false) {
      $cfg_new[] = "\$rogo_version = '$version';\n";
    } else {
      $cfg_new[] = $line;
    }
  }
  if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === false) {
    echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
  }
    @ob_flush();
    @flush();

  // Staff user was missing DELETE privileges on properties in the install script
  $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".properties TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".properties TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";

    @ob_flush();
    @flush();

  // 15/05/2012 -  Add LTI Tables
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_keys' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ( $result->num_rows() == 0 ) {
    // Table to hold Reference material
    $sql="CREATE TABLE IF NOT EXISTS  " . $cfg_db_database . ".`lti_user` (  `oauth_consumer_key` varchar(200) NOT NULL,  `user_id` varchar(200) NOT NULL,  `rogo_id` int(11) NOT NULL,  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`oauth_consumer_key`,`user_id`),  KEY `rogo_id` (`rogo_id`)) ENGINE=InnoDB";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>\n";

    $sql="CREATE TABLE IF NOT EXISTS  " . $cfg_db_database . ".`lti_resource` (  `oauth_consumer_key` varchar(255) NOT NULL DEFAULT '',  `lti_resource_id` varchar(255) NOT NULL,  `internal_id` varchar(255) DEFAULT NULL,  `itype` varchar(255) DEFAULT NULL,  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`oauth_consumer_key`,`lti_resource_id`),  KEY `destination2` (`itype`),  KEY `destination` (`internal_id`)) ENGINE=InnoDB";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>\n";

    $sql="CREATE TABLE IF NOT EXISTS  " . $cfg_db_database . ".`lti_keys` (  `id` mediumint(9) NOT NULL AUTO_INCREMENT,  `oauth_consumer_key` char(255)NOT NULL,  `secret` char(255)DEFAULT NULL,  `name` char(255) DEFAULT NULL,  `context_id` char(255) DEFAULT NULL,  `created_at` datetime NOT NULL, `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`)) ENGINE=InnoDB";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>\n";

    ob_flush();
    flush();

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_keys TO '".  $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_keys TO '". $cfg_db_sysadmin_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";

    $sql="GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_user TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_resource TO '". $cfg_db_sysadmin_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";

    $sql="GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_resource TO '". $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".lti_resource TO '". $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";
  }
    @ob_flush();
    @flush();

  // 16/05/2012 - Enlarge the size of the password field to hold higher level of encryption SHA-512.
  $data_len = 0;
  $result = $mysqli->prepare("SELECT CHARACTER_OCTET_LENGTH FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='password'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_len);
  $result->fetch();
  if ($data_len != 90) {
    $adjust = $mysqli->prepare("ALTER TABLE users CHANGE COLUMN password password char(90)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE users CHANGE COLUMN password password char(90)</li>\n";
    ob_flush();
    flush();
  }
  $result->close();
  @ob_flush();
  @flush();
  
  // 16/05/2012 - Add encryption salt to config file.
  $new_cfg_str = array();
  //$new_cfg_str[] =  "  \$cfg_encrypt_salt = 'K8m2hzflkgjzdfgj';\n";
  $new_cfg_str[] =  "  \$cfg_encrypt_salt       = '" . gen_random_salt() . "';    // Do not alter if not on LDAP.\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  $cur_line = 0;
  $target_line = 66;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_encrypt_salt') !== false) {
      $found = true;
    }
    if (strpos($line,'cfg_use_ldap') !== false) {
      $target_line = $cur_line + 1;
    }
    $cur_line++;
  }
  
  if (!$found) {
    array_splice($cfg,$target_line,0,$new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Add \$cfg_encrypt_salt to config file.</li>\n";
    ob_flush();
    flush();
  }

  @ob_flush();
  @flush();


  // 22/05/2012 -  Chnage LTI Tables
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_keys' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='deleted'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0 ) {
    $sql="ALTER TABLE `lti_keys` CHANGE `created_at` `deleted` DATETIME NULL , CHANGE `updated_at` `updated_at` DATETIME NOT NULL";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="UPDATE `lti_keys` set `deleted`=NULL WHERE `deleted`='0000-00-00 00:00:00'";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_resource` CHANGE `updated` `updated` DATETIME NOT NULL";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_user` CHANGE `updated_on` `updated_on` DATETIME NOT NULL";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";
  }

  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_context' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $sql = "CREATE TABLE IF NOT EXISTS  " . $cfg_db_database . ".`lti_context` (`oauth_consumer_key` VARCHAR( 255 ) NOT NULL ,`lti_context_id` VARCHAR( 255 ) NOT NULL ,`c_internal_id` VARCHAR( 255 ) NOT NULL ,`updated_on` DATETIME NOT NULL, PRIMARY KEY (`oauth_consumer_key`,`lti_context_id`), KEY `c_internal_id` (`c_internal_id`)) ENGINE=InnoDB";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_context TO '" . $cfg_db_sysadmin_user . "'@'" . $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_context TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".lti_context TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>$sql</li>\n";
  }

  @ob_flush();
  @flush();

  // 22/05/2012 - Addition of grey personal folder
  $column_type = '';
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='folders' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='color'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('yellow','red','green','blue')") {
    $adjust = $mysqli->prepare("ALTER TABLE folders CHANGE COLUMN color color enum('yellow','red','green','blue','grey')");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE folders CHANGE COLUMN color color enum('yellow','red','green','blue','grey')</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 28/05/2012 - Add new autosave timeout.
  $new_cfg_str =  array("\n//Paper auto saving time out in seconds - default 180s == 3 minutes\n",
                        "  \$cfg_autosave_timeout = 180;\n");

  $cfg = file($cfg_web_root . 'config/config.inc.php');

  //remove refrances to old vars
  $cfg_new = array();
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_autosave_timeout') !== false) {
      $found = true;
    }
    $cfg_new[] = $line;
  }

  if (!$found) {
    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line, '$cfg_hour_warning') !== false) {
        $found = true;
        break;
      }
      $index++;
    }

    if (!$found) $index = $index; //put at end of file

    //add the new config chunk
    array_splice($cfg_new, $index + 1, 0, $new_cfg_str);

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old10.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added  new autosave timeout to configuration file.</li>\n";
  }
  
 // 28/05/2012 - Add permission for external examiners to view student help.
  $priv_SQL = array();
  $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_help TO '". $cfg_db_external_user . "'@'". $cfg_db_host . "'";
  $priv_SQL[] = "FLUSH PRIVILEGES";
  foreach ($priv_SQL as $sql) {
    $mysqli->query($sql);

    @ob_flush();
    @flush();

    if ($mysqli->errno != 0) {
      echo '<li class="error">ERROR: could not set permissions ' . $sql . '</li>';
    }  
  }
  
  // 29/05/2012 - Add 'scheduling' tables
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='scheduling' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Table to hold Reference material
    $adjust = $mysqli->prepare("CREATE TABLE scheduling (id int not null primary key auto_increment, paperID int, period varchar(255), barriers_needed tinyint, cohort_size varchar(20), notes text, sittings tinyint, campus varchar(255))");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE scheduling (id int not null primary key auto_increment, paperID int, period varchar(255), barriers_needed tinyint, cohort_size varchar(20), notes text, sittings tinyint, campus varchar(255))</li>\n";
    ob_flush();
    flush();
    $adjust = $mysqli->prepare("ALTER TABLE state ADD UNIQUE idx_user_state (userID, state_name, page)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE scheduling ADD UNIQUE idx_paperID (paperID)</li>\n";
    
    $sql = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".scheduling TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT ON " . $cfg_db_database . ".scheduling TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";
    
    $new_cfg_str = array();
    $new_cfg_str[] =  "\$cfg_summative_mgmt = false;     // Set this to true for central summative exam administration.";
    $cfg = file($cfg_web_root . 'config/config.inc.php');
    $found = false;
    $cur_line = 0;
    $target_line = 24;
    foreach ($cfg as $line) {
      if (strpos($line,'cfg_summative_mgmt') !== false) {
        $found = true;
      }
      if (strpos($line,'cfg_tmpdir') !== false) {
        $target_line = $cur_line + 1;
      }
      $cur_line++;
    }
    
    if (!$found) {
      array_splice($cfg,$target_line,0,$new_cfg_str);
      if (file_exists($cfg_web_root . 'config/config.inc.php')) {
        rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
      }
      
      if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
        echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
      }
      echo "<li>Add \$cfg_summative_mgmt = false.</li>\n";
      ob_flush();
      flush();
    }
  }
  $result->close();

  // 15/06/2012 - Add performance tables to store p and d values against questions in the bank.
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='performance_main' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE performance_main (id int not null primary key auto_increment, q_id int unsigned, paperID int unsigned, percentage tinyint, cohort_size int unsigned, taken date)");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE performance_main (id int not null primary key auto_increment, q_id int unsigned, paperID int unsigned, percentage tinyint, cohort_size int unsigned, taken date)</li>\n";
    ob_flush();
    flush();
    $adjust = $mysqli->prepare("ALTER TABLE performance_main ADD UNIQUE idx_q_id (q_id)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE performance_main ADD UNIQUE idx_q_id (q_id)</li>\n";
    
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".performance_main TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".performance_main TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";

    $adjust = $mysqli->prepare("CREATE TABLE performance_details (perform_id int, part_no tinyint, p tinyint, d tinyint)");
    $adjust->execute();
    $adjust->close();
    echo "<li>CREATE TABLE performance_details (perform_id int, part_no tinyint, p tinyint, d tinyint)</li>\n";
    ob_flush();
    flush();
    
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".performance_details TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $mysqli->query($sql);
    echo "<li>GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".performance_details TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";
  }
  $result->close();

  // Delete permission might be missing on log_late for staff (21/06/2012)
  $priv_SQL = array();
  $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log_late TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
  $priv_SQL[] = "FLUSH PRIVILEGES";
  foreach ($priv_SQL as $sql) {
    $mysqli->query($sql);

    @ob_flush();
    @flush();

    if ($mysqli->errno != 0) {
      echo '<li class="error">ERROR: could not set permissions ' . $sql . '</li>';
    }  
  }
  
  // 26/06/2012 - add new index to review_comments
  $result = $mysqli->prepare("SHOW INDEX FROM review_comments WHERE Key_name = 'idx_q_paper'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 0) {
    echo "<li>CREATE INDEX idx_q_paper ON review_comments (q_paper)</li>\n";
    if (!$mysqli->real_query("CREATE INDEX idx_q_paper ON review_comments (q_paper)")) {
      echo "<li>" . $mysqli->error . "</li>\n";
    }
  } 
  $result->close();

  // Delete permission might be missing on papers and state (28/06/2012)
  $priv_SQL = array();
  $priv_SQL[] = "GRANT DELETE ON " . $cfg_db_database . ".papers TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
  $priv_SQL[] = "GRANT DELETE ON " . $cfg_db_database . ".state TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
  $priv_SQL[] = "GRANT DELETE ON " . $cfg_db_database . ".state TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
  $priv_SQL[] = "FLUSH PRIVILEGES";
  foreach ($priv_SQL as $sql) {
    $mysqli->query($sql);

    @ob_flush();
    @flush();

    if ($mysqli->errno != 0) {
      echo '<li class="error">ERROR: could not set permissions ' . $sql . '</li>';
    }  
  }
   
  // 21/03/2012 - Move to InnoDB for all table except help tables SHOULD not go live untill ver 4.3 - With full testing
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE ENGINE='MyISAM' AND TABLE_SCHEMA = '" . $cfg_db_database . "'");
  $result->execute();
  $result->store_result();
  $result->bind_result($name);
  $skip_table = Array('help_log'=>1,'help_searches'=>1,'help_tutorial_log'=>1,'staff_help'=>1,'student_help'=>1);
  while ($result->fetch()) {
    if (isset($skip_table[$name])) {
      continue;
    }
    echo "<li>ALTER TABLE " . $name . " ENGINE=InnoDB</li>\n";
    if (!$mysqli->real_query("ALTER TABLE $name ENGINE=InnoDB")) {
      echo "<li>" . $mysqli->error . "</li>\n";
    }
    ob_flush();
    flush();
  }
  
  //update student_modules.moduleid to a char(25)
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='student_modules' AND TABLE_SCHEMA='touchstone' and COLUMN_NAME = 'moduleid' and COLUMN_TYPE = 'char(15)' AND TABLE_SCHEMA = '" . $cfg_db_database . "'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    echo "<li>ALTER TABLE student_modules CHANGE moduleid moduleid char(25)</li>\n";
    if (!$mysqli->real_query("ALTER TABLE student_modules CHANGE moduleid moduleid char(25)")) {
      echo "<li>" . $mysqli->error . "</li>\n";
    }
    ob_flush();
    flush();
  }
  $result->close();

  //Fix DB permissions for staff users importing off-line paper marks
  $sql = "GRANT DELETE ON " . $cfg_db_database . ".log_metadata TO '" . $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT DELETE ON " . $cfg_db_database . ".log_metadata TO '" . $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";
  $sql = "GRANT DELETE ON " . $cfg_db_database . ".log5 TO '" . $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
  $mysqli->query($sql);
  echo "<li>GRANT DELETE ON " . $cfg_db_database . ".log5 TO '" . $cfg_db_staff_user . "'@'". $cfg_db_host . "'</li>\n";

  ob_flush();
  flush();



  // 2012-07-30 cczsa1 alter lti tables to remove seperate oauth_consumer_key and combine with other index
  $result = $mysqli->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_context' AND COLUMN_NAME='oauth_consumer_key' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {

    $sql="UPDATE `lti_context` SET `lti_context_id`=CONCAT(`oauth_consumer_key`,':',`lti_context_id`)";
    $adjust = $mysqli->prepare($sql);
    if ($mysqli->error) {
      try {
        throw new Exception("0MySQL error $mysqli->error <br /> Query:<br /> $sql", $mysqli->errno);
      }
      catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
        echo nl2br($e->getTraceAsString());
      }
    }
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_context` DROP `oauth_consumer_key`";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_context` CHANGE `lti_context_id` `lti_context_key` VARCHAR( 255 ) NOT NULL ";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";


  }

  $result->close();
  @ob_flush();
  @flush();


  $result = $mysqli->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_resource' AND COLUMN_NAME='oauth_consumer_key' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {

    $sql="UPDATE `lti_resource` SET `lti_resource_id`=CONCAT(`oauth_consumer_key`,':',`lti_resource_id`)";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_resource` DROP `oauth_consumer_key`";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_resource` CHANGE `lti_resource_id` `lti_resource_key` VARCHAR( 255 ) NOT NULL ";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_resource` CHANGE `itype` `internal_type` VARCHAR( 255 ) NOT NULL ";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_resource` CHANGE `updated` `updated_on` DATETIME";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";
  }
  $result->close();

  @ob_flush();
  @flush();


  $result = $mysqli->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_user' AND COLUMN_NAME='oauth_consumer_key' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {

    $sql="UPDATE `lti_user` SET `user_id`=CONCAT(`oauth_consumer_key`,':',`user_id`)";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_user` DROP `oauth_consumer_key`";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_user` CHANGE `user_id` `lti_user_key` VARCHAR( 255 ) NOT NULL ";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";

    $sql="ALTER TABLE `lti_user` CHANGE `rogo_id` `lti_user_equ` VARCHAR( 255 ) NOT NULL ";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";
  }

  @ob_flush();
  @flush();
  $result->close();


  $result = $mysqli->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_keys' AND COLUMN_NAME='updated_at' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {


    $sql="ALTER TABLE `lti_keys` CHANGE `updated_at` `updated_on` DATETIME";
    $adjust = $mysqli->prepare($sql);
    $adjust->execute();
    $adjust->close();
    echo "<li>$sql</li>";
  }
  $result->close();

  @ob_flush();
  @flush();




























  // End ------------------------------------------------------------------
  // 18/07/2012
  // Add index to improve performance for finding question copying in the Information dialog box.
  $result = $mysqli->prepare("SHOW INDEX FROM track_changes");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    $adjust = $mysqli->prepare("ALTER TABLE track_changes ADD INDEX(type)");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE track_changes ADD INDEX(type)</li>\n";
    ob_flush();
    flush();
  }

  // 24/07/2012 - Add new 'Area' question type
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='q_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();
  if ($column_type == "enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false')") {
    $adjust = $mysqli->prepare("ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false','area')");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false','area')</li>\n";
    ob_flush();
    flush();
  }

  // 27/07/2012 - Remove invalid entries from track changes
  $result = $mysqli->prepare("SELECT typeID FROM track_changes WHERE typeID < 1 LIMIT 1");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    $adjust = $mysqli->prepare("DELETE FROM track_changes WHERE typeID < 1");
    $adjust->execute();
    $adjust->close();
    echo "<li>DELETE * FROM track_changes WHERE typeID < 1</li>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 31/07/2012 - Add deleted column to users
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='user_deleted'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Add new deleted column
    $adjust = $mysqli->prepare("ALTER TABLE users ADD COLUMN user_deleted datetime");
    $adjust->execute();
    $adjust->close();
    echo "<li>ALTER TABLE users ADD COLUMN user_deleted datetime</li>\n";
  }
  $result->close();

  // 02/08/2012 - Move Area question settings into the question itself
  $result = $mysqli->prepare("SELECT o_id, option_text FROM options o INNER JOIN questions q ON o.o_id=q.q_id WHERE q.q_type='area' AND o.option_text IS NOT NULL AND o.option_text!=''");
  $result->execute();
  $result->store_result();
  $result->bind_result($o_id, $option_text);
  $area_fixed = 0;
  while ($result->fetch()) {
    // Update question
    $adjust1 = $mysqli->prepare("UPDATE questions SET display_method=? WHERE q_id=?");
    $adjust1->bind_param('si', $option_text, $o_id);
    $adjust1->execute();
    $adjust1->close();

    // Clear option
    $adjust1 = $mysqli->prepare("UPDATE options SET option_text=NULL WHERE o_id=?");
    $adjust1->bind_param('i', $o_id);
    $adjust1->execute();
    $adjust1->close();

    $area_fixed++;
  }
  if ($area_fixed > 0) {
    echo "<li>Moved area settings into question table</li>\n";
  }
  $result->close();

  // 03/08/2012 - Add session change over date.
  $new_cfg_str = array();
  $new_cfg_str[] =  "\$cfg_academic_year_start = '07/01';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = false;
  foreach ($cfg as $line) {
    if (strpos($line,'cfg_academic_year_start') !== false) {
      $found = true;
    }
  }
  
  if (!$found) {
    array_splice($cfg,20,0,$new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
    }
    
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added academic_year_start to config file.</li>\n";
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
  echo "</ol>\n<div>" . $string['finished'] . "</div>\n<div style=\"text-align:center\"><input type=\"button\" value=\" " . $string['home'] . " \" onclick=\"window.location('/staff/')\" /></div><blockquote>\n";
}
?>