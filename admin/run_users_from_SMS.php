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
* Script to obtain module enrolements from Student Management System (SMS).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

set_time_limit(0);

require '../include/sysadmin_auth.inc';

if ($configObject->get('cfg_sms_api') == '') {
  log_error(0, 'CRON JOB', 'Application Error', "'cfg_sms_api' setting in config.inc.php is set to blank.", 'users_from_SMS.php', 0, '', null, null, null);
  exit();
}
$sms_connection = SmsUtils::GetSmsUtils();

// Calculate what the current academic session is.
$yearutils = new yearutils($mysqli);
$session = (isset($_GET['session']) and $_GET['session'] != '') ? $_GET['session'] : $yearutils->get_current_session();
$academic_year = $yearutils->get_academic_session($session);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Rog&#333; <?php echo $configObject->get('cfg_install_type'); ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css" />
    <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
    <link rel="stylesheet" type="text/css" href="../css/list.css" />
    
    <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="../js/staff_help.js"></script>
    <script type="text/javascript" src="../js/toprightmenu.js"></script>
    <script>
      $(function () {
        if ($("#maindata").find("tr").size() > 1) {
          $("#maindata").tablesorter({ 
            sortList: [[1,0]] 
          });
        }
      });
    </script>
  </head>
  <body>
<?php
require '../include/toprightmenu.inc';
require '../include/admin_module_options.inc';
	
echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="list_modules.php"><?php echo $string['modules'] ?></a></div>
  <div class="page_title"><?php echo $string['runningsmsimport'] ?></div>
</div>
  
<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
  <thead>
    <tr><th class="col"><?php echo $string['academicyear'] ?></th><th class="col"><?php echo $string['modulecode'] ?></th><th class="col"><?php echo $string['url'] ?></th><th class="col"><?php echo $string['enrolements'] ?></th><th class="col"><?php echo $string['deletions'] ?></th></tr>
  </thead>
  
<?php
// Only include sms integration modules.
// Do not include deleted modules or non-active modules.
$sms_url = $configObject->get_setting('core', 'cfg_sms_url') . '%';
$module_data = $mysqli->prepare("SELECT modules.id, moduleid, sms FROM modules WHERE sms LIKE ? AND mod_deleted IS NULL AND active = 1 ORDER BY moduleid");
$module_data->bind_param('s', $sms_url);
$module_data->execute();
$module_data->store_result();
$module_data->bind_result($idMod, $module, $sms);
while ($module_data->fetch()) {
  $sms_connection->update_module_enrolement($module, $idMod, $sms, $mysqli, $session);  
  echo "<tr class=\"l\"><td>$academic_year</td><td>$module</td><td>$sms</td><td>" . $sms_connection->get_enrolement_no($module) . "</td><td>" . $sms_connection->get_deletion_no($module) . "</td></tr>\n";
}
$module_data->close();

$errorinfo = $sms_connection->geterrors();

if (count($errorinfo['usernamematch']) > 0) {
  log_error(0, 'CRON JOB', 'Application Warning', implode('\r\n', $errorinfo['usernamematch']), 'users_from_SMS.php', 0, '', null, $errorinfo['usernamematchdata'], null);
}

if (count($errorinfo['unabletodetermineusername']) > 0) {
  log_error(0, 'CRON JOB', 'Application Warning', implode('\r\n', $errorinfo['unabletodetermineusername']), 'users_from_SMS.php', 0, '', null, $errorinfo['unabletodetermineusernamedata'], null);
}

$errorstr = '';
if (count($errorinfo['moduleerrorstate']) > 0) {
  foreach ($errorinfo['moduleerrorstate'] as $key => $value) {
    $cnt= count($value);
    $errorstr .= 'Error state: ' . $key . " <br />\r\n$cnt module(s):: ";
    foreach ($value as $value2) {
      $errorstr .= $value2 . ", ";
    }
    $errorstr .= "<br />\r\n";
  }
  log_error(0, 'CRON JOB', 'Application Warning', $errorstr, 'users_from_SMS.php', 0, '', null, $errorinfo['moduleerrorstatedata'], null);
}

$errorstr = '';
if (count($errorinfo['modulenodata']) > 0) {
  $errorstr .=  "The following " .count($errorinfo['modulenodata']) . " modules returned no data: <br />\r\n";
  foreach ($errorinfo['modulenodata'] as $key => $value) {
    $errorstr .= "$value, ";
  }
  log_error(0, 'CRON JOB', 'Application Warning', $errorstr, 'users_from_SMS.php', 0, '', null, $errorinfo['modulenodatadata'], null);
}

$mysqli->close();
?>
</table>

<br />
<div>Complete.</div>

</div>
</body>
</html>