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

$version = '5.1';

set_time_limit(0);

$old_version = $configObject->get('rogo_version');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>Rog&#333; <?php echo $configObject->get('rogo_version') . ' to ' . $version; ?> update Script</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <link rel="stylesheet" type="text/css" href="../css/updater.css"/>

    <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  </head>
  <body>
  <table class="header">
    <tr>
      <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
          <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0" style="float:left; padding-right:8px"/>

          <div style="color:#1F497D; font-size:28pt; font-weight:bold">Rog&#333;</div>
          <div style="color:#1F497D; font-size:9pt">Update Utility (<?php echo $old_version . ' to ' . $version; ?>)</div>
      </th>
      <th style="text-align:right; padding-right:10px"><img src="../artwork/software_64.png" width="64" height="64" alt="Upgrade Icon" border="0" /></th>
    </tr>
    <tr>
      <th colspan="2" class="bevel"></th>
    </tr>
  </table>
<?php
if (round($old_version,0) < 5) {
  echo "<p style=\"margin-left:10px\">Rog&#333; $old_version is installed.<br /><br />Please use <strong><a href=\"/updates/version4.php\">/updates/version4.php</a></strong> before running /updates/version5.php</p>";
  exit;
}
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
    <div><?php echo $string['warning2']; ?></div>
    <?php
  } elseif (!InstallUtils::configPathIsWriteable()) {
    ?>
    <h2><?php echo $string['updatefromversion'] . ' ' . $configObject->get('rogo_version') . ' to ' . $version; ?></h2>
    <div><?php echo $string['warning3']; ?></div>
    <div><?php echo $string['warning4']; ?></div>
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

      <div><label for="mysql_admin_user"><?php echo $string['dbusername']; ?></label> <input type="text" value="" name="mysql_admin_user" class="required" minlength="2" /></div>
      <div><label for="mysql_admin_pass"><?php echo $string['dbpassword']; ?></label> <input type="password" value="" name="mysql_admin_pass"/>
      </div>

      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['onlinehelpsystems']; ?></nobr>
              </td>
              <td class="line">
                  <hr />
              </td>
          </tr>
      </table>
      <div><label for="update_staff_help"><?php echo $string['updatestaffhelp']; ?></label> <input type="checkbox" value="" name="update_staff_help" checked="checked" /></div>
      <div><label for="update_student_help"><?php echo $string['updatestudenthelp']; ?></label> <input type="checkbox" value="" name="update_student_help" checked="checked" /></div>

      <div class="submit"><input type="submit" name="update" value="<?php echo $string['startupdate']; ?>"/></div>
  </form>
    <?php
  }
  ?>
   </body>
   </html>
  <?php

} else {
  if ($configObject->get('cfg_db_charset') == null) {
    $cfg_db_charset = 'latin1';
  } else {
    $cfg_db_charset = $configObject->get('cfg_db_charset');
  }

  $mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $_POST['mysql_admin_user'], $_POST['mysql_admin_pass'], $configObject->get('cfg_db_database'), $cfg_db_charset, $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

  if ($mysqli->connect_error) {
    echo "<div>Failded to contect to mysql using " . $_POST['mysql_admin_user'] . '' . $_POST['mysql_admin_pass'] . '</div>';
    echo "</body>";
    echo "</html>";
    exit;
  }
  $updater_utils = new UpdaterUtils($mysqli, $configObject->get('cfg_db_database'));

  // Backup the config file before proceeding.
  $updater_utils->backup_file($cfg_web_root, $old_version);
  

  // Avoid repeated method calls
  $cfg_db_database      = $configObject->get('cfg_db_database');
  $cfg_db_student_user  = $configObject->get('cfg_db_student_user');
  $cfg_db_staff_user    = $configObject->get('cfg_db_staff_user');
  $cfg_db_host          = $configObject->get('cfg_db_host');
  $cfg_db_username      = $configObject->get('cfg_db_username');
  $cfg_db_external_user = $configObject->get('cfg_db_external_user');
  $cfg_db_inv_username  = $configObject->get('cfg_db_inv_user');
  $cfg_use_ldap         = $configObject->get('cfg_use_ldap');

  error_reporting(-1);
  ob_start();

  echo "<div>Starting at " . date("H:i:s") . "</div>";

  echo "\n<blockquote>\n<h1>" . $string['startingupdate'] . "</h1>\n<ol>";

  $mysqli->autocommit(false);
  // 01/05/2013 - Update the online help files.
  if (isset($_POST['update_staff_help'])) {
    $updater_utils->execute_query("TRUNCATE staff_help", true);

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
  $mysqli->commit();
  
  // 01/05/2013
  if (!$updater_utils->does_column_exist('users', 'password_expire')) {
    $updater_utils->execute_query("ALTER TABLE users ADD COLUMN password_expire int(11) unsigned", true);
  }

  // 02/05/2013 - Add password expire config file.
  $new_lines = array("\$cfg_password_expire = 30;    // Set in days\n");
  $target_line = '$authentication = array';
  $updater_utils->add_line('$percent_decimals', $new_lines, 80, $cfg_web_root, $target_line, 7);
  
 
  // 08/05/2013 (uiznm) - Add permission for external examiners to see standards setting values
  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'standards_setting', $cfg_db_host)) {
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".standards_setting TO '" . $cfg_db_external_user . "'@'" . $cfg_db_host . "'";
    $updater_utils->execute_query($sql, true);
  }
  
  
  // 09/05/2013 (brzsw) - Remove $protocol and insert $cfg_secure_connection
  $lines  = array();
  $cfg    = file($cfg_web_root . 'config/config.inc.php');
  $found  = false;
  foreach ($cfg as $line) {
    if (strpos($line, '$protocol = ') !== false) {
      $lines[] = "\$cfg_secure_connection = true;    // If true site must be accessed via HTTPS\n";
      $found = true;
    } else {
      $lines[] = $line;
    }
  }

  if ($found) {
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $lines) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added \$cfg_secure_connection config file.</li>\n";
    ob_flush();
    flush();
  }
  
  // 15/05/2013 (brzsw) - Add in new variable to control number of decimals for percentages.
  $new_lines = array("//Reports\n", "  \$percent_decimals = 0;\n");
  $updater_utils->add_line('$percent_decimals', $new_lines, 60, $cfg_web_root);
 
 
 
  /*
   *****   NOW UPDATE THE INSTALLER SCRIPT   *****
   */

  // End of updates -----------------------------------------------------------------

  // Final housekeeping activities - put all updates above this line
  $updated = $updater_utils->update_version($version, $string, $cfg_web_root);
  if ($updated !== true) {
    echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
  }
  $updater_utils->execute_query('FLUSH PRIVILEGES', true);
  $updater_utils->execute_query('TRUNCATE sys_errors', true);
  echo "</ol>\n";

  $mysqli->close();
  echo "<div>Ended at " . date("H:i:s") . "</div>";
  echo "\n<h2>" . $string['actionrequired'] . "</h2>\n<ol>";
  echo "\n<li>" . $string['readonly'] . "</li>\n";
  echo "</ol>\n<div>" . $string['finished'] . "</div>\n<div style=\"text-align:center\"><input type=\"button\" value=\" " . $string['home'] . " \" onclick=\"" . $configObject->get('cfg_root_path') . "window.location('/staff/')\" /></div><blockquote>\n";
}
?>
