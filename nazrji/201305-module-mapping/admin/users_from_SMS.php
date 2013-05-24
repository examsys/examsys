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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

// Only run from the command line!
//if (PHP_SAPI != 'cli') {
//  die("Please run this test from CLI!\n");
//}

set_time_limit(0);

$path = str_replace('/admin', '', str_replace('\\', '/', dirname(__FILE__)));
if ($path == '') {
  $path = $_SERVER['DOCUMENT_ROOT'];
}
require_once $path . '/include/load_config.php';
require_once $path . '/classes/dateutils.class.php';
require_once $path . '/classes/dbutils.class.php';
require_once $path . '/classes/userutils.class.php';
require_once $path . '/classes/userobject.class.php';
require_once $path . '/include/auth.inc';
require_once $path . '/classes/smsutils.class.php';

$sms_connection = SmsUtils::GetSmsUtils();

//error_reporting(E_ALL);
//ini_set('display_errors',1);

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host') , $configObject->get('cfg_db_sysadmin_user'), $configObject->get('cfg_db_sysadmin_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));

$useObject = new UserObject($configObject, $mysqli);

// Calculate what the current academic session is.
$session = (isset($_GET['session']) and $_GET['session'] != '') ? $_GET['session'] : date_utils::get_current_academic_year();
$session_parts = explode('/', $session);


$module_data = $mysqli->prepare("SELECT modules.id, moduleid, sms FROM modules WHERE sms != '' ORDER BY moduleid");
$module_data->execute();
$module_data->store_result();
$module_data->bind_result($idMod, $module, $sms);
while ($module_data->fetch()) {
  $sms_connection->update_module_enrolement($module, $idMod, $sms, $mysqli, $session);  
}
$module_data->close();

$mysqli->close();
?>