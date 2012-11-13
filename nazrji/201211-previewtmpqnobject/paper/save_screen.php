<?php
// This file is part of Rogō
//
// Rog? is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rog? is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rog?.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* This script can only be called from start.php for ajax saving'.
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../include/staff_student_auth.inc';
require_once '../include/marking_functions.inc';
require_once '../include/errors.inc';
require_once '../include/paper_security.inc';
require_once '../classes/paperutils.class.php';

$displayDebug = false; //ajax call so debug info messes up the output

check_var('id', 'GET', true, false);

$stmt = $mysqli->prepare("SELECT property_id, paper_type, labs, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), calendar_year, password FROM properties WHERE crypt_name=? LIMIT 1");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->bind_result($property_id, $paper_type, $labs, $start_date, $end_date, $calendar_year, $password);
$stmt->fetch();
$stmt->close();

$attempt = 1; //default attempt to 1 overwritten if the student is resit candidate
$original_paper_type = $paper_type; //store the original paper type - needed to retrieve answers from the correct log and functionality related decisions

$moduleID = Paper_utils::get_modules($property_id,$mysqli);

if ($userObject->has_role('Student')) {

  // Check for additional password on the paper
  check_paper_password($password);

  // Check time security
  check_datetime($start_date, $end_date);

  //Check room security
  $low_bandwidth = check_labs($paper_type, $labs, $password, $mysqli);

  //get modules if the user is a student and the paper is not formative
  $attempt = check_modules($userObject->get_user_ID(), $moduleID, $calendar_year, $mysqli);

  // Check for any metadata security restrictions
  check_metadata($property_id, $userObject->get_user_ID(), $moduleID, $mysqli);

  if (time() > $end_date and ($paper_type == '1' or $paper_type == '2')) {
    $paper_type = '_late';
  }
}

//TODO we need to add some error checking in here. maby wrap this whole function in a transaction ??
$ret = record_marks($property_id, $mysqli, $userObject->get_user_ID(), $paper_type, $userObject->get_grade(), $userObject->get_year(), $attempt, $userObject->list_user_roles());
echo $_POST['randomPageID'];
?>