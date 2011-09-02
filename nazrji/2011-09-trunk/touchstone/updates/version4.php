<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once '../config/config.inc.php';
require_once '../classes/installutils.class.php';
require_once '../classes/passwordutils.class.php';

set_time_limit(0);
if (!isset($_POST['update'])) {
  ?>
  <html>
    <head>
      <title>TouchStone Update Script</title>
      <style type="text/css">
        html { padding: 0em; margin: 0em; width: 100%}
        body { padding: 0em; margin: 0em; width: 100%; font-family:Arial,sans-serif; font-size:100%; background-color:white; color:black }
        .error { float: none; color: red; padding-left: .5em; vertical-align: top; }
        .warning { float: none; color: red; padding-left: .5em; vertical-align: top; }
        label { float:left; width:7.5em; padding-left:0em; text-align:left;}
        p { clear: both; }
        .submit { margin-left: 42%; padding-top:2em; }
        table {border:none;}
        table.topbar {font-weight: bold; width:100%; border-collapse:collapse;}
        .topbar td {background-color:#F1F5FB;}
        .header {font-weight: bold; margin-top:1.5em;  margin-bottom:0.5em;  width:97%; color:#1E3287}
        .header hr  {border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:97%;}
        td.line {width:98%}
        
        input {width:200px}
        form {padding: 1em}
        form div {padding-left: 2em}
      </style>
      <script language="text/javascript" type="text/javascript" src="../javascript/jquery-1.6.1.min.js"></script>
      <script language="text/javascript" type="text/javascript" src="../javascript/jquery.validate.min.js"></script>
    </head>
    <body>
    <table class="topbar"> 
      <tr> 
        <td><div style="font-size:22pt; font-weight:bold">&nbsp;TouchStone </div><div style="position:relative; left:12px; top:-3px; font-size:8pt">Assessment Management System</div></td> 
        <td style="text-align:right"><img src="../artwork/touchstone_logo_330_85.png" width="330" height="85" alt="Logo" border="0" />&nbsp;&nbsp;</td> 
      </tr> 
      <tr> 
        <td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td> 
      </tr> 
    </table> 
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
       <h2>Update from version 4.0 to 4.1</h2>
       <div>This update requires that /touchstone/config/config.inc.php is writeable.</div>
       <div>Please chown the file to the webserver and chomod it 644</div>
      <?php
    } else {
      ?>    
      <h2>Update from version 4.0 to 4.1</h2>
      <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <table class="header"><tr><td><nobr>Database Admin User</nobr></td><td class="line"><hr /></td></tr></table> 
          <div>The update script needs the username and password of a MySQL admin user to updare the database, users and tables. This username is not saved to the server and is only used by this update script.</div>
          <br />
          <div><label for="mysql_admin_user">DB Username:</label> <input type="text" value="" name="mysql_admin_user" class="required" minlength="2" /> </div>
          <div><label for="mysql_admin_pass">DB Password:</label> <input type="password" value="" name="mysql_admin_pass"/></div>
     
       <div class="submit"> <input type="submit" name="update" value="Update Touchstone" /> </div>
     </form>
    <?php
   }
   ?>
   </body>
   </html>
  <?php

 } else {
  
  $mysqli = new $dbclass($cfg_db_host , $_POST['mysql_admin_user'], $_POST['mysql_admin_pass'], $cfg_db_database);
  
  if ($mysqli->connect_error) {
    echo "<div>Filded to contect to mysql using " . $_POST['mysql_admin_user'] . '' .  $_POST['mysql_admin_pass'] . '</div>';
    echo "</body>";
    echo "</html>";
    exit;
  }
  
  echo "\n<h1>Starting update from version 4.0 to 4.1</h1>\n";
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
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='password_tokens' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
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
    echo "<div>CREATE TABLE users_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, userID INT, moduleID int, type varchar(255), value varchar(255), calendar_year enum('2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20'));</div>\n";
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
    echo "<div>ALTER TABLE properties ADD COLUMN retired datetime</div>\n";
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
    echo "<div>CREATE TABLE paper_metadata_security (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, paperID int, name varchar(255), value varchar(255))</div>\n";
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
    echo "<div>CREATE TABLE questions_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, questionID int, type varchar(255), value varchar(255))</div>\n";
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
    echo "<div>ALTER TABLE sys_errors ADD COLUMN paperID int</div>\n";
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
    echo "<div>ALTER TABLE sys_errors ADD COLUMN post_data text</div>\n";
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
    //create touchstone 'database user authentication user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_username . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_password . "'");
    echo "<div>NEW DB USER:: $cfg_db_username created</div>";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $cfg_db_database . ".users TO '". $cfg_db_username . "'@'". $cfg_db_host . "'";
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
    
    
    //create touchstone 'database user student user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_student_user . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_student_passwd . "'");
    echo "<div>NEW DB USER:: $cfg_db_student_user created</div>";
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
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_modules TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users_metadata TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".labs TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".question_exclude TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".sessions TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $cfg_db_database . ".sid TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $cfg_db_database . ".users TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
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
    $priv_SQL[] = "GRANT SELECT,INSERT ON " . $cfg_db_database . ".temp_users TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $cfg_db_database . ".sys_errors TO '". $cfg_db_student_user . "'@'". $cfg_db_host . "'";    
    $priv_SQL[] = "FLUSH PRIVILEGES";
 
    //create touchstone 'database user external user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_external_user . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_external_passwd . "'");
    echo "<div>NEW DB USER:: $cfg_db_external_user created</div>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".papers TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".options TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_external_user . "'@'". $cfg_db_host . "'";
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
    
    //create touchstone 'database user staff user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_staff_user . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_staff_passwd . "'");
    echo "<div>NEW DB USER:: $cfg_db_staff_user created</div>";
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
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".properties TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".feedback_release TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".paper_metadata_security TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".paper_notes TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
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
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4 TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4_overall TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log5 TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_late TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_metadata TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".textbox_marking TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".textbox_remark TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".track_changes TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".temp_users TO '". $cfg_db_staff_user . "'@'". $cfg_db_host . "'";  
    
    $mysqli->query("CREATE USER  '" . $cfg_db_sysadmin_user . "'@'". $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_sysadmin_passwd . "'");
    echo "<div>NEW DB USER:: $cfg_db_sysadmin_user created</div>";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, DROP  ON " . $cfg_db_database . ".* TO '". $cfg_db_sysadmin_user . "'@'". $cfg_db_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    
    foreach ($priv_SQL as $sql) {
      $mysqli->query($sql);
      if ($mysqli->errno != 0) {
        echo '<div> ERROR:: could not set permissions ' . $sql . '</div>';
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
    
    $touchstone_path = str_ireplace('/updates/version4.php','',$_SERVER['SCRIPT_FILENAME']);
    
    $cfg = file($touchstone_path . '/config/config.inc.php');

    //remove refrances to old vars
    $cfg_new = array();
    $remove_array = array('Local database', 'cfg_db_username', 'cfg_db_passwd', 'cfg_db_database', 'cfg_db_host');
    foreach ($cfg as $line) {
       $remove = false;
       foreach($remove_array as $needle) {
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
    
    
    if (file_exists($touchstone_path . '/config/config.inc.php')) {
      rename($touchstone_path . '/config/config.inc.php', $touchstone_path . '/config/config.inc.old.php');
    }
    
    if (file_put_contents($touchstone_path . '/config/config.inc.php', $cfg_new) === false) {
      echo "300 => could not write config file !";
    }
    ///////////////////////  update the config file!! //////////////////////////////////////
    
  } // END Create DB user
  
  // 26/08/2011 - Add date and time formats to config file.
  $new_cfg_str[] =  "// Date formats in MySQL DATE_FORMAT format\n";
  $new_cfg_str[] =  "\$cfg_short_date = '%m/%d/%y';\n";
  $new_cfg_str[] =  "\$cfg_long_date_time = '%m/%d/%Y %H:%i';\n";
  $touchstone_path = str_ireplace('/updates/version4.php','',$_SERVER['SCRIPT_FILENAME']);
  $cfg = file($touchstone_path . '/config/config.inc.php');
  if (!in_array('// Date formats in MySQL DATE_FORMAT format', $cfg)) {
    array_splice($cfg,36,0,$new_cfg_str);
    if (file_exists($touchstone_path . '/config/config.inc.php')) {
      rename($touchstone_path . '/config/config.inc.php', $touchstone_path . '/config/config.inc.old.php');
    }
    
    if (file_put_contents($touchstone_path . '/config/config.inc.php', $cfg) === false) {
      echo "300 => could not write config file !";
    }
    echo "<div>Added date and time formats to config file.</div>\n";
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
    echo "<div>ALTER TABLE questions CHANGE COLUMN score_method display_method text</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE questions ADD COLUMN score_method enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE questions ADD COLUMN score_method enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark')</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("UPDATE questions SET score_method = 'Mark per Option' WHERE q_type != 'Calculation'");
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
    echo "<div>ALTER TABLE options CHANGE COLUMN marks marks_correct float</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE options ADD COLUMN marks_incorrect float");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE options ADD COLUMN marks_incorrect float</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE options ADD COLUMN marks_partial float");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE options ADD COLUMN marks_partial float</div>\n";
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
    echo "<div>ALTER TABLE schools ADD COLUMN facultyID int</div>\n";
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
    echo "<div>ALTER TABLE schools DROP COLUMN faculty</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 10/08/2011 - Add new column for negative marking setting for modules.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='neg_marking'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN neg_marking TINYINT(1)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules ADD COLUMN neg_marking TINYINT(1)</div>\n";
    ob_flush();
    flush();
    $adjust = $mysqli->prepare("UPDATE modules SET neg_marking=1");
    $adjust->execute();
    $adjust->close();
  }
  $result->close();

  // 15/08/2011 - Add new table to hold Ebel grid defaults.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='ebel_grid_defaults' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE ebel_grid_defaults (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, EE tinyint, EI tinyint, EN tinyint, ME tinyint, MI tinyint, MN tinyint, HE tinyint, HI tinyint, HN tinyint, EE2 tinyint, EI2 tinyint, EN2 tinyint, ME2 tinyint, MI2 tinyint, MN2 tinyint, HE2 tinyint, HI2 tinyint, HN2 tinyint)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE ebel_grid_defaults (id INT NOT NULL PRIMARY_KEY AUTO INCREMENT, EE tinyint, EI tinyint, EN tinyint, ME tinyint, MI tinyint, MN tinyint, HE tinyint, HI tinyint, HN tinyint, EE2 tinyint, EI2 tinyint, EN2 tinyint, ME2 tinyint, MI2 tinyint, MN2 tinyint, HE2 tinyint, HI2 tinyint, HN2 tinyint)</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  //Close the database
  $mysqli->close();
  ob_end_flush();
  echo "\n<h2>Action Required</h2>\n";
  echo "\n<div>Don't forget to make the configfile readonly! (chmod 444)</div>\n";
  echo "\n<div>Finished!</div>\n";
}
?>