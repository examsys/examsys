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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once '../include/load_config.php';

require_once '../classes/installutils.class.php';
require_once '../classes/updaterutils.class.php';
require_once '../include/auth.inc';
require_once '../classes/lang.class.php';
require_once $cfg_web_root . 'classes/dbutils.class.php';

$version = '4.4';

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

  for ($i = 0; $i < 16; $i++) {
    $salt .= substr($characters, rand(0, 61), 1);
  }

  return $salt;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

      <title>Rogo <?php echo $configObject->get('rogo_version') . ' to ' . $version; ?> update Script</title>

      <link rel="stylesheet" type="text/css" href="../css/body.css"/>
      <link rel="stylesheet" type="text/css" href="../css/header.css"/>
      <style type="text/css">
          body {
              font-size: 90%
          }

          h1 {
              font-size: 140%;
              color: #1F497D
          }

          h2 {
              font-size: 120%;
              color: #1F497D
          }

          .error {
              color: red;
              font-weight: bold
          }

          .warning {
              float: none;
              color: red;
              padding-left: .5em;
              vertical-align: top
          }

          label {
              float: left;
              width: 150px;
              padding-left: 0em;
              text-align: left
          }

          p {
              clear: both
          }

          .submit {
              text-align: center;
              padding-top: 2em
          }

          table {
              border: none
          }

          .h {
              margin-top: 1.5em;
              margin-bottom: 0.5em;
              width: 100%;
              color: #1E3287
          }

          .h hr {
              border: 0px;
              height: 1px;
              color: #E5E5E5;
              background-color: #E5E5E5;
              width: 100%
          }

          td.line {
              width: 98%
          }

          input[type=text], input[type=password] {
              width: 140px
          }

          form {
              padding: 1em
          }

          form div {
              padding-left: 2em
          }
      </style>
      <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
      <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  </head>
  <body>
  <table class="header">
      <tr>
          <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
              <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0"
                   style="float:left; padding-right:8px"/>

              <div style="color:#1F497D; font-size:28pt; font-weight:bold">Rogo</div>
              <div style="color:#1F497D; font-size:9pt">Update Utility
                  (<?php echo $configObject->get('rogo_version') . ' to ' . $version; ?>)
              </div>
          </th>
          <th style="text-align:right; padding-right:10px">
              <img src="../artwork/software_64.png" width="64" height="64" alt="Upgrade Icon" border="0"/>
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
    $(document).ready(function () {
        $("#installForm").validate();
    });

    $(document).ready(function () {
        $('#useLdap').change(function () {
            $('#ldapOptions').toggle();
        });
    });
</script>
  <?php
  if (!InstallUtils::configFileIsWriteable()) {
    ?>
  <h2><?php echo $string['updatefromversion'] . ' ' . $configObject->get('rogo_version') . ' to ' . $version; ?></h2>
  <div><?php echo $string['warning1']; ?></div>
  <div><?php echo $string['warning1']; ?></div>
    <?php
  } else {
    ?>
  <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
      <div><?php printf($string['msg1'], $version); ?></div>
      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['databaseadminuser']; ?></nobr>
              </td>
              <td class="line">
                  <hr/>
              </td>
          </tr>
      </table>
      <div><?php echo $string['msg2']; ?></div>
      <br/>

      <div><label for="mysql_admin_user"><?php echo $string['dbusername']; ?></label> <input type="text" value=""
                                                                                             name="mysql_admin_user"
                                                                                             class="required"
                                                                                             minlength="2"/></div>
      <div><label for="mysql_admin_pass"><?php echo $string['dbpassword']; ?></label> <input type="password" value=""
                                                                                             name="mysql_admin_pass"/>
      </div>

      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['onlinehelpsystems']; ?></nobr>
              </td>
              <td class="line">
                  <hr/>
              </td>
          </tr>
      </table>
      <div><label for="update_staff_help"><?php echo $string['updatestaffhelp']; ?></label> <input type="checkbox"
                                                                                                   value=""
                                                                                                   name="update_staff_help"
                                                                                                   checked="checked"/>
      </div>
      <div><label for="update_student_help"><?php echo $string['updatestudenthelp']; ?></label> <input type="checkbox"
                                                                                                       value=""
                                                                                                       name="update_student_help"
                                                                                                       checked="checked"/>
      </div>

      <div class="submit"><input type="submit" name="update" value="<?php echo $string['startupdate']; ?>"/></div>
  </form>
    <?php
  }
  ?>
   </body>
   </html>
  <?php

} else {
  if ($configObject->get('cfg_db_charset') == NULL) {
    $cfg_db_charset = 'latin1';
  } else {
    $cfg_db_charset = $configObject->get('cfg_db_charset');
  }

  $mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $_POST['mysql_admin_user'], $_POST['mysql_admin_pass'], $configObject->get('cfg_db_database'), $cfg_db_charset, $notice, $configObject->get('dbclass'));

  if ($mysqli->connect_error) {
    echo "<div>Failded to contect to mysql using " . $_POST['mysql_admin_user'] . '' . $_POST['mysql_admin_pass'] . '</div>';
    echo "</body>";
    echo "</html>";
    exit;
  }
  $updater_utils = new UpdaterUtils($mysqli, $configObject->get('cfg_db_database'));

  // Avoid repeated method calls
  $cfg_db_database = $configObject->get('cfg_db_database');
  $cfg_db_student_user = $configObject->get('cfg_db_student_user');
  $cfg_db_staff_user = $configObject->get('cfg_db_staff_user');
  $cfg_db_host = $configObject->get('cfg_db_host');
  $cfg_db_username = $configObject->get('cfg_db_username');
  $cfg_db_external_user = $configObject->get('cfg_db_external_user');
  $cfg_use_ldap = $configObject->get('cfg_use_ldap');
  
  error_reporting(-1);

  echo "\n<blockquote>\n<h1>" . $string['startingupdate'] . "</h1>\n<ol>";
  ob_start();

  // 15/06/2011
  // Add index to improve performance for standards setting index page
  if (!$updater_utils->does_index_exist('ebel', 'SETTER_AND_DATE')) {
    $updater_utils->execute_query("ALTER TABLE ebel ADD INDEX SETTER_AND_DATE (setterID, date_set)", true);
  }

  // 16/06/2011
  if ($updater_utils->does_column_exist('log_late', 'year')) {
    $updater_utils->execute_query("ALTER TABLE log_late DROP COLUMN year", true);
    $updater_utils->execute_query("ALTER TABLE log_late DROP COLUMN student_grade", true);
    $updater_utils->execute_query("ALTER TABLE log_late DROP COLUMN ipaddress", true);
  }

  // 16/06/2011
  if (!$updater_utils->does_column_exist('sys_errors', 'fixed')) {
    $updater_utils->execute_query("ALTER TABLE sys_errors ADD COLUMN fixed datetime", true);
    $updater_utils->execute_query("ALTER TABLE sys_errors ADD COLUMN php_self text", true);
    $updater_utils->execute_query("ALTER TABLE sys_errors ADD COLUMN query_string text", true);
    $updater_utils->execute_query("ALTER TABLE sys_errors ADD COLUMN request_method enum('GET', 'HEAD', 'POST', 'PUT', 'DELETE')", true);
  }

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
    if ($group_list != '') {
      $updater_utils->execute_query("UPDATE standards_setting SET group_review = \"" . $group_list . "\" WHERE paperID = $paperID AND method = 'Modified Angoff' AND group_review = 'Yes'", true);
    }
  }
  $group_reviews->close();

  // 29/06/2011
  if (!$updater_utils->does_column_exist('modules', 'selfenroll')) {
    $updater_utils->execute_query("ALTER TABLE modules ADD COLUMN selfenroll tinyint", true);
    $updater_utils->execute_query("UPDATE modules SET selfenroll = 0", true);
  }

  // 30/06/2011 - Change schools from text to integers
  if (!$updater_utils->does_column_exist('modules', 'schoolid')) {
    // Add new integer column
    $updater_utils->execute_query("ALTER TABLE modules ADD COLUMN schoolid int", true);
    
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
    foreach ($schools as $school_name => $schoolid) {
      $updater_utils->execute_query("UPDATE modules SET schoolid=$schoolid WHERE school=\"" . $school_name . "\"", true);
    }
    // Drop the old textual column
    $updater_utils->execute_query("ALTER TABLE modules DROP COLUMN school", true);
  }

  // 04/07/2011 - Drop 'Faculty' column from users.
  if (!$updater_utils->does_column_exist('users', 'faculty')) {
    $updater_utils->execute_query("ALTER TABLE users DROP COLUMN faculty", true);
  }

  // 04/07/2011 - Create new 'admin_access' table to hold which modules 'Admin' can access.
  if (!$updater_utils->does_column_exist('admin_access', 'adminID')) {
    $updater_utils->execute_query("CREATE TABLE admin_access (adminID int not null primary key auto_increment, userID int, schools_id int)", true);
  }

  // 04/07/2011 - New table to handle forgotten password requests.
  if (!$updater_utils->does_column_exist('password_tokens', 'id')) {
    $updater_utils->execute_query("CREATE TABLE password_tokens (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, token CHAR(16) NOT NULL, time DATETIME NOT NULL)", true);
  }

  // 06/07/2011 - New table users_metadata.
  if (!$updater_utils->does_column_exist('users_metadata', 'userID')) {
    $updater_utils->execute_query("CREATE TABLE users_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, userID INT, moduleID int, type varchar(255), value varchar(255), calendar_year enum('2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20'))", true);
  }

  // 11/07/2011 - Add new column for retiring papers.
  if (!$updater_utils->does_column_exist('properties', 'retired')) {
    $updater_utils->execute_query("ALTER TABLE properties ADD COLUMN retired datetime", true);
  }

  // 25/07/2011 - New table paper_metadata_security.
  if (!$updater_utils->does_column_exist('paper_metadata_security', 'id')) {
    $updater_utils->execute_query("CREATE TABLE paper_metadata_security (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, paperID int, name varchar(255), value varchar(255))", true);
  }

  // 27/07/2011 - New table questions_metadata.
  if (!$updater_utils->does_column_exist('questions_metadata', 'id')) {
    $updater_utils->execute_query("CREATE TABLE questions_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, questionID int, type varchar(255), value varchar(255))", true);
  }

  // 01/08/2011 - Add new column for paperID in the errors table.
  if (!$col_exists = $updater_utils->does_column_exist('sys_errors', 'paperID')) {
    $updater_utils->execute_query("ALTER TABLE sys_errors ADD COLUMN paperID int", true);
  }

  // 01/08/2011 - Add new column for paperID in the errors table.
  if (!$updater_utils->does_column_exist('sys_errors', 'post_data')) {
    $updater_utils->execute_query("ALTER TABLE sys_errors ADD COLUMN post_data text", true);
  }

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
    $cfg_db_external_passwd = gen_password(16);
    $cfg_db_sysadmin_user = $cfg_db_database . '_sys';
    $cfg_db_sysadmin_passwd = gen_password(16);

    $priv_SQL = array();
    //create 'database user authentication user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_username . "'@'" . $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_password . "'");
    echo "<li>NEW DB USER:: $cfg_db_username created</li>";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $cfg_db_database . ".users TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".sid TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_modules TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".schools TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_metadata_security TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE, INSERT, DELETE ON " . $cfg_db_database . ".password_tokens TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users_metadata TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".labs TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".admin_access TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT,INSERT ON " . $cfg_db_database . ".temp_users TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $cfg_db_database . ".sys_errors TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";

    //create 'database user student user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_student_passwd . "'");
    echo "<li>NEW DB USER:: $cfg_db_student_user created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_help TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".papers TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".options TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".feedback_release TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".ip_addresses TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".modules TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".objectives TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_metadata_security TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".relationships TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".student_modules TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".schools TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users_metadata TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".labs TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".question_exclude TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".sessions TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".sid TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".users TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_log TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_searches TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_tutorial_log TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log0 TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log1 TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log2 TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log3 TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4 TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4_overall TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log5 TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_late TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_metadata TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".temp_users TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $cfg_db_database . ".sys_errors TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";

    //create 'database user external user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_external_passwd . "'");
    echo "<li>NEW DB USER:: $cfg_db_external_user created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".papers TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".options TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".teams TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_help TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".staff_help TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log0 TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log1 TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log2 TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log3 TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4 TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log4_overall TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log5 TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_late TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_metadata TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".review_comments TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $cfg_db_database . ".sys_errors TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";

    //create 'database user staff user' and grant permissions
    $mysqli->query("CREATE USER  '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_staff_passwd . "'");
    echo "<li>NEW DB USER:: $cfg_db_staff_user created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".* TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".users TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".users_metadata TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sid TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".password_tokens TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".student_modules TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".student_notes TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".papers TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".questions TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".questions_metadata TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".options TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".properties TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".feedback_release TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".paper_metadata_security TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".paper_notes TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".standards_setting TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".ebel TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".question_exclude TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".keywords_question TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".keywords_user TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".objectives TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".relationships TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".review_comments TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".recent_papers TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".folders TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".teams TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_log TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_searches TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".help_tutorial_log TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log0 TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log1 TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log2 TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log3 TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log4 TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log4_overall TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log5 TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log_late TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log_metadata TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".textbox_marking TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".textbox_remark TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".track_changes TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".temp_users TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sessions TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";

    $priv_SQL[] = "CREATE USER  '" . $cfg_db_sysadmin_user . "'@'" . $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_sysadmin_passwd . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, DROP  ON " . $cfg_db_database . ".* TO '" . $cfg_db_sysadmin_user . "'@'" . $cfg_db_host . "'";
    foreach ($priv_SQL as $sql) {
      $updater_utils->execute_query($sql, false);
    }

    //Old users will be missing permision to delete from textbox_marking and textbox_remark just add them in
    $priv_SQL = Array();
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".textbox_marking TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".textbox_remark TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    foreach ($priv_SQL as $sql) {
      $updater_utils->execute_query($sql, false);
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //  update the config file!!
    //
    $new_cfg_str[] = "// Local database\n";
    $new_cfg_str[] = "  \$cfg_db_username = '$cfg_db_username';\n";
    $new_cfg_str[] = "  \$cfg_db_passwd = '$cfg_db_password';\n";
    $new_cfg_str[] = "  \$cfg_db_database = '$cfg_db_database';\n";
    $new_cfg_str[] = "  \$cfg_db_host 	  = '$cfg_db_host';\n";
    $new_cfg_str[] = "// student db user \n";
    $new_cfg_str[] = "  \$cfg_db_student_user = '$cfg_db_student_user';\n";
    $new_cfg_str[] = "  \$cfg_db_student_passwd = '$cfg_db_student_passwd';\n";
    $new_cfg_str[] = "// staff db user\n";
    $new_cfg_str[] = "  \$cfg_db_staff_user = '$cfg_db_staff_user';\n";
    $new_cfg_str[] = "  \$cfg_db_staff_passwd = '$cfg_db_staff_passwd';\n";
    $new_cfg_str[] = "// external examiner db user\n";
    $new_cfg_str[] = "  \$cfg_db_external_user = '$cfg_db_external_user';\n";
    $new_cfg_str[] = "  \$cfg_db_external_passwd = '$cfg_db_external_passwd';\n";
    $new_cfg_str[] = "// sysdamin db user\n";
    $new_cfg_str[] = "  \$cfg_db_sysadmin_user = '$cfg_db_sysadmin_user';\n";
    $new_cfg_str[] = "  \$cfg_db_sysadmin_passwd = '$cfg_db_sysadmin_passwd';\n";

    $cfg = file($cfg_web_root . 'config/config.inc.php');

    //remove refrances to old vars
    $cfg_new = array();
    $remove_array = array('Local database', 'cfg_db_username', 'cfg_db_passwd', 'cfg_db_database', 'cfg_db_host');
    foreach ($cfg as $line) {
      $remove = FALSE;
      foreach ($remove_array as $needle) {
        if (stripos($line, $needle) !== FALSE) {
          $remove = TRUE;
          break 1;
        }
      }
      if (!$remove) {
        $cfg_new[] = $line;
      }
    }

    //add the new config chunk
    array_splice($cfg_new, 18, 0, $new_cfg_str);

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old1.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    ///////////////////////  update the config file!! //////////////////////////////////////

  } // END Create DB user

  // 26/08/2011 - Add date and time formats to config file.
  $new_cfg_str[] = "// Date formats in MySQL DATE_FORMAT format\n";
  $new_cfg_str[] = "  \$cfg_short_date = '%m/%d/%y';\n";
  $new_cfg_str[] = "  \$cfg_long_date_time = '%m/%d/%Y %H:%i';\n";
  $new_cfg_str[] = "  \$cfg_timezone = 'Europe/London';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'Date formats in MySQL DATE_FORMAT') !== FALSE) {
      $found = TRUE;
    }
  }

  if (!$found) {
    array_splice($cfg, 36, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old2.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<div>Added date and time formats to config file.</div>\n";
    ob_flush();
    flush();
  }

  // 05/09/2011 - Add company name config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "\$cfg_company = 'The University of Nottingham';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_company') !== FALSE) {
      $found = TRUE;
    }
  }

  if (!$found) {
    array_splice($cfg, 16, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added company name config file.</li>\n";
    ob_flush();
    flush();
  }

  // 01/08/2011 - Change to database structure for more flexible marking
  if (!$updater_utils->does_column_exist('questions', 'display_method')) {
    $updater_utils->execute_query("ALTER TABLE questions CHANGE COLUMN score_method display_method text", true);
    $updater_utils->execute_query("ALTER TABLE questions ADD COLUMN score_method enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark')", true);
    $updater_utils->execute_query("UPDATE questions SET score_method = 'Mark per Option' WHERE q_type != 'Calculation'", false);
    $updater_utils->execute_query("UPDATE questions SET score_method = 'Mark per Question' WHERE q_type = 'Calculation'", false);
    
    // Update the BonusMark setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method = 'BonusMark'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $updater_utils->execute_query("UPDATE questions SET display_method='', score_method='Bonus Mark' WHERE q_id=$q_id", false);
    }
    $q_data->close();

    // Update the StrictOrder setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method = 'StrictOrder'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $updater_utils->execute_query("UPDATE questions SET display_method='', score_method='Mark per Option' WHERE q_id=$q_id", false);
    }
    $q_data->close();

    // Update the AllItemsCorrect setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method = 'AllItemsCorrect'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $updater_utils->execute_query("UPDATE questions SET display_method='', score_method='Mark per Question' WHERE q_id=$q_id", false);
    }
    $q_data->close();

    // Update the SelectedPositive setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method = 'SelectedPositive'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $updater_utils->execute_query("UPDATE questions SET display_method='', score_method='Mark per Option' WHERE q_id=$q_id", false);
    }
    $q_data->close();

    // Update the OrderNeighbours setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method = 'OrderNeighbours'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $updater_utils->execute_query("UPDATE questions SET display_method='', score_method='Allow partial Marks' WHERE q_id=$q_id", false);
    }
    $q_data->close();
    
    $updater_utils->execute_query('ALTER TABLE options CHANGE COLUMN marks marks_correct float', true);
    $updater_utils->execute_query('ALTER TABLE options ADD COLUMN marks_incorrect float', true);
    $updater_utils->execute_query('ALTER TABLE options ADD COLUMN marks_partial float', true);
    $updater_utils->execute_query('UPDATE options SET marks_incorrect = 0', false);
    $updater_utils->execute_query('UPDATE options SET marks_partial = 0', false);

    // Update options for negative marking
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='TF_NegativeAbstain' OR display_method='YN_NegativeAbstain'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $updater_utils->execute_query("UPDATE options SET marks_incorrect=-1 WHERE o_id=$q_id", false);
    }
    $q_data->close();

    // Update options for half-negative marking
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='TF_NegativeAbstainHalf'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $updater_utils->execute_query("UPDATE options SET marks_incorrect=-0.5 WHERE o_id=$q_id", false);
      
      $updater_utils->execute_query("UPDATE questions SET display_method='TF_NegativeAbstain' WHERE q_id=$q_id", false);
    }
    $q_data->close();
  }
  $result->close();

  // 01/08/2011 - Change to database structure for more flexible marking
  if (!$updater_utils->does_column_exist('schools', 'facultyID')) {
    $updater_utils->execute_query("ALTER TABLE schools ADD COLUMN facultyID int", true);

    // Populate the new field with Faculty IDs
    $q_data = $mysqli->prepare("SELECT id, name FROM faculty");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($faculty_id, $faculty_name);
    while ($q_data->fetch()) {
      $updater_utils->execute_query("UPDATE schools SET facultyID=$faculty_id WHERE faculty='$faculty_name'", false);
    }
    $q_data->close();

    $updater_utils->execute_query("ALTER TABLE schools DROP COLUMN faculty", true);
  }

  // 10/08/2011 - Add new column for negative marking setting for modules.
  if (!$updater_utils->does_column_exist('modules', 'neg_marking')) {
    $updater_utils->execute_query("ALTER TABLE modules ADD COLUMN neg_marking TINYINT(1)", true);
    $updater_utils->execute_query("UPDATE modules SET neg_marking = 1", false);
  }

  // 08/09/2011 - Add field to Modules table to hold which Ebel grid template to use.
  if (!$updater_utils->does_column_exist('modules', 'ebel_grid_template')) {
    $updater_utils->execute_query("ALTER TABLE modules ADD COLUMN ebel_grid_template int", true);
  }

  // 15/08/2011 - Add new table to hold Ebel grid templates.
  if (!$col_exists = $updater_utils->does_column_exist('ebel_grid_templates', 'id')) {
    $updater_utils->execute_query("CREATE TABLE ebel_grid_templates (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, EE tinyint, EI tinyint, EN tinyint, ME tinyint, MI tinyint, MN tinyint, HE tinyint, HI tinyint, HN tinyint, EE2 tinyint, EI2 tinyint, EN2 tinyint, ME2 tinyint, MI2 tinyint, MN2 tinyint, HE2 tinyint, HI2 tinyint, HN2 tinyint, name varchar(255))", true);

    if (strpos(strtolower($_SERVER['HTTP_HOST']), 'nottingham.ac.uk') !== FALSE) {
      $sql = array();
      $sql[] = "INSERT INTO `ebel_grid_templates` (id,EE,EI,EN,ME,MI,MN,HE,HI,HN,EE2,EI2,EN2,ME2,MI2,MN2,HE2,HI2,HN2,name) VALUES (1,65,60,55,60,55,50,55,50,45,0,0,0,0,0,0,0,0,0,'BMedSci')";
      $sql[] = "INSERT INTO `ebel_grid_templates` (id,EE,EI,EN,ME,MI,MN,HE,HI,HN,EE2,EI2,EN2,ME2,MI2,MN2,HE2,HI2,HN2,name) VALUES (2,80,60,55,55,50,35,45,35,30,0,0,0,0,0,0,0,0,0,'BMBS')";
      $sql[] = "UPDATE modules SET ebel_grid_template=1 WHERE vle_api = 'NLE'";
      $sql[] = "UPDATE modules SET ebel_grid_template=2 WHERE moduleid IN ('A13CLP','A14CHH','A14DOO','A14HCE','A14ONG','A14PSY','A14ACE')";

      foreach ($sql as $q) {
        $updater_utils->execute_query($q, false);
      }
      echo "<li>Populating ebel_grid_templates with Nottingham data.</li>";
    }
  }

  // 01/09/2011 - Fix 'question' foreign key field in 'papers' not being big enough to hold a question ID!
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='papers' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='question'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();

  if (strpos($column_type, 'smallint') !== FALSE) {
    $updater_utils->execute_query("ALTER TABLE papers CHANGE COLUMN question question INT(4) UNSIGNED NOT NULL DEFAULT 0", true);
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
      $updater_utils->execute_query("UPDATE log0 SET user_answer=REPLACE(user_answer, 'y', 't') WHERE q_id=$questionID AND user_answer LIKE '%y%'", true);
    }
    $check->close();

    $check = $mysqli->prepare("SELECT * FROM log0 WHERE q_id=? AND user_answer LIKE '%n%'");
    $check->bind_param('i', $questionID);
    $check->execute();
    $check->store_result();
    $check->fetch();
    if ($check->num_rows() > 0) {
      $updater_utils->execute_query("UPDATE log0 SET user_answer=REPLACE(user_answer, 'n', 'f') WHERE q_id=$questionID AND user_answer LIKE '%n%'", true);
    }
    $check->close();

    $check = $mysqli->prepare("SELECT * FROM log2 WHERE q_id=? AND user_answer LIKE '%y%'");
    $check->bind_param('i', $questionID);
    $check->execute();
    $check->store_result();
    $check->fetch();
    if ($check->num_rows() > 0) {
      $updater_utils->execute_query("UPDATE log2 SET user_answer=REPLACE(user_answer, 'y', 't') WHERE q_id=$questionID AND user_answer LIKE '%y%'", true);
    }
    $check->close();

    $check = $mysqli->prepare("SELECT * FROM log2 WHERE q_id=? AND user_answer LIKE '%n%'");
    $check->bind_param('i', $questionID);
    $check->execute();
    $check->store_result();
    $check->fetch();
    if ($check->num_rows() > 0) {
      $updater_utils->execute_query("UPDATE log2 SET user_answer=REPLACE(user_answer, 'n', 'f') WHERE q_id=$questionID AND user_answer LIKE '%n%'", true);
    }
    $check->close();

    $updater_utils->execute_query("UPDATE options SET correct='t', marks_correct=1, marks_incorrect=-1 WHERE o_id=$questionID AND correct='y'", true);
    $updater_utils->execute_query("UPDATE options SET correct='f', marks_correct=1, marks_incorrect=-1 WHERE o_id=$questionID AND correct='n'", true);
    $updater_utils->execute_query("UPDATE questions SET q_type='dichotomous', display_method='TF_Positive', score_method='Mark per Option' WHERE q_id=$questionID", true);
  }
  $result->close();

  // 20/09/2011 - set marks for fill-in-the-blank question tyoe
  $updater_utils->execute_query("UPDATE options SET marks_correct=1, marks_incorrect=0 WHERE o_id IN (SELECT q_id FROM questions WHERE q_type='blank') AND (marks_correct IS NULL OR marks_correct=0)", false);
  
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

      $updater_utils->execute_query("UPDATE questions SET display_method=\"" . $new_method . "\" WHERE q_id=$questionID", false);
    }
  }
  $result->close();

  // 22/09/2011 - remove timedate question type
  $check = $mysqli->prepare("SELECT * FROM questions WHERE q_type='timedate'");
  $check->execute();
  $check->store_result();
  $check->fetch();
  if ($check->num_rows() > 0) {
    $updater_utils->execute_query("UPDATE questions SET q_type='textbox', display_method='40x1' WHERE q_type='timedate'", false);
  }

  // 01/09/2011 - Remove the time/date question type
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='q_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();
  if ($column_type == "enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','timedate','info','extmatch','random','sct','keyword_based')") {
    $sql = "ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based')";
    $updater_utils->execute_query($sql, true);
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
      $updater_utils->execute_query($q, false);
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
      for ($i = 8; $i < count($parts); $i++) {
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
    $mysqli->query("CREATE USER  '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_sct_password . "'");
    echo "<li>NEW DB USER:: $cfg_db_sct_username created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".papers TO '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions TO '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".questions_metadata TO '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".options TO '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_metadata_security TO '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".paper_notes TO '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sct_reviews TO '" . $cfg_db_sct_username . "'@'" . $cfg_db_host . "'";

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
    $new_cfg_str[] = "// SCT db user\n";
    $new_cfg_str[] = "  \$cfg_db_sct_user = '$cfg_db_sct_username';\n";
    $new_cfg_str[] = "  \$cfg_db_sct_passwd = '$cfg_db_sct_password';\n";

    $cfg = file($cfg_web_root . 'config/config.inc.php');

    //add the new config chunk
    array_splice($cfg, 36, 0, $new_cfg_str);


    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old1.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
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
    $mysqli->query("CREATE USER  '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_inv_password . "'");
    echo "<li>NEW DB USER:: $cfg_db_inv_username created</li>";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".student_modules TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".users TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".sid TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".ip_addresses TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".labs TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".properties TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".student_notes TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".paper_notes TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";

    foreach ($priv_SQL as $sql) {
      $updater_utils->execute_query($sql, false);
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //
    //  update the config file!!
    //

    $new_cfg_str = array();
    $new_cfg_str[] = "// Invigilator user\n";
    $new_cfg_str[] = "  \$cfg_db_inv_user = '$cfg_db_inv_username';\n";
    $new_cfg_str[] = "  \$cfg_db_inv_passwd = '$cfg_db_inv_password';\n";

    $cfg = file($cfg_web_root . 'config/config.inc.php');

    //add the new config chunk
    array_splice($cfg, 36, 0, $new_cfg_str);

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old1.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    ///////////////////////  update the config file!! //////////////////////////////////////

  } // END Create DB user

  // 12/10/2011 - Add encrypted name for a paper.
  if (!$updater_utils->does_column_exist('properties', 'crypt_name')) {
    $updater_utils->execute_query("ALTER TABLE properties ADD COLUMN crypt_name varchar(32)", true);
    
    if (!$updater_utils->does_index_exist('properties', 'crypt_name_idx')) {
      $updater_utils->execute_query("ALTER TABLE properties ADD INDEX crypt_name_idx (crypt_name)", false);
    }

    $result2 = $mysqli->prepare("SELECT property_id, UNIX_TIMESTAMP(created), paper_ownerID FROM properties");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($property_id, $created, $paper_ownerID);
    while ($result2->fetch()) {
      $hash = $property_id . $created . $paper_ownerID;
      $updater_utils->execute_query("UPDATE properties SET crypt_name='$hash' WHERE property_id=$property_id", false);
    }
    $update->close();

    $result2->close();
  }

  // 18/10/2011 - Add type to feedback_release.
  if (!$updater_utils->does_column_exist('feedback_release', 'type')) {
    $updater_utils->execute_query("ALTER TABLE feedback_release ADD COLUMN type enum('objectives','questions')", true);
    $updater_utils->execute_query("UPDATE feedback_release SET type='objectives'", false);
  }

  // 24/10/2011
  if (!$updater_utils->does_column_type_value_exist('log4_overall', 'year', 'tinyint(4)')) {
    $updater_utils->execute_query("ALTER TABLE log4_overall ADD COLUMN yearofstudy tinyint", true);
    
    $convert_years = array('year1' => 1, 'year2' => 2, 'year3' => 3, 'year4' => 4, 'year5' => 5, 'year6' => 6, 'cp1' => 3, 'cp2' => 4, 'cp3' => 5, 'f1' => 5, 'graduate' => 6);
    foreach ($convert_years as $old_year => $new_year) {
      $updater_utils->execute_query("UPDATE log4_overall SET yearofstudy=$new_year WHERE year='$old_year'", false);
    }

    $updater_utils->execute_query("ALTER TABLE log4_overall DROP COLUMN year", true);
    $updater_utils->execute_query("ALTER TABLE log4_overall CHANGE COLUMN yearofstudy year tinyint(4)", true);
  }

  // 27/10/2011
  if (!$updater_utils->does_column_type_value_exist('users', 'title', 'varchar(30)')) {
    $updater_utils->execute_query("ALTER TABLE users CHANGE COLUMN title title varchar(30)", true);
  }

  // 18/10/2011 - Add type to feedback_release.
  $result = $mysqli->prepare("SELECT * FROM questions WHERE q_type='calculation' AND score_method!='Allow Partial Marks'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows > 0) {
    $updater_utils->execute_query("UPDATE questions SET score_method='Allow Partial Marks' WHERE q_type='calculation' AND score_method!='Allow Partial Marks'", true);
  }
  $result->close();

  if (strpos(strtolower($_SERVER['HTTP_HOST']), 'nottingham.ac.uk') !== FALSE) {
    // 02/11/2011 - Set the modules who do not have negative marking.
    $updater_utils->execute_query("UPDATE modules SET neg_marking=0 WHERE vle_api='NLE'", false);
  }

  // 09/11/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='labs' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='campus'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('University Park','Jubilee','King''s Meadow','Derby','Malaysia','Ningbo','Sutton Bonington','Other')") {
    $updater_utils->execute_query("ALTER TABLE labs CHANGE COLUMN campus campus varchar(255)", true);
  }
  $result->close();

  // 09/11/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sms_imports' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='import_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('manual','SATURN UK','SATURN Malaysia','SATURN China','ARC')") {
    $updater_utils->execute_query("ALTER TABLE sms_imports CHANGE COLUMN import_type import_type varchar(255)", true);
  }
  $result->close();

  // 07/12/2011
  if (!$updater_utils->does_column_exist('log6', 'id')) {
    $sql = "CREATE TABLE log6 (id int not null primary key auto_increment, paperID smallint, reviewerID mediumint, peerID mediumint, started datetime, q_id int, rating tinyint)";
    $updater_utils->execute_query($sql, true);

    $sql = "ALTER TABLE properties CHANGE COLUMN paper_type paper_type enum('0','1','2','3','4','5','6')";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log6 TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".log6 TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 08/09/2011 - Add auth_user column to sys_errors
  if (!$updater_utils->does_column_exist('sys_errors', 'auth_user')) {
    $updater_utils->execute_query("ALTER TABLE sys_errors ADD COLUMN auth_user VARCHAR(45) DEFAULT NULL AFTER userID", true);
  }

  // 13/01/2012 - Add deleted column to Faculty table
  if (!$updater_utils->does_column_exist('faculty', 'deleted')) {
    $updater_utils->execute_query("ALTER TABLE faculty ADD COLUMN deleted datetime", true);
  }

  // 13/01/2012 - Add deleted column to Degrees table
  if (!$updater_utils->does_column_exist('degrees', 'deleted')) {
    $result2 = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='degrees' AND TABLE_SCHEMA='$cfg_db_database'"); // Check to see if Degrees exists, more recently renamed Courses.
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($table_name);
    $result2->fetch();
    if ($result2->num_rows() > 0) {
      $updater_utils->execute_query("ALTER TABLE degrees ADD COLUMN deleted datetime", true);
    }
  }

  // 13/01/2012 - Add new character set to configuration file.
  $new_cfg_str[] = "  \$cfg_db_charset = 'latin1';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');

  //remove refrances to old vars
  $cfg_new = array();
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_db_charset') !== FALSE) {
      $found = TRUE;
    }
    $cfg_new[] = $line;
  }

  if (!$found) {
    //add the new config chunk
    array_splice($cfg_new, 25, 0, $new_cfg_str);

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old1.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added database charset.</li>\n";
  }

  // 16/01/2012 - Rename Degrees table to Courses table
  if (!$updater_utils->does_table_exist('degrees')) {
    $updater_utils->execute_query("RENAME TABLE degrees TO courses", true);
    $updater_utils->execute_query("ALTER TABLE courses CHANGE COLUMN degree name varchar(255)", true);
  }

  // 19/01/2012 - Add deleted column to Schools table
  if (!$updater_utils->does_column_exist('schools', 'deleted')) {
    $updater_utils->execute_query("ALTER TABLE schools ADD COLUMN deleted datetime", true);
  }

  // 19/01/2012 - Update the version number
  $cfg_new = array();
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  foreach ($cfg as $line) {
    if (strpos($line, 'ts_version') !== FALSE) {
      $cfg_new[] = "\$rogo_version = '$version';\n";
    } else {
      $cfg_new[] = $line;
    }
  }
  if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
    echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
  }

  // 19/01/2012 - Add root path functions to config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "if (empty(\$root)) \$root = str_replace('/config', '/', str_replace('\\\\', '/', dirname(__FILE__)));\n";
  $new_cfg_str[] = "require \$root . '/include/path_functions.inc.php';\n\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'dirname(__FILE__)') !== FALSE) {
      $found = TRUE;
    }
  }

  if (!$found) {
    array_splice($cfg, 11, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old4.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
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
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_root_path') !== FALSE) {
      $found = TRUE;
    }
  }

  if (!$found) {
    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line, 'cfg_web_root =') !== FALSE) {
        $found = TRUE;
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

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
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
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'Root path for JS') !== FALSE) {
      $found = TRUE;
    }
  }

  if (!$found) {
    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line, '//Editor') !== FALSE) {
        $found = TRUE;
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
      if (strpos($line, 'cfg_editor_javascript =') !== FALSE) {
        $found = TRUE;
        break;
      }
      $index++;
    }

    if ($found) {
      unset($cfg[$index]);

      // Editor JS string was sometimes split over multiple lines. Check and remove if this is the case
      if (substr(trim($cfg[$index + 2]), 0, 2) == '";') {
        unset($cfg[$index + 2]);
      }
      if (substr(trim($cfg[$index + 1]), 0, 16) == '<script language') {
        unset($cfg[$index + 1]);
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

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
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
  $found = FALSE;
  $last_break = 0;
  $index = 0;
  foreach ($cfg as $line) {
    if (strpos($line, 'default:') !== FALSE) {
      $found = TRUE;
    }
    if (strpos($line, 'break;') !== FALSE) {
      $last_break = $index;
    }
    $index++;
  }

  if (!$found) {
    array_splice($cfg, $last_break + 1, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old7.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
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

  // 06/02/2012 - Change schools from text to integers in courses table
  if (!$updater_utils->does_column_exist('courses', 'schoolid')) {
    // Add new integer column
    $updater_utils->execute_query("ALTER TABLE courses ADD COLUMN schoolid int", true);

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
    foreach ($schools as $school_name => $schoolid) {
      $adjust = $mysqli->prepare("UPDATE courses SET schoolid=? WHERE school=?");
      $adjust->bind_param('is', $schoolid, $school_name);
      $adjust->execute();
      $adjust->close();
    }
    // Drop the old textual column
    $updater_utils->execute_query("ALTER TABLE courses DROP COLUMN school", true);
  }

  // 19/01/2012 - Add LDAP user search prefix to config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "  \$cfg_ldap_user_prefix   = 'sAMAccountName='; // Nottingham specific.  Please change.\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = FALSE;
  $ldap_pass_location = 0;
  $index = 0;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_ldap_user_prefix') !== FALSE) {
      $found = TRUE;
    }
    if (strpos($line, 'cfg_ldap_bind_password') !== FALSE) {
      $ldap_pass_location = $index;
    }
    $index++;
  }

  if (!$found) {
    array_splice($cfg, $ldap_pass_location + 1, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old8.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added LDAP user search prefix to config file.\n";
    echo "<br /><strong>If you use LDAP authentication then you will need to change the value <code>\$cfg_ldap_user_prefix</code> in <code>/config/config.inc.php</code></strong></li>\n";
    ob_flush();
    flush();
  }

  // 24/02/2012 - Add new page character set to configuration file.
  $new_cfg_str = array("\$cfg_page_charset = 'UTF-8';\n");
  $cfg = file($cfg_web_root . 'config/config.inc.php');

  //remove refrances to old vars
  $cfg_new = array();
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_page_charset') !== FALSE) {
      $found = TRUE;
    }
    $cfg_new[] = $line;
  }

  if (!$found) {
    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line, '$protocol') !== FALSE) {
        $found = TRUE;
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

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added page charset to configuration file.</li>\n";
  }

  ob_flush();
  flush();

  // 05/03/2012 - Add announcements table
  if (!$updater_utils->does_table_exist('announcements')) {
    $sql = "CREATE TABLE announcements (id int not null primary key auto_increment, title varchar(255), staff_msg text, student_msg text, icon varchar(255), startdate datetime, enddate datetime, deleted datetime)";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_student_user, 'SELECT', 'announcements', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".announcements TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_inv_username, 'SELECT', 'log2', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".log2 TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_student_user, 'SELECT', 'standards_setting', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".standards_setting TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_username, 'SELECT, UPDATE, INSERT, DELETE', 'password_tokens', $cfg_db_host)) {
    $sql = "GRANT SELECT, UPDATE, INSERT, DELETE ON " . $cfg_db_database . ".password_tokens TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'sessions', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".sessions TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }
  
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
  if (!$updater_utils->does_index_exist('users', 'idx_roles')) {
    $updater_utils->execute_query("CREATE INDEX idx_roles ON users (roles)", true);
  }
  
  if (!$updater_utils->does_index_exist('standards_setting', 'idx_std_set')) {
    $updater_utils->execute_query("CREATE INDEX idx_std_set ON standards_setting (std_set)", true);
    $updater_utils->execute_query("CREATE INDEX idx_setterID ON standards_setting (setterID)", true);
  }

  if (!$updater_utils->does_index_exist('log_metadata', 'idx_log_metadata_student_grade')) {
    $updater_utils->execute_query("CREATE INDEX idx_log_metadata_student_grade ON log_metadata (student_grade)", true);
    $updater_utils->execute_query("CREATE INDEX idx_log_metadata_paperID ON log_metadata (paperID)", true);
  }

  if (!$updater_utils->does_index_exist('log0', 'idx_log0_screen')) {
    $updater_utils->execute_query("CREATE INDEX idx_log0_screen ON log0 (screen)", true);
    $updater_utils->execute_query("CREATE INDEX idx_log1_screen ON log1 (screen)", true);
    $updater_utils->execute_query("CREATE INDEX idx_log2_screen ON log2 (screen)", true);
    $updater_utils->execute_query("CREATE INDEX idx_log3_screen ON log3 (screen)", true);
  }
  
  if (!$updater_utils->does_index_exist('courses', 'idx_courses_name')) {
    $updater_utils->execute_query("CREATE INDEX idx_courses_name ON courses (name)", true);
  }

  // 19/03/2012 - Add 'reference_material' and 'paper_reference' tables
  if (!$updater_utils->does_table_exist('reference_material')) {
    // Table to hold Reference material
    $updater_utils->execute_query("CREATE TABLE reference_material (id int not null primary key auto_increment, title varchar(255), content text,  width  SMALLINT UNSIGNED, created datetime, deleted datetime)", true);

    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_material TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_material TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_material TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    // Table to hold Reference modules
    $updater_utils->execute_query("CREATE TABLE reference_modules (id int not null primary key auto_increment, refID mediumint unsigned, moduleID mediumint unsigned)", true);

    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_modules TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_modules TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_modules TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    // Table to assign Reference material to papers
    $updater_utils->execute_query("CREATE TABLE reference_papers (id int not null primary key auto_increment, paperID mediumint, refID mediumint)", true);

    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".reference_papers TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_papers TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".reference_papers TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 05/04/2012 - Enlarge the size of the integer for property_id in properties table.
  if (!$updater_utils->does_column_type_value_exist('properties', 'property_id', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE properties CHANGE COLUMN property_id property_id mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paper in papers table.
  if (!$updater_utils->does_column_type_value_exist('papers', 'paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE papers CHANGE COLUMN paper paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for id in users table.
  if (!$updater_utils->does_column_type_value_exist('users', 'id', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE users CHANGE COLUMN id id int(10) UNSIGNED NOT NULL AUTO_INCREMENT", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in sid table.
  if (!$updater_utils->does_column_type_value_exist('sid', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE sid CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for memberID in teams table.
  if (!$updater_utils->does_column_type_value_exist('teams', 'memberID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE teams CHANGE COLUMN memberID memberID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log0 table.
  if (!$updater_utils->does_column_type_value_exist('log0', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log0 CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log0 table.
  if (!$updater_utils->does_column_type_value_exist('log0', 'q_paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log0 CHANGE COLUMN q_paper q_paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log1 table.
  if (!$updater_utils->does_column_type_value_exist('log1', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log1 CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log1 table.
  if (!$updater_utils->does_column_type_value_exist('log1', 'q_paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log1 CHANGE COLUMN q_paper q_paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log2 table.
  if (!$updater_utils->does_column_type_value_exist('log2', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log2 CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log2 table.
  if (!$updater_utils->does_column_type_value_exist('log2', 'q_paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log2 CHANGE COLUMN q_paper q_paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log3 table.
  if (!$updater_utils->does_column_type_value_exist('log3', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log3 CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log3 table.
  if (!$updater_utils->does_column_type_value_exist('log3', 'q_paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log3 CHANGE COLUMN q_paper q_paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log4 table.
  if (!$updater_utils->does_column_type_value_exist('log4', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log4 CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log4 table.
  if (!$updater_utils->does_column_type_value_exist('log4', 'q_paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log4 CHANGE COLUMN q_paper q_paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log4_overall table.
  if (!$updater_utils->does_column_type_value_exist('log4_overall', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log4_overall CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log4_overall table.
  if (!$updater_utils->does_column_type_value_exist('log4_overall', 'q_paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log4_overall CHANGE COLUMN q_paper q_paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log5 table.
  if (!$updater_utils->does_column_type_value_exist('log5', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log5 CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log5 table.
  if (!$updater_utils->does_column_type_value_exist('log5', 'q_paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log5 CHANGE COLUMN q_paper q_paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log6 table.
  if (!$updater_utils->does_column_type_value_exist('log6', 'peerID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log6 CHANGE COLUMN peerID peerID int(10) unsigned", true);
    $updater_utils->execute_query("ALTER TABLE log6 CHANGE COLUMN reviewerID reviewerID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paperID in log6 table.
  if (!$updater_utils->does_column_type_value_exist('log6', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log6 CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in log_late table.
  if (!$updater_utils->does_column_type_value_exist('log_late', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log_late CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log_late table.
  if (!$updater_utils->does_column_type_value_exist('log_late', 'q_paper', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log_late CHANGE COLUMN q_paper q_paper int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for q_paper in log_metadata table.
  if (!$updater_utils->does_column_type_value_exist('log_metadata', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log_metadata CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for ownerID in questions table.
  if (!$updater_utils->does_column_type_value_exist('questions', 'ownerID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE questions CHANGE COLUMN ownerID ownerID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paper_ownerID in properties table.
  if (!$updater_utils->does_column_type_value_exist('properties', 'paper_ownerID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE properties CHANGE COLUMN paper_ownerID paper_ownerID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for editor in track_changes table.
  if (!$updater_utils->does_column_type_value_exist('track_changes', 'editor', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE track_changes CHANGE COLUMN editor editor int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in textbox_marking table.
  if (!$updater_utils->does_column_type_value_exist('textbox_marking', 'markerID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE textbox_marking CHANGE COLUMN markerID markerID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in textbox_remark table.
  if (!$updater_utils->does_column_type_value_exist('textbox_remark', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE textbox_remark CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in student_modules table.
  if (!$updater_utils->does_column_type_value_exist('student_modules', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE student_modules CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in student_notes table.
  if (!$updater_utils->does_column_type_value_exist('student_notes', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE student_notes CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for note_authorID in student_notes table.
  if (!$updater_utils->does_column_type_value_exist('student_notes', 'note_authorID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE student_notes CHANGE COLUMN note_authorID note_authorID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paper_id in student_notes table.
  if (!$updater_utils->does_column_type_value_exist('student_notes', 'paper_id', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE student_notes CHANGE COLUMN paper_id paper_id mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in special_needs table.
  if (!$updater_utils->does_column_type_value_exist('special_needs', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE special_needs CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for reviewer in review_comments table.
  if (!$updater_utils->does_column_type_value_exist('review_comments', 'reviewer', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE review_comments CHANGE COLUMN reviewer reviewer int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paper_id in paper_notes table.
  if (!$updater_utils->does_column_type_value_exist('paper_notes', 'paper_id', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE paper_notes CHANGE COLUMN paper_id paper_id mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in question_exclude table.
  if (!$updater_utils->does_column_type_value_exist('question_exclude', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE question_exclude CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for ownerID in questions table.
  if (!$updater_utils->does_column_type_value_exist('questions', 'ownerID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE questions CHANGE COLUMN ownerID ownerID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for checkout_authorID in questions table.
  if (!$updater_utils->does_column_type_value_exist('questions', 'checkout_authorID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE questions CHANGE COLUMN checkout_authorID checkout_authorID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in recent_papers table.
  if (!$updater_utils->does_column_type_value_exist('recent_papers', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE recent_papers CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paperID in reference_papers table.
  if (!$updater_utils->does_column_type_value_exist('reference_papers', 'paper_id', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE reference_papers CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in relationships table.
  if (!$updater_utils->does_column_type_value_exist('relationships', 'paper_id', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE relationships CHANGE COLUMN paper_id paper_id mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the enum for calendar_year in relationships table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='relationships' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='calendar_year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if (strpos($data_type, '2019/20') === FALSE) {
    $updater_utils->execute_query("ALTER TABLE relationships CHANGE COLUMN calendar_year calendar_year enum('2006/07','2007/08','2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20')", true);
  }
  $result->close();

  // 05/04/2012 - Enlarge the size of the enum for calendar_year in sessions table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sessions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='calendar_year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if (strpos($data_type, '2019/20') === FALSE) {
    $updater_utils->execute_query("ALTER TABLE sessions CHANGE COLUMN calendar_year calendar_year enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20')", true);
  }
  $result->close();

  // 05/04/2012 - Enlarge the size of the enum for calendar_year in student_modules table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sessions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='calendar_year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if (strpos($data_type, '2019/20') === FALSE) {
    $updater_utils->execute_query("ALTER TABLE sessions CHANGE COLUMN calendar_year calendar_year enum('2002/03','2003/04','2004/05','2005/06','2006/07','2007/08','2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20')", true);
  }
  $result->close();

  // 05/04/2012 - Enlarge the size of the enum for calendar_year in properties table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='calendar_year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if (strpos($data_type, '2019/20') === FALSE) {
    $updater_utils->execute_query("ALTER TABLE properties CHANGE COLUMN calendar_year calendar_year enum('2002/03','2003/04','2004/05','2005/06','2006/07','2007/08','2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20')", true);
  }
  $result->close();

  // 05/04/2012 - Enlarge the size of the enum for calendar_year in objectives table.
  $data_type = '';
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='objectives' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='calendar_year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if (strpos($data_type, '2019/20') === FALSE) {
    $updater_utils->execute_query("ALTER TABLE objectives CHANGE COLUMN calendar_year calendar_year enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20')", true);
    
  }
  $result->close();

  // 05/04/2012 - Enlarge the size of the integer for userID in recent_papers table.
  if (!$updater_utils->does_column_type_value_exist('recent_papers', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE recent_papers CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paperID in standards_setting table.
  if (!$updater_utils->does_column_type_value_exist('standards_setting', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE standards_setting CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paperID in sct_reviews table.
  if (!$updater_utils->does_column_type_value_exist('sct_reviews', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE sct_reviews CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paperID in review_comments table.
  if (!$updater_utils->does_column_type_value_exist('review_comments', 'q_paper', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE review_comments CHANGE COLUMN q_paper q_paper mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paperID in review_comments table.
  if (!$updater_utils->does_column_type_value_exist('review_comments', 'reviewer', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE review_comments CHANGE COLUMN reviewer reviewer int(10) unsigned", true);
  }

  // 05/04/2012 - Resize the integer for paperID in sys_errors table.
  if (!$updater_utils->does_column_type_value_exist('sys_errors', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE sys_errors CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Resize the integer for paper_id in relationships table.
  if (!$updater_utils->does_column_type_value_exist('relationships', 'paper_id', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE relationships CHANGE COLUMN paper_id paper_id mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for paperID in feedback_release table.
  if (!$updater_utils->does_column_type_value_exist('feedback_release', 'paper_id', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE feedback_release CHANGE COLUMN paper_id paper_id mediumint(8) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in keywords_user table.
  if (!$updater_utils->does_column_type_value_exist('keywords_user', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE keywords_user CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in help_searches table.
  if (!$updater_utils->does_column_type_value_exist('help_searches', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE help_searches CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in help_tutorial_log table.
  if (!$updater_utils->does_column_type_value_exist('help_tutorial_log', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE help_tutorial_log CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in help_log table.
  if (!$updater_utils->does_column_type_value_exist('help_log', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE help_log CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for ownerID in folders table.
  if (!$updater_utils->does_column_type_value_exist('folders', 'ownerID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE folders CHANGE COLUMN ownerID ownerID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for setterID in ebel table.
  if (!$updater_utils->does_column_type_value_exist('ebel', 'setterID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE ebel CHANGE COLUMN setterID setterID int(10) unsigned", true);
  }

  // 05/04/2012 - Enlarge the size of the integer for userID in admin_access table.
  if (!$updater_utils->does_column_type_value_exist('admin_access', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE admin_access CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 05/04/2012 - Resize the integer for paper_id in paper_metadata_security table.
  if (!$updater_utils->does_column_type_value_exist('paper_metadata_security', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE paper_metadata_security CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 19/04/2012 - Add 'state' tables
  $col_exists = $updater_utils->does_column_exist('state', 'userID');
  if (!$col_exists) {
    // Table to hold Reference material
    $updater_utils->execute_query("CREATE TABLE state (userID int unsigned, state_name varchar(255), content varchar(255), page varchar(255))", true);
    
    $updater_utils->execute_query("ALTER TABLE state ADD UNIQUE idx_user_state (userID, state_name, page)", true);
    
    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '" . $cfg_db_sysadmin_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".state TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 24/04/2012 - Add default timezone config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "  date_default_timezone_set(\$cfg_timezone);\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = FALSE;
  $target_line = 53;
  $cur_line = 0;
  foreach ($cfg as $line) {
    if (strpos($line, 'date_default_timezone_set') !== FALSE) {
      $found = TRUE;
    }
    if (strpos($line, 'cfg_timezone') !== FALSE) {
      $target_line = $cur_line + 1;
    }
    $cur_line++;
  }

  if (!$found) {
    array_splice($cfg, $target_line, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Add default timezone config file.</li>\n";
    ob_flush();
    flush();
  }

  // 24/04/2012 - Add temp directory specification to config file.
  $new_cfg_str = array();
  $new_cfg_str[] = "\$cfg_tmpdir = '/tmp/';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_tmpdir') !== FALSE) {
      $found = TRUE;
    }
  }

  if (!$found) {
    array_splice($cfg, 22, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old4.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
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
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, "define('TOUCHSTONE'") === FALSE and strpos($line, "define('DIR_SEPARATOR'") === FALSE and strpos($line, "\$news") === FALSE) {
      $new_cfg_str[] = $line;
    } else {
      $found = TRUE;
    }
  }

  $cfg = $new_cfg_str;

  if ($found) {
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old4.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Removed unneccessary lines from configuration (defines and \$news).</li>\n";
    ob_flush();
    flush();
  }


  // 02/05/2012 - Update the online help files.
  if (isset($_POST['update_staff_help'])) {
    $updater_utils->execute_query("TRUNCATE student_help", true);

    $file = file_get_contents('../install/staff_help.sql');
    $mysqli->multi_query($file);
    if ($mysqli->error) {
      try {
        throw new Exception("MySQL error $mysqli->error <br> Query:<br> ", $mysqli->errno);
      } catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
        echo nl2br($e->getTraceAsString());
        exit();
      }
    }
    $ext = '';
    while ($mysqli->more_results()) {
      $mysqli->next_result();
      if ($mysqli->insert_id > 0) $ext = $ext . ' ' . $mysqli->insert_id;
    }
    echo "<li>LOADED staff_help: " . $ext . "</li>\n";
  }

  if (isset($_POST['update_student_help'])) {
    $updater_utils->execute_query("TRUNCATE student_help", true);

    $file = file_get_contents('../install/student_help.sql');
    $mysqli->multi_query($file);
    if ($mysqli->error) {
      try {
        throw new Exception("MySQL error $mysqli->error <br /> Query:<br /> ", $mysqli->errno);
      } catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
        echo nl2br($e->getTraceAsString());
        exit();
      }
    }
    $ext = '';
    while ($mysqli->more_results()) {
      $mysqli->next_result();
      if ($mysqli->insert_id > 0) $ext = $ext . ' ' . $mysqli->insert_id;
    }
    echo "<li>LOADED student_help: " . $ext . "</li>\n";
  }

  // 02/05/2012 - Update the version number
  $cfg_new = array();
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  foreach ($cfg as $line) {
    if (strpos($line, 'rogo_version') !== FALSE) {
      $cfg_new[] = "\$rogo_version = '$version';\n";
    } else {
      $cfg_new[] = $line;
    }
  }
  if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
    echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
  }
  @ob_flush();
  @flush();

  // Staff user was missing DELETE privileges on properties in the install script
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'properties', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".properties TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 15/05/2012 -  Add LTI Tables
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_keys' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Table to hold Reference material
    $sql = "CREATE TABLE IF NOT EXISTS  " . $cfg_db_database . ".`lti_user` (  `oauth_consumer_key` varchar(200) NOT NULL,  `user_id` varchar(200) NOT NULL,  `rogo_id` int(11) NOT NULL,  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`oauth_consumer_key`,`user_id`),  KEY `rogo_id` (`rogo_id`)) ENGINE=InnoDB";
    $updater_utils->execute_query($sql, true);

    $sql = "CREATE TABLE IF NOT EXISTS  " . $cfg_db_database . ".`lti_resource` (  `oauth_consumer_key` varchar(255) NOT NULL DEFAULT '',  `lti_resource_id` varchar(255) NOT NULL,  `internal_id` varchar(255) DEFAULT NULL,  `itype` varchar(255) DEFAULT NULL,  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`oauth_consumer_key`,`lti_resource_id`),  KEY `destination2` (`itype`),  KEY `destination` (`internal_id`)) ENGINE=InnoDB";
    $updater_utils->execute_query($sql, true);

    $sql = "CREATE TABLE IF NOT EXISTS  " . $cfg_db_database . ".`lti_keys` (  `id` mediumint(9) NOT NULL AUTO_INCREMENT,  `oauth_consumer_key` char(255)NOT NULL,  `secret` char(255)DEFAULT NULL,  `name` char(255) DEFAULT NULL,  `context_id` char(255) DEFAULT NULL,  `created_at` datetime NOT NULL, `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`)) ENGINE=InnoDB";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_keys TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_keys TO '" . $cfg_db_sysadmin_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_user TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_resource TO '" . $cfg_db_sysadmin_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_resource TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".lti_resource TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 16/05/2012 - Enlarge the size of the password field to hold higher level of encryption SHA-512.
  if (!$updater_utils->does_column_type_value_exist('users', 'password', 'char(90)')) {
    $updater_utils->execute_query("ALTER TABLE users CHANGE COLUMN password password char(90)", true);
  }

  // 16/05/2012 - Add encryption salt to config file.
  $new_cfg_str = array();
  //$new_cfg_str[] =  "  \$cfg_encrypt_salt = 'K8m2hzflkgjzdfgj';\n";
  $new_cfg_str[] = "  \$cfg_encrypt_salt       = '" . gen_random_salt() . "';    // Do not alter if not on LDAP.\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = FALSE;
  $cur_line = 0;
  $target_line = 66;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_encrypt_salt') !== FALSE) {
      $found = TRUE;
    }
    if (strpos($line, 'cfg_use_ldap') !== FALSE) {
      $target_line = $cur_line + 1;
    }
    $cur_line++;
  }

  if (!$found) {
    array_splice($cfg, $target_line, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Add \$cfg_encrypt_salt to config file.</li>\n";
    ob_flush();
    flush();
  }

  // 22/05/2012 -  Chnage LTI Tables
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_keys' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='deleted'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $sql = "ALTER TABLE `lti_keys` CHANGE `created_at` `deleted` DATETIME NULL , CHANGE `updated_at` `updated_at` DATETIME NOT NULL";
    $updater_utils->execute_query($sql, true);

    $sql = "UPDATE `lti_keys` set `deleted`=NULL WHERE `deleted`='0000-00-00 00:00:00'";
    $updater_utils->execute_query($sql, true);

    $sql = "ALTER TABLE `lti_resource` CHANGE `updated` `updated` DATETIME NOT NULL";
    $updater_utils->execute_query($sql, true);

    $sql = "ALTER TABLE `lti_user` CHANGE `updated_on` `updated_on` DATETIME NOT NULL";
    $updater_utils->execute_query($sql, true);
  }

  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_context' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $sql = "CREATE TABLE IF NOT EXISTS  " . $cfg_db_database . ".`lti_context` (`oauth_consumer_key` VARCHAR(255) NOT NULL ,`lti_context_id` VARCHAR(255) NOT NULL ,`c_internal_id` VARCHAR(255) NOT NULL ,`updated_on` DATETIME NOT NULL, PRIMARY KEY (`oauth_consumer_key`,`lti_context_id`), KEY `c_internal_id` (`c_internal_id`)) ENGINE=InnoDB";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_context TO '" . $cfg_db_sysadmin_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".lti_context TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".lti_context TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 22/05/2012 - Addition of grey personal folder
  $column_type = '';
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='folders' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='color'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('yellow','red','green','blue')") {
    $updater_utils->execute_query("ALTER TABLE folders CHANGE COLUMN color color enum('yellow','red','green','blue','grey')", true);
  }
  $result->close();

  // 28/05/2012 - Add new autosave timeout.

  $cfg = file($cfg_web_root . 'config/config.inc.php');

  //remove refrances to old vars
  $cfg_new = array();
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_autosave_timeout') !== FALSE) {
      $found = TRUE;
    }
    $cfg_new[] = $line;
  }

  if (!$found) {
    $new_cfg_str = array("\$cfg_autosave_timeout = 180;\n");
    $index = 0;
    foreach ($cfg as $line) {
      if (strpos($line, '$cfg_hour_warning') !== FALSE) {
        $found = TRUE;
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

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added  new autosave timeout to configuration file.</li>\n";
  }

  // 28/05/2012 - Add permission for external examiners to view student help.
  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'student_help', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".student_help TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 29/05/2012 - Add 'scheduling' tables
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='scheduling' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Table to hold Reference material
    $sql = "CREATE TABLE scheduling (id int not null primary key auto_increment, paperID int, period varchar(255), barriers_needed tinyint, cohort_size varchar(20), notes text, sittings tinyint, campus varchar(255))";
    $updater_utils->execute_query($sql, true);
    
    $updater_utils->execute_query("ALTER TABLE scheduling ADD UNIQUE idx_paperID (paperID)", true);

    $sql = "GRANT SELECT, INSERT, DELETE ON " . $cfg_db_database . ".scheduling TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $new_cfg_str = array();
    $new_cfg_str[] = "\$cfg_summative_mgmt = false;     // Set this to true for central summative exam administration.";
    $cfg = file($cfg_web_root . 'config/config.inc.php');
    $found = FALSE;
    $cur_line = 0;
    $target_line = 24;
    foreach ($cfg as $line) {
      if (strpos($line, 'cfg_summative_mgmt') !== FALSE) {
        $found = TRUE;
      }
      if (strpos($line, 'cfg_tmpdir') !== FALSE) {
        $target_line = $cur_line + 1;
      }
      $cur_line++;
    }

    if (!$found) {
      array_splice($cfg, $target_line, 0, $new_cfg_str);
      if (file_exists($cfg_web_root . 'config/config.inc.php')) {
        rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
      }

      if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
        echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
      }
      echo "<li>Add \$cfg_summative_mgmt = false.</li>\n";
      ob_flush();
      flush();
    }
  }
  $result->close();


  // 15/06/2012 - Add performance tables to store p and d values against questions in the bank.
  if (!$updater_utils->does_table_exist('performance_main')) {
    $sql = "CREATE TABLE performance_main (id int not null primary key auto_increment, q_id int unsigned, paperID int unsigned, percentage tinyint, cohort_size int unsigned, taken date)";
    $updater_utils->execute_query($sql, true);
    
    $updater_utils->execute_query("ALTER TABLE performance_main ADD INDEX idx_q_id (q_id)", true);

    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".performance_main TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $updater_utils->execute_query("CREATE TABLE performance_details (perform_id int, part_no tinyint, p tinyint, d tinyint)", true);

    $updater_utils->execute_query("ALTER TABLE performance_details ADD INDEX idx_perform_id (perform_id)", true);

    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".performance_details TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // Delete permission might be missing on log_late for staff (21/06/2012)
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'log_late', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log_late TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, false);
  }

  // 26/06/2012 - add new index to review_comments
  if (!$updater_utils->does_index_exist('review_comments', 'idx_q_paper')) {
    $updater_utils->execute_query("CREATE INDEX idx_q_paper ON review_comments (q_paper)", true);
  }

  // Delete permission might be missing on papers and state (28/06/2012)
  
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'papers', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".papers TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }
  
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'state', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".state TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }
  
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'state', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".state TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 21/03/2012 - Move to InnoDB for all table except help tables SHOULD not go live untill ver 4.3 - With full testing
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE ENGINE='MyISAM' AND TABLE_SCHEMA = '" . $cfg_db_database . "'");
  $result->execute();
  $result->store_result();
  $result->bind_result($name);
  $skip_table = Array('help_log' => 1, 'help_searches' => 1, 'help_tutorial_log' => 1, 'staff_help' => 1, 'student_help' => 1);
  while ($result->fetch()) {
    if (isset($skip_table[$name])) {
      continue;
    }
    $updater_utils->execute_query("ALTER TABLE $name ENGINE=InnoDB", true);
  }

  //update student_modules.moduleid to a char(25)
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='student_modules' AND TABLE_SCHEMA='touchstone' and COLUMN_NAME = 'moduleid' and COLUMN_TYPE = 'char(15)' AND TABLE_SCHEMA = '" . $cfg_db_database . "'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    $updater_utils->execute_query("ALTER TABLE student_modules CHANGE moduleid moduleid char(25)", true);
  }
  $result->close();

  // 05/07/2012 - Add VLE API reference to relationships table (for historical references) and update for modules using NLE
  $result_col = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='relationships' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='vle_api'");
  $result_col->execute();
  $result_col->store_result();
  $result_col->bind_result($column_type);
  $result_col->fetch();
  if ($result_col->num_rows() == 0) {
    // First fix '0' values in modules table
    $updater_utils->execute_query("UPDATE modules SET vle_api=NULL WHERE vle_api = '0'", true);

    $updater_utils->execute_query("ALTER TABLE relationships ADD COLUMN vle_api varchar(255) NOT NULL DEFAULT ''", true);

    $mod_count = 0;
    $result_mod = $mysqli->prepare("SELECT moduleid FROM modules WHERE vle_api='NLE'");
    $result_mod->execute();
    $result_mod->store_result();
    $result_mod->bind_result($moduleid);
    while ($result_mod->fetch()) {
      $update = $mysqli->prepare("UPDATE relationships SET vle_api='NLE' WHERE module_id=?");
      $update->bind_param('s', $moduleid);
      $update->execute();
      $update->close();
      $mod_count++;
    }
    echo "<li>Updated relationships table for $mod_count modules</li>\n";

    $result_mod->close();
  }
  $result_col->close();

  // 18/07/2012
  // Add index to improve performance for finding question copying in the Information dialog box.
  $result = $mysqli->prepare("SHOW INDEX FROM track_changes");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    $updater_utils->execute_query("ALTER TABLE track_changes ADD INDEX(type)", true);
  }
  $result->close();

  // 27/07/2012 - Remove invalid entries from track changes
  $result = $mysqli->prepare("SELECT typeID FROM track_changes WHERE typeID < 1 LIMIT 1");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    $updater_utils->execute_query("DELETE FROM track_changes WHERE typeID < 1", true);
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
    $updater_utils->execute_query("ALTER TABLE users ADD COLUMN user_deleted datetime", true);
  }
  $result->close();


  // 03/08/2012 - Add session change over date.
  $new_cfg_str = array();
  $new_cfg_str[] = "\$cfg_academic_year_start = '07/01';\n";
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $found = FALSE;
  foreach ($cfg as $line) {
    if (strpos($line, 'cfg_academic_year_start') !== FALSE) {
      $found = TRUE;
    }
  }

  if (!$found) {
    array_splice($cfg, 20, 0, $new_cfg_str);
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old3.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added academic_year_start to config file.</li>\n";
    ob_flush();
    flush();
  }

  // 15/08/2012 - cczsa1 adding unknown school and faculty
  require_once $cfg_web_root . 'classes/facultyutils.class.php';
  require_once $cfg_web_root . 'classes/schoolutils.class.php';

  $result = $mysqli->prepare("SELECT id FROM " . $cfg_db_database . ".faculty  WHERE name='UNKNOWN Faculty'");
  $result->execute();
  $result->store_result();
  $result->bind_result($facultyID);
  $result->fetch();
  $rows = $result->num_rows();
  $result->close();
  if ($rows == 0) {
    $facultyID = FacultyUtils::add_faculty('UNKNOWN Faculty', $mysqli);
    echo "<li>Adding Unknown Faculty</li>\n";
  }

  $result = $mysqli->prepare("SELECT id FROM " . $cfg_db_database . ".`schools`  WHERE school='UNKNOWN School'");
  $result->execute();
  $result->store_result();
  $result->bind_result($id1);
  $result->fetch();
  $rows = $result->num_rows();
  $result->close();
  if ($rows == 0) {
    $scoolID = SchoolUtils::add_school($facultyID, 'UNKNOWN School', $mysqli);
    echo "<li>Adding Unknown School</li>\n";
  }

  // 24/08/2012 -- add access to on External Examiners
  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'staff_help', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".staff_help TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'users', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".users TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'special_needs', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".special_needs TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT, INSERT', 'help_log', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".help_log TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT, INSERT', 'help_searches', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".help_searches TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 28/08/2012 - Add 'area' question type
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='q_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();
  if ($column_type == "enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false')") {
    $sql = "ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false','area')";
    $updater_utils->execute_query($sql, true);
  }

  // 04/09/2012 - add new index to schools
  $result = $mysqli->prepare("SHOW INDEX FROM schools WHERE Key_name = 'idx_facultyID'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 0) {
    $updater_utils->execute_query("CREATE INDEX `idx_facultyID` ON `schools` (`facultyID`)", true);
  }
  $result->close();


  // cczsa1 2012/09/05 update table structure to match new lti (somehow this has dissapeared from this file somewhere in the past)
  $result = $mysqli->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_user' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='oauth_consumer_key'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {
    $updater_utils->execute_query("UPDATE `lti_user` set oauth_consumer_key=CONCAT(oauth_consumer_key,':',user_id)", true);
    
    $updater_utils->execute_query("ALTER TABLE `lti_user` DROP COLUMN user_id", true);
        
    $updater_utils->execute_query("ALTER TABLE `lti_user` CHANGE `oauth_consumer_key` `lti_user_key` varchar(255)", true);
        
    $updater_utils->execute_query("ALTER TABLE `lti_user` CHANGE `rogo_id` `lti_user_equ` varchar(255) NOT NULL", true);
        
    $updater_utils->execute_query("ALTER TABLE `lti_user` CHANGE `updated_on` `updated_on` datetime NOT NULL", true);
  }
  
  $result = $mysqli->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_context' AND COLUMN_NAME='oauth_consumer_key' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {
    $sql = "UPDATE `lti_context` SET `oaurth_consumer_key`=CONCAT(`oauth_consumer_key`,':',`lti_context_id`)";
    $updater_utils->execute_query($sql, true);
    
    $updater_utils->execute_query("ALTER TABLE `lti_context` DROP `lti_context_id`", true);

    $updater_utils->execute_query("ALTER TABLE `lti_context` CHANGE `oauth_consumer_key` `lti_context_key` VARCHAR(255) NOT NULL", true);
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
    $updater_utils->execute_query("UPDATE `lti_resource` SET `lti_resource_id`=CONCAT(`oauth_consumer_key`,':',`lti_resource_id`)", true);

    $updater_utils->execute_query("ALTER TABLE `lti_resource` DROP `oauth_consumer_key`", true);

    $updater_utils->execute_query("ALTER TABLE `lti_resource` CHANGE `lti_resource_id` `lti_resource_key` VARCHAR(255) NOT NULL", true);

    $updater_utils->execute_query("ALTER TABLE `lti_resource` CHANGE `itype` `internal_type` VARCHAR(255) NOT NULL", true);

    $updater_utils->execute_query("ALTER TABLE `lti_resource` CHANGE `updated` `updated_on` DATETIME", true);
  }
  $result->close();

  $result = $mysqli->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='lti_keys' AND COLUMN_NAME='updated_at' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {
    $updater_utils->execute_query("ALTER TABLE `lti_keys` CHANGE `updated_at` `updated_on` DATETIME", true);
  }
  $result->close();

  // 03/09/2012 Permissions fix for staff users
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'log5', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log5 TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'log_metadata', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".log_metadata TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT', 'modules', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".modules TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //cczsa1 2012-09-07 add permission to sid table for main user
  if (!$updater_utils->has_grant($cfg_db_username, 'SELECT, INSERT', 'sid', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".sid TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  if (!$updater_utils->has_grant($cfg_db_username, 'SELECT, INSERT, UPDATE', 'users', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".users TO '" . $cfg_db_username . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 06/09/2012 - Delete the blank 'parent' books from the staff help
  $updater_utils->execute_query("DELETE FROM staff_help WHERE body = ''", false);
  
  // 06/09/2012 - Delete the blank 'parent' books from the student help
  $updater_utils->execute_query("DELETE FROM student_help WHERE body = ''", false);
  

  $new_cfg_str = array();
  $new_cfg_str[] = "\r\n";
  $new_cfg_str[] = "// LTI these configure the default lti integration if you want more ability than this then you will need to override the lti_integration class (in config/integration called lti-integration.class.php), UoN version is shipped in the -UoN folder\r\n";
  $new_cfg_str[] = "\$cfg_lti_allow_module_self_reg = false; // allows rogo to auto add student to module if selfreg is set for module if from lti launch\r\n";
  $new_cfg_str[] = "\$cfg_lti_allow_staff_module_register = false; // allows rogo to register staff onto the module team if set to true and from lti launch and staff in vle\r\n";
  $new_cfg_str[] = "\$cfg_lti_allow_module_create = false;  // allows rogo to create module if it doesnt exist\r\n";
  $new_cfg_str[] = "\r\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');


  // 17/09/2012 cczsa1 update to make database consistant with new install
  $findsql = "SELECT column_type from information_schema.COLUMNS where TABLE_NAME='student_modules'  and TABLE_SCHEMA='" . $cfg_db_database . "' and column_type=\"enum('2002/03','2003/04','2004/05','2005/06','2006/07','2007/08','2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15')\"";
  $result = $mysqli->prepare($findsql);
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() > 0) {
    $updater_utils->execute_query("ALTER TABLE `student_modules` CHANGE `calendar_year` `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') DEFAULT NULL", true);
  }
  $result->close();

  $findsql = "SELECT column_type from information_schema.COLUMNS where TABLE_NAME='users_metadata'  and TABLE_SCHEMA='" . $cfg_db_database . "'  and column_name='userID' and column_type like 'int%unsigned'";
  $result = $mysqli->prepare($findsql);
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $updater_utils->execute_query("ALTER TABLE `users_metadata` CHANGE `userID` `userID` int unsigned default NULL", true);
  }
  $result->close();

  $findsql = "SELECT column_type from information_schema.COLUMNS where TABLE_NAME='textbox_remark'  and TABLE_SCHEMA='" . $cfg_db_database . "'  and column_name='paperID' and column_type like 'mediumint%unsigned'";
  $result = $mysqli->prepare($findsql);
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $updater_utils->execute_query("ALTER TABLE `textbox_remark` CHANGE `paperID` `paperID` mediumint unsigned DEFAULT NULL", true);
  }
  $result->close();

  @ob_flush();
  @flush();

  //remove references to old vars
  $cfg_new = array();
  $found = FALSE;
  foreach ($cfg as $curline => $line) {

    if (strpos($line, 'cfg_lti_allow_module_self_reg') !== FALSE) {
      $found = TRUE;
    }
    if (strpos($line, 'cfg_sms_api') !== FALSE) {
      $target_line = $curline + 1;
    }
    $cfg_new[] = $line;
  }

  if (!$found) {
    //add the new config chunk
    array_splice($cfg_new, $target_line, 0, $new_cfg_str);


    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old12.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Add lti config variables</li>\n";
  }

  if (!$updater_utils->has_grant($cfg_db_staff_user, 'INSERT', 'sms_imports', $cfg_db_host)) {
    $sql = "GRANT INSERT ON " . $cfg_db_database . ".sms_imports TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $mysqli->query($sql);
  }

  // 14/09/2012 - Change the way borders are done on images in the Staff help system.
  $result = $mysqli->prepare("SELECT id, body FROM staff_help WHERE body LIKE '%border=%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $body);
  while ($result->fetch()) {
    $patterns = '/(<img .*)(border="1")(.*>)/i';
    $replace = '${1}class="image_brd"${3}';
    $body = preg_replace($patterns, $replace, $body);

    $patterns = '/(<img .*)(border=1)(.*>)/i';
    $replace = '${1}class="image_brd"${3}';
    $body = preg_replace($patterns, $replace, $body);

    $patterns = '/(<img .*)(border="0")(.*>)/i';
    $replace = '${1}class="image_no_brd"${3}';
    $body = preg_replace($patterns, $replace, $body);

    $patterns = '/(<img .*)(border=0)(.*>)/i';
    $replace = '${1}class="image_no_brd"${3}';
    $body = preg_replace($patterns, $replace, $body);

    $body_plain = strip_tags($body);

    $update = $mysqli->prepare("UPDATE staff_help SET body=?, body_plain=? WHERE id=?");
    $update->bind_param('ssi', $body, $body_plain, $id);
    $update->execute();
    $update->close();
  }
  $result->close();

  // 14/09/2012 - Change the way borders are done on images in the Student help system.
  $result = $mysqli->prepare("SELECT id, body FROM student_help WHERE body LIKE '%border=%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $body);
  while ($result->fetch()) {
    $patterns = '/(<img .*)(border="1")(.*>)/i';
    $replace = '${1}class="image_brd"${3}';
    $body = preg_replace($patterns, $replace, $body);

    $patterns = '/(<img .*)(border=1)(.*>)/i';
    $replace = '${1}class="image_brd"${3}';
    $body = preg_replace($patterns, $replace, $body);

    $patterns = '/(<img .*)(border="0")(.*>)/i';
    $replace = '${1}class="image_no_brd"${3}';
    $body = preg_replace($patterns, $replace, $body);

    $patterns = '/(<img .*)(border=0)(.*>)/i';
    $replace = '${1}class="image_no_brd"${3}';
    $body = preg_replace($patterns, $replace, $body);

    $body_plain = strip_tags($body);

    $update = $mysqli->prepare("UPDATE student_help SET body=?, body_plain=? WHERE id=?");
    $update->bind_param('ssi', $body, $body_plain, $id);
    $update->execute();
    $update->close();
  }
  $result->close();

  // 19/09/2012 - remove ID field from users_metadata
  $result = $mysqli->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='users_metadata' AND COLUMN_NAME='id' AND TABLE_SCHEMA='$cfg_db_database'");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    $updater_utils->execute_query("ALTER TABLE users_metadata DROP COLUMN id", true);
  }
  $result->close();

  // 19/09/2012 - add new index to users_metadata
  if (!$updater_utils->does_column_exist('users_metadata', 'idx_users_metadata')) {
    $updater_utils->execute_query("ALTER TABLE users_metadata ADD UNIQUE idx_users_metadata (userID, moduleID, type, calendar_year)", true);
  }

  // 21/09/2012 - Create new 'class_totals_test_local' table to hold progress in class totals comparison test.
  if (!$updater_utils->does_table_exist('class_totals_test_local')) {
    $sql = <<< QUERY
        CREATE TABLE `class_totals_test_local` (
          `id` int NOT NULL AUTO_INCREMENT,
          `user_id` int unsigned DEFAULT NULL,
          `paper_id` mediumint unsigned DEFAULT NULL,
          `status` enum('in_progress','success','failure') DEFAULT NULL,
          `errors` text,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$cfg_db_charset}
QUERY;

    $updater_utils->execute_query($sql, true);
  }

  // 25/09/2012 - Enlarge the size of the integer for userID in log_metadata table.
  if (!$updater_utils->does_column_type_value_exist('log_metadata', 'userID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE log_metadata CHANGE COLUMN userID userID int(10) unsigned", true);
  }

  // 25/09/2012 - Enlarge the size of the integer for note_authorID in paper_notes table.
  if (!$updater_utils->does_column_type_value_exist('paper_notes', 'note_authorID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE paper_notes CHANGE COLUMN note_authorID note_authorID int(10) unsigned", true);
  }

  // 25/09/2012 - Enlarge the size of the integer for note_authorID in student_help table.
  if (!$updater_utils->does_column_type_value_exist('student_help', 'checkout_authorID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE student_help CHANGE COLUMN checkout_authorID checkout_authorID int(10) unsigned", true);
  }

  // 25/09/2012 - Enlarge the size of the integer for setterID in standards_setting table.
  if (!$updater_utils->does_column_type_value_exist('standards_setting', 'setterID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE standards_setting CHANGE COLUMN setterID setterID int(10) unsigned", true);
  }

  // 25/09/2012 - Enlarge the size of the integer for note_authorID in staff_help table.
  if (!$updater_utils->does_column_type_value_exist('staff_help', 'checkout_authorID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE staff_help CHANGE COLUMN checkout_authorID checkout_authorID int(10) unsigned", true);
  }

  // 25/09/2012 - Enlarge the size of the integer for student_userID in textbox_marking table.
  if (!$updater_utils->does_column_type_value_exist('textbox_marking', 'paperID', 'int(10) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE textbox_marking CHANGE COLUMN student_userID student_userID int(10) unsigned", true);
  }

  // 25/09/2012 - Reduce size of the integer for paperID in textbox_marking table.
  if (!$updater_utils->does_column_type_value_exist('textbox_marking', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE textbox_marking CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  //27/09/2012 - remove concatenated moduleID form properties and crate the properties_module linking table
  if (!$updater_utils->does_table_exist('properties_modules')) {
    $sql = "CREATE TABLE properties_modules (property_id mediumint(8) unsigned, idMod int(11) unsigned, constraint pk_properties_module primary key (property_id, idMod)) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=$cfg_db_charset";
    $updater_utils->execute_query($sql, true);
    
    $res = $mysqli->prepare("SELECT id, moduleid FROM modules");
    $res->execute();
    $res->bind_result($id, $moduleid);
    $modules = array();
    while ($res->fetch()) {
      $modules[$moduleid] = $id;
    }
    $res->close();
    unset($res);
    $res = $mysqli->prepare("SELECT property_id, moduleID FROM properties");
    $res->execute();
    $res->store_result();
    $res->bind_result($property_id, $moduleID);
    $insert_res = $mysqli->prepare("INSERT INTO properties_modules VALUES (?, ?)");
    while ($res->fetch()) {
      $paper_modules = explode(',', $moduleID);
      foreach ($paper_modules as $m) {
        $insert_res->bind_param('ii', $property_id, $modules[$m]);
        $insert_res->execute();
      }
    }
    $insert_res->close();
    $res->close();

    $updater_utils->execute_query("ALTER TABLE properties DROP moduleid", true);

    //deal with questions q_group
    $sql = "CREATE TABLE questions_modules (q_id int(4) unsigned, idMod int(11) unsigned, constraint pk_questions_module primary key (q_id, idMod)) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=$cfg_db_charset";
    $updater_utils->execute_query($sql, true);

    $res = $mysqli->prepare("SELECT q_id, q_group FROM questions");
    $res->execute();
    $res->store_result();
    $res->bind_result($q_id, $moduleID);
    $insert_res = $mysqli->prepare("INSERT INTO questions_modules VALUES (?,?)");
    while ($res->fetch()) {
      $questions_modules = explode(',', $moduleID);
      foreach ($questions_modules as $m) {
        $insert_res->bind_param('ii', $q_id, $modules[$m]);
        $insert_res->execute();
      }
    }
    $insert_res->close();
    $res->close();

    $updater_utils->execute_query("ALTER TABLE questions DROP q_group", true);

    //'folders' => 'team_name' is not 1 to 1 so need a folders_modules_staff joining table
    $sql = "CREATE TABLE folders_modules_staff (folders_id int unsigned, idMod int(11) unsigned, constraint pk_properties_module primary key (folders_id, idMod)) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=$cfg_db_charset";
    $updater_utils->execute_query($sql, true);
    
    unset($res);
    $res = $mysqli->prepare("SELECT id, team_name FROM folders");
    $res->execute();
    $res->store_result();
    $res->bind_result($folder_id, $team_name);
    $insert_res = $mysqli->prepare("INSERT INTO folders_modules_staff VALUES (?, ?)");
    while ($res->fetch()) {
      $folder_modules = explode(',', $moduleID);
      foreach ($folder_modules as $m) {
        $insert_res->bind_param('ii', $folder_id, $modules[$m]);
        $insert_res->execute();
      }
    }
    $insert_res->close();
    $res->close();

    $updater_utils->execute_query("ALTER TABLE folders DROP team_name", true);
    
    //translate moduleID to idMod in all tables
    $updater_utils->execute_query("ALTER TABLE sessions DROP PRIMARY KEY", false);
    $tables = array('objectives ' => 'moduleID', 'relationships' => 'module_id', 'sessions' => 'moduleID', 'sms_imports' => 'moduleid', 'student_modules' => 'moduleid', 'teams' => 'name');
    foreach ($tables as $table => $col) {
      foreach ($modules as $code => $id) {
        $updater_utils->execute_query("UPDATE $table SET $col = $id WHERE $col = '$code'", false);
      }
    }
    //rename and rename and retype the columns
    $tables['reference_modules'] = 'moduleID'; //this just needs renaming
    $tables['users_metadata'] = 'moduleID'; //this just needs renaming
    foreach ($tables as $table => $col) {
      $updater_utils->execute_query("ALTER TABLE $table CHANGE $col idMod INT(11) unsigned DEFAULT NULL", true);
    }
    //rename teams and student_modues
    $updater_utils->execute_query('RENAME TABLE teams TO modules_staff, student_modules TO modules_student', true);
    $updater_utils->execute_query("ALTER TABLE sessions ADD PRIMARY KEY(identifier, idMod, calendar_year)", true);
  }

  // 02/11/2012 - Add new field to special_needs table.
  if (!$updater_utils->does_column_exist('special_needs', 'unanswered')) {
    $updater_utils->execute_query("ALTER TABLE special_needs ADD COLUMN unanswered varchar(20)", true);
  }

  //cczsa11 07/11/2012 -- Add new fields to sys_error table.
  if (!$updater_utils->does_column_exist('sys_errors', 'variables')) {
    $updater_utils->execute_query("ALTER TABLE sys_errors ADD COLUMN variables LONGTEXT, ADD COLUMN backtrace LONGTEXT", true);
  }

  //brzsw 14/11/2012 - Add new grants for staff users needing to add modules to questions.
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'questions_modules', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".questions_modules TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //brzsw 14/11/2012 - Add new grants for staff users needing to add modules to papers.
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'papers_modules', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".papers_modules TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //brzsw 14/11/2012 - Add new grants for staff users needing to add modules to papers.
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'modules_staff', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".modules_staff TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //brzsw 14/11/2012 - Add new grants for staff users needing to add modules to papers.
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'folders_modules_staff', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".folders_modules_staff TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //brzsw 14/11/2012 - Add new grants for staff users needing to add modules to papers.
  if (!$updater_utils->has_grant($cfg_db_student_user, 'SELECT', 'properties_modules', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".properties_modules TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //brzsw 14/11/2012 - Add new grants for staff users needing to add modules to papers.
  if (!$updater_utils->has_grant($cfg_db_student_user, 'SELECT, INSERT', 'modules_student', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".modules_student TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //brzab3 14/11/2012 - Add new grants for student users needing select from schools.
  if (!$updater_utils->has_grant($cfg_db_student_user, 'SELECT', 'schools', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".schools TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }
  
  //BP 22/11/2012 - Add new grants for invigilator users needing select from properties_modules
  if (!$updater_utils->has_grant($cfg_db_inv_username, 'SELECT', 'properties_modules', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".properties_modules TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //brzsw 22/11/2012 - Add new grants for invigilator users needing select from properties_modules
  if (!$updater_utils->has_grant($cfg_db_inv_username, 'SELECT', 'modules_student', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".modules_student TO '" . $cfg_db_inv_username . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }
  
  //brzsw 02/01/2013 - Add new grants for staff users needing change properties_modules
  if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'properties_modules', $cfg_db_host)) {
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".properties_modules TO '" . $cfg_db_staff_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }
  
  //brzsw 04/01/2013 - Add new grants for external examiners
  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'modules', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".modules TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 30/11/2012
  // Adding a new table log_start_time
  if (!$updater_utils->does_table_exist('log_start_time')) {
    $sql = 'CREATE TABLE
                 log_start_time(   id           int            PRIMARY KEY NOT NULL AUTO_INCREMENT
                                 , userID       int            unsigned NOT NULL
                                 , paperID      mediumint      unsigned NOT NULL
                                 , start_time   datetime       NOT NULL
                                 , CONSTRAINT   key_user_paper UNIQUE (userID, paperID )
                             ) ENGINE=InnoDB DEFAULT CHARSET=' . $cfg_db_charset . ' PACK_KEYS=1 AUTO_INCREMENT=1;';
    $updater_utils->execute_query($sql, true);
  }

  $does_column_exist = $updater_utils->does_column_exist('log_metadata', 'completed');
  if ($does_column_exist === FALSE) {
    $sql = "ALTER TABLE log_metadata ADD completed DATETIME NULL";
    $updater_utils->execute_query($sql, true);
  }

  //cczsa1 13/12/2012 - Convert authentication in config file to  new format
  $cfg = file($cfg_web_root . 'config/config.inc.php');

  $addauth = TRUE;
  foreach ($cfg as $k => $v) {
    $found = strpos($v, '$authentication = array(');
    if ($found !== FALSE) {
      $addauth = FALSE;
    }
    $found = strpos($v, '$cfg_encrypt_salt');
    if ($found !== FALSE) {
      $saltloc = $k + 1;
    }
  }

  if ($addauth == TRUE) {
    $extra1 = '';
    $array_new[] = "\n";
    $array_new[] = '$authentication = array(' . "\n";
    if ($cfg_use_ldap === TRUE) {
      $extra1 = ',';
    }
    $array_new[] = "array('internaldb', array('table' => 'users', 'username_col' => 'username', 'passwd_col' => 'password', 'id_col' => 'id', 'encrypt' => 'SHA-512', 'encrypt_salt' => \$cfg_encrypt_salt), 'Internal Database')$extra1\n";
    if ($cfg_use_ldap === TRUE) {
      $array_new[] = "array('ldap',array( 'table' => 'users', 'username_col' => 'username', 'id_col' => 'id', 'ldap_server' => \$cfg_ldap_server, 'ldap_search_dn' => \$cfg_ldap_search_dn, 'ldap_bind_rdn' => \$cfg_ldap_bind_rdn, 'ldap_bind_password' => \$cfg_ldap_bind_password, 'ldap_user_prefix' => \$cfg_ldap_user_prefix),'LDAP')\n";
    }

    $array_new[] = ");\n";
    array_splice($cfg, $saltloc, 0, $array_new);

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.preauthchange.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    } else {
      echo"<li>Changed config file to new authentication method</li>";
    }
  }

  //2012/12/14 cczsa1 add permission for db user to access properties & ip_addresses tables for guestlogin authentication module.
  if (!$updater_utils->has_grant($cfg_db_username, 'SELECT', 'properties', $cfg_db_host)) {
    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.properties TO \'' . $cfg_db_username . '\'@\'' . $cfg_db_host . '\'';
    $updater_utils->execute_query($sql, true);
  }
  
  if (!$updater_utils->has_grant($cfg_db_username, 'SELECT', 'ip_addresses', $cfg_db_host)) {
    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.ip_addresses TO \'' . $cfg_db_username . '\'@\'' . $cfg_db_host . '\'';
    $updater_utils->execute_query($sql, true);
  }

  //2012/12/14 bparish - Add new table to support a timer for summative exams
  if (!$updater_utils->does_table_exist('log_lab_end_time')) {
    $sql = 'CREATE TABLE
                 log_lab_end_time(   id            int unsigned PRIMARY KEY NOT NULL AUTO_INCREMENT
                                   , labID         smallint unsigned NOT NULL
                                   , paperID       mediumint unsigned NOT NULL
                                   , invigilatorID int unsigned NOT NULL
                                   , end_time      int unsigned NOT NULL
                                   , CONSTRAINT    key_lab_paper_invig_time UNIQUE ( labID, paperID, invigilatorID, end_time )
                                 ) ENGINE=InnoDB DEFAULT CHARSET=' . $cfg_db_charset . ' PACK_KEYS=1 AUTO_INCREMENT=1;';
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.log_lab_end_time TO \'' . $cfg_db_inv_username . '\'@\'' . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.log_lab_end_time TO \'' . $cfg_db_student_user . '\'@\'' . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  //18/12/2012 brzsw - Add new table to support a timer for summative exams
  if (!$updater_utils->has_grant($cfg_db_student_user, 'SELECT', 'keywords_question', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".keywords_question TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }
  
  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'keywords_question', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".keywords_question TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 19/12/2012 - Enlarge the size of the address field.
  if (!$updater_utils->does_column_type_value_exist('ip_addresses', 'address', char(60))) {
    $updater_utils->execute_query("ALTER TABLE ip_addresses CHANGE COLUMN address address char(60)", true);
  }

  // 20/12/2012 - Add new line to configuration file
  $new_cfg_str = array();
  $new_cfg_str[] = "  \$cfg_autosave_settimeout = 10;\r\n";
  $new_cfg_str[] = "  \$cfg_autosave_frequency  = 180;\r\n";
  $new_cfg_str[] = "  \$cfg_autosave_retrylimit = 3;\r\n";
  $new_cfg_str[] = "\r\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $cfg_new = array();
  $found = FALSE;
  foreach ($cfg as $curline => $line) {

    if (strpos($line, 'cfg_autosave_settimeout') !== FALSE) {
      $found = TRUE;
    }
    if (strpos($line, 'cfg_autosave_timeout') !== FALSE or strpos($line, 'cfg_autosave_frequency') !== FALSE) {
      $target_line = $curline + 1;
    }
    $cfg_new[] = $line;
  }

  if (!$found) {
    //add the new config chunk
    array_splice($cfg_new, $target_line, 0, $new_cfg_str);

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old13.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Add cfg_autosave_settimeout config variable</li>\n";
  }

  // 20/12/2012 - Remove line from configuration file
  $cfg_new = array();
  $cfg = file($cfg_web_root . 'config/config.inc.php');
  foreach ($cfg as $curline => $line) {
    if (strpos($line, 'cfg_autosave_timeout') === FALSE) {
      $cfg_new[] = $line;
    }
  }
  if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === FALSE) {
    echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
  }

  // 20/12/2012 - Add new line to configuration file
  $new_cfg_str = array();
  $new_cfg_str[] = "\$cfg_client_lookup = 'ipaddress';\r\n";
  $new_cfg_str[] = "\r\n";

  $cfg = file($cfg_web_root . 'config/config.inc.php');
  $cfg_new = array();
  $found = false;
  $target_line = 26;
  foreach ($cfg as $curline=>$line) {

    if (strpos($line,'cfg_client_lookup') !== false) {
      $found = true;
    }
    if (strpos($line,'cfg_summative_mgmt') !== false) {
      $target_line = $curline + 1;
    }
    $cfg_new[] = $line;
  }

  if (!$found) {
    //add the new config chunk
    array_splice($cfg_new,$target_line,0,$new_cfg_str);

    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old13.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Add cfg_client_lookup config variable</li>\n";
  }

  //2012/12/18 bparish - Add new table to enable a students time to be extended when taking summative exams
  if (!$updater_utils->does_table_exist('log_extra_time')) {
    $sql    = 'CREATE TABLE
                     log_extra_time( id            int unsigned PRIMARY KEY NOT NULL AUTO_INCREMENT
                                   , labID         smallint unsigned NOT NULL
                                   , paperID       mediumint unsigned NOT NULL
                                   , invigilatorID int unsigned NOT NULL
                                   , userID        int unsigned NOT NULL
                                   , extra_time    int unsigned NOT NULL
                                   , end_date      int unsigned NOT NULL
                                   , CONSTRAINT    key_lab_id_paper_id_user_id UNIQUE ( labID, paperID, userID )
                                 ) ENGINE=InnoDB DEFAULT CHARSET=' . $cfg_db_charset . ' PACK_KEYS=1 AUTO_INCREMENT=1;';
    $updater_utils->execute_query($sql, false);

    $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.log_extra_time TO \'' . $cfg_db_inv_username . '\'@\''. $cfg_db_host . "'";
    $updater_utils->execute_query($sql, false);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.log_extra_time TO \'' . $cfg_db_student_user . '\'@\''. $cfg_db_host . "'";
    $updater_utils->execute_query($sql, false);
  }

  // 09/01/2013 - Create new 'paper_feedback' table to hold feedback to be display after an assessment is completed.
  if (!$updater_utils->does_table_exist('paper_feedback')) {
    $updater_utils->execute_query("CREATE TABLE paper_feedback (id int not null primary key auto_increment, paperID mediumint unsigned NOT NULL, boundary tinyint unsigned NOT NULL, msg text) ENGINE=InnoDB DEFAULT CHARSET=$cfg_db_charset PACK_KEYS=1 AUTO_INCREMENT=1", true);

    if (!$updater_utils->has_grant($cfg_db_staff_user, 'SELECT, INSERT, UPDATE, DELETE', 'paper_feedback', $cfg_db_host)) {
      $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.paper_feedback TO \'' . $cfg_db_staff_user . '\'@\''. $cfg_db_host . "'";
      $updater_utils->execute_query($sql, false);
    }
    
    if (!$updater_utils->has_grant($cfg_db_student_user, 'SELECT', 'paper_feedback', $cfg_db_host)) {
      $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.paper_feedback TO \'' . $cfg_db_student_user . '\'@\''. $cfg_db_host . "'";
      $updater_utils->execute_query($sql, false);
    }
  }

  // 11/01/2013 - Create new 'lab_name' field in log_metadata table.
  if (!$updater_utils->does_column_type_value_exist('log_metadata', 'lab_name', 'varchar(255)')) {
    $updater_utils->execute_query("ALTER TABLE log_metadata ADD COLUMN lab_name varchar(255)", true);
    
    // Populate existing records.
    $lab_lookup = array();
    $result2 = $mysqli->prepare("SELECT address, name FROM (ip_addresses, labs) WHERE ip_addresses.lab = labs.id");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($address, $lab_name);
    while ($result2->fetch()) {
      $lab_lookup[$address] = $lab_name;
    }
    $result2->close();
    
    if (strpos(strtolower($_SERVER['HTTP_HOST']), 'nottingham.ac.uk') !== FALSE) {  // Backwards compatibility at UoN
      $sql = "UPDATE log_metadata SET lab_name = 'Pope - A24' WHERE ipaddress IN ('128.243.137.9','128.243.137.10','128.243.137.161','128.243.137.23','128.243.137.24','128.243.137.25','128.243.137.26','128.243.137.28','128.243.137.29','128.243.137.32','128.243.137.34','128.243.137.81','128.243.137.131','128.243.137.143','128.243.137.144','128.243.137.146','128.243.137.148','128.243.137.149','128.243.137.151','128.243.137.153','128.243.137.154','128.243.137.156','128.243.137.157','128.243.137.158','128.243.137.160','128.243.137.159','128.243.137.155','128.243.137.152','128.243.137.16','128.243.137.147','128.243.137.145','128.243.137.142','128.243.137.141','128.243.137.76','128.243.137.33','128.243.137.31','128.243.137.30','128.243.137.27','128.243.137.18','128.243.137.11')";
      $updater_utils->execute_query($sql, false);      
      $sql = "UPDATE log_metadata SET lab_name = 'Pope - A15' WHERE ipaddress IN ('128.243.137.5','128.243.137.13','128.243.137.14','128.243.137.15','128.243.137.17','128.243.137.19','128.243.137.21','128.243.137.22','128.243.137.63','128.243.137.67','128.243.137.86','128.243.137.88','128.243.137.96','128.243.137.97','128.243.137.104','128.243.137.107','128.243.137.108','128.243.137.110','128.243.137.111','128.243.137.112','128.243.137.114','128.243.137.115','128.243.137.117','128.243.137.118','128.243.137.119','128.243.137.120','128.243.137.123','128.243.137.124','128.243.137.125','128.243.137.126','128.243.137.129','128.243.137.130','128.243.137.133','128.243.137.135','128.243.137.140','128.243.137.150','128.243.137.163','128.243.137.165','128.243.137.166','128.243.137.167','128.243.137.168','128.243.137.169','128.243.137.170','128.243.137.171','128.243.137.172','128.243.137.173','128.243.137.174','128.243.137.175','128.243.137.176','128.243.137.178','128.243.137.179','128.243.137.177','128.243.137.180','128.243.137.186','128.243.137.190','128.243.137.194','128.243.137.202','128.243.137.205','128.243.137.207','128.243.137.208','128.243.137.209')";
      $updater_utils->execute_query($sql, false);
    }
    
    // Look up log2 records and populate.
    $result2 = $mysqli->prepare("SELECT DISTINCT ipaddress FROM log_metadata");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($ipaddress);
    while ($result2->fetch()) {
      if (isset($lab_lookup[$ipaddress])) {
        $labs_name = $lab_lookup[$ipaddress];
        
        $updater_utils->execute_query("UPDATE log_metadata SET lab_name = \"" . $labs_name . "\" WHERE ipaddress = '$ipaddress'", true);
      }      
    }
    $result2->close();
  }

  // 21/01/2013
  if (!$updater_utils->does_column_type_value_exist('password_tokens', 'user_id', 'int(11) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE password_tokens CHANGE COLUMN user_id user_id int(11) unsigned", true);
  }

  // 21/01/2013
  /*
  $data_type = '';
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='ownerID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($data_type);
  $result->fetch();
  if ($data_type != 'int(11) unsigned') {
    $updater_utils->execute_query("ALTER TABLE questions CHANGE COLUMN ownerID ownerID int(11) unsigned", true);
  }
  $result->close();
  */

  // 21/01/2013
  if (!$updater_utils->does_column_type_value_exist('scheduling', 'paperID', 'mediumint(8) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE scheduling CHANGE COLUMN paperID paperID mediumint(8) unsigned", true);
  }

  // 21/01/2013
  if (!$updater_utils->does_column_type_value_exist('sys_errors', 'userID', 'int(11) unsigned')) {
    $updater_utils->execute_query("ALTER TABLE sys_errors CHANGE COLUMN userID userID int(11) unsigned", true);
  }
  
  //brzsw 25/01/2013 - Remove grants no longer needed (tables have been renamed).
  $sql_cmd = array();
  $sql_cmd[] = "REVOKE ALL PRIVILEGES ON student_modules FROM '" . $cfg_db_database . "_stu'@'" . $cfg_db_host . "'";
  $sql_cmd[] = "REVOKE ALL PRIVILEGES ON teams FROM '" . $cfg_db_database . "_staff'@'" . $cfg_db_host . "'";
  $sql_cmd[] = "REVOKE ALL PRIVILEGES ON student_modules FROM '" . $cfg_db_database . "_staff'@'" . $cfg_db_host . "'";
  $sql_cmd[] = "REVOKE ALL PRIVILEGES ON teams FROM '" . $cfg_db_database . "_ext'@'" . $cfg_db_host . "'";
  $sql_cmd[] = "REVOKE ALL PRIVILEGES ON student_modules FROM '" . $cfg_db_database . "_inv'@'" . $cfg_db_host . "'";
  foreach($sql_cmd as $sql) {
    $updater_utils->execute_query($sql, true);
  }
  
  //brzsw 25/01/2013 - Add missing indexes.
  if (!$updater_utils->does_index_exist('log4', 'q_paper')) {
    $updater_utils->execute_query("ALTER TABLE log4 ADD INDEX q_paper (q_paper)", true);
  }
  
  if (!$updater_utils->does_index_exist('log4', 'username')) {
    $updater_utils->execute_query("ALTER TABLE log4 ADD INDEX username (userID)", true);
  }

  if (!$updater_utils->does_index_exist('log4', 'started')) {
    $updater_utils->execute_query("ALTER TABLE log4 ADD INDEX started (started)", true);
  }

  if (!$updater_utils->does_index_exist('log4_overall', 'q_paper')) {
    $updater_utils->execute_query("ALTER TABLE log4_overall ADD INDEX q_paper (q_paper)", true);
  }
  
  if (!$updater_utils->does_index_exist('log4_overall', 'username')) {
    $updater_utils->execute_query("ALTER TABLE log4_overall ADD INDEX username (userID)", true);
  }

  if (!$updater_utils->does_index_exist('log4_overall', 'started')) {
    $updater_utils->execute_query("ALTER TABLE log4_overall ADD INDEX started (started)", true);
  }

  if (!$updater_utils->does_index_exist('log5', 'q_paper')) {
    $updater_utils->execute_query("ALTER TABLE log5 ADD INDEX q_paper (q_paper)", true);
  }
  
  if (!$updater_utils->does_index_exist('log5', 'username')) {
    $updater_utils->execute_query("ALTER TABLE log5 ADD INDEX username (userID)", true);
  }

  if (!$updater_utils->does_index_exist('log5', 'started')) {
    $updater_utils->execute_query("ALTER TABLE log5 ADD INDEX started (started)", true);
  }

  if (!$updater_utils->does_index_exist('log6', 'started')) {
    $updater_utils->execute_query("ALTER TABLE log6 ADD INDEX started (started)", true);
  }

  if (!$updater_utils->does_index_exist('lti_keys', 'oauth_consumer_key')) {
    $updater_utils->execute_query("ALTER TABLE lti_keys ADD INDEX oauth_consumer_key (oauth_consumer_key)", true);
  }

  if (!$updater_utils->does_index_exist('lti_user', 'rogo_id')) {
    $updater_utils->execute_query("ALTER TABLE lti_user ADD INDEX lti_user_equ (lti_user_equ)", true);
  }
  // End of updates -----------------------------------------------------------------

  // Final housekeeping activities - put all updates above this line
  $updater_utils->execute_query('FLUSH PRIVILEGES', true);
  $updater_utils->execute_query('TRUNCATE sys_errors', true);
  echo "</ol>\n";
  
  $mysqli->close();
  echo "\n<h2>" . $string['actionrequired'] . "</h2>\n<ol>";
  echo "\n<li>" . $string['readonly'] . "</li>\n";
  echo "</ol>\n<div>" . $string['finished'] . "</div>\n<div style=\"text-align:center\"><input type=\"button\" value=\" " . $string['home'] . " \" onclick=\"window.location('/staff/')\" /></div><blockquote>\n";
}
?>
