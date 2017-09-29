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
* This script is designed to compare marks between the Class Totals report and students' actual exam scripts (finish.php).
* It works by:
*   1. Get summative exam papers in the require date range.
*   2. For each paper call class_totals.php and parse for student IDs and marks.
*   3. For each student call finish.php and compare the mark.
*   4. Echo errors for any which do not match.
* 
* @author Simon Wilkinson and Joseph Baxter
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require dirname(__DIR__) . '/include/sysadmin_auth.inc';
include_once dirname(__DIR__) . '/include/load_config.php';
require_once 'classes/class_totals.php';

set_time_limit(0);
session_write_close();
$response = '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
ignore_user_abort(true);
header("Connection: close");
header("Content-Length: " . mb_strlen($response));
echo $response;
flush();

$end_dateSQL = 'NOW()';
if (isset($_POST['period']) and $_POST['period'] != '') {
  if ($_POST['period'] == 'day') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 DAY)';
  } elseif ($_POST['period'] == 'week') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 WEEK)';
  } elseif ($_POST['period'] == 'month') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 MONTH)';
  } elseif ($_POST['period'] == 'year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 YEAR)';
  } elseif ($_POST['period'] == '2year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 2 YEAR)';
  } elseif ($_POST['period'] == '3year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 3 YEAR)';
  } elseif ($_POST['period'] == '6year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 6 YEAR)';
  }
} else {
  $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 5 YEAR)';
}

if (!isset($protocol)) {
  $protocol = 'https://';
}

if (isset($_POST['server']) and $_POST['server'] != '') {
  $server = $_POST['server'];
} else {
  $server = $protocol . $_SERVER['SERVER_ADDR'];
}

$userid = $userObject->get_user_ID();
$rootpath = $configObject->get('cfg_root_path');
$username = $userObject->get_username();
$password = $_POST['passwd'];

if (isset($_POST['paper']) and $_POST['paper'] != '') {
    $class_totals = new class_totals();
    $class_totals->process_papers($mysqli, $username, $password, $rootpath, $userid, $start_dateSQL, $end_dateSQL, $server, $string, $userObject, $_POST['paper']);
} else {
    $class_totals = new class_totals();
    $class_totals->process_papers($mysqli, $username, $password, $rootpath, $userid, $start_dateSQL, $end_dateSQL, $server, $string, $userObject);
}
?>
