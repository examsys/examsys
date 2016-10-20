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
* Script to obtain module enrolements from Student Management System (SMS). Run via a cron job.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

// Only run from the command line!
if (PHP_SAPI != 'cli') {
  die("Please run this test from CLI!\n");
}

set_time_limit(0);

$path = str_replace('/admin', '', str_replace('\\', '/', dirname(__FILE__)));
if ($path == '') {
  $path = $_SERVER['DOCUMENT_ROOT'];
}
require_once $path . '/include/load_config.php';
require_once $path . '/include/auth.inc';
require_once $path . '/include/custom_error_handler.inc';

if ($configObject->get('cfg_sms_api') == '') {
  log_error(0, 'CRON JOB', 'Application Error', "'cfg_sms_api' setting in config.inc.php is set to blank.", 'users_from_SMS.php', 0, '', null, null, null);
  exit();
}
$sms_connection = SmsUtils::GetSmsUtils();

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host') , $configObject->get('cfg_db_sysadmin_user'), $configObject->get('cfg_db_sysadmin_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));

$useObject = new UserObject($configObject, $mysqli);
$configObject->set_db_object($mysqli);
$sms_url = $configObject->get_setting('core', 'cfg_sms_url') . '%';
// Only include sms integration modules.
// Do not include deleted modules or non-active modules.
$module_data = $mysqli->prepare("SELECT modules.id, moduleid, sms, academic_year_start FROM modules WHERE sms LIKE ? AND mod_deleted IS NULL AND active = 1 ORDER BY moduleid");
$module_data->bind_param('s', $sms_url);
$module_data->execute();
$module_data->store_result();
$module_data->bind_result($idMod, $module, $sms, $academic_year_start);
$yearutils = new yearutils($mysqli);
while ($module_data->fetch()) {

  $session = $yearutils->get_current_session($academic_year_start);

  $sms_connection->update_module_enrolement($module, $idMod, $sms, $mysqli, $session);  
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
