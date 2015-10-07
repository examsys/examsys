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
 * Class for processing Student Management System (SMS) enrolments (including user and course, faculty and school creation)
 *
 * @author Anthony Brown
 * @author Barry Oosthuizen based on code from Eugene Venter
 * @copyright  2015 onwards, The University of Nottingham
 * @copyright  2010 Eugene Venter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Sms_Cron {

  /**
   * Process the configured SMS module enrolment
   * 
   * @global stdClass $configObject
   */
  public function process() {
    global $configObject;

    set_time_limit(0);

    $sms_connection = SmsUtils::GetSmsUtils();
    $mysqli = $this->get_db_object();
   
    $userObject = new UserObject($configObject, $mysqli);
    $yearutils = new yearutils($mysqli);

    if (!$sms_connection->createModules()) {
      $this->update_rogo_modules($mysqli, $sms_connection, $yearutils);
    } else {
      $sms_connection->process_sms_modules($mysqli);
    }

    $this->log_errors($sms_connection);
    $mysqli->close();
  }

  public function get_db_object() {
    global $configObject, $notice;
    $mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_sysadmin_user'),
        $configObject->get('cfg_db_sysadmin_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'),
        $notice, $configObject->get('dbclass'));
    return $mysqli;
  }

  public function update_rogo_modules($mysqli, $sms_connection, $yearutils) {
    // Do not include deleted modules or non-active modules.
    $module_data = $mysqli->prepare("SELECT modules.id, moduleid, sms, academic_year_start "
        . "FROM modules "
        . "WHERE sms != '' AND mod_deleted IS NULL AND active = 1 "
        . "ORDER BY moduleid");
    $module_data->execute();
    $module_data->store_result();
    $module_data->bind_result($idMod, $module, $sms, $academic_year_start);
    while ($module_data->fetch()) {
      $session = $yearutils->get_current_session($academic_year_start);
      $sms_connection->update_module_enrolement($module, $idMod, $sms, $mysqli, $session);
    }
    $module_data->close();
  }

  public function log_errors($sms_connection) {

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
        $cnt = count($value);
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
      $errorstr .= "The following " . count($errorinfo['modulenodata']) . " modules returned no data: <br />\r\n";
      foreach ($errorinfo['modulenodata'] as $key => $value) {
        $errorstr .= "$value, ";
      }
      log_error(0, 'CRON JOB', 'Application Warning', $errorstr, 'users_from_SMS.php', 0, '', null, $errorinfo['modulenodatadata'], null);
    }
  }

}
