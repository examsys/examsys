<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * This screen presents a list of students assigned to a particular cohort.
 * You click on the student name of interest and the OSCE station marking
 * form comes up.
 *
 * @author Naseem Sarwar
 * @version 1.0
 * @copyright Copyright (c) 2017 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$id = check_var('id', 'GET', true, false, true);
$initial = check_var('initial', 'GET', true, false, true);

$properties = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

$paperID        = $properties->get_property_id();
$paper_title    = $properties->get_paper_title();
$calendar_year  = $properties->get_calendar_year();
$modules        = $properties->get_modules();

$student_no = 0;
$old_letter = '';
$user_list  = array();
$result = $mysqli->prepare('SELECT users.id, surname, first_names, title, student_id, started FROM (modules_student, users, sid) LEFT JOIN log4_overall ON users.id = log4_overall.userID AND q_paper = ? WHERE modules_student.userID = users.id AND users.id = sid.userID AND modules_student.idMod IN (' . implode(',', array_keys($modules)) . ') AND calendar_year = ? and initials = ? ORDER BY surname, initials');
$result->bind_param('iss', $paperID, $calendar_year, $initial);
$result->execute();
$result->store_result();
$result->bind_result($tmp_userID, $surname, $first_names, $title, $student_id, $started);

while ($result->fetch()) {
    $user_list[] = array($tmp_userID, $surname, $first_names, $title, $student_id, $started);
}
$mysqli->close();

echo json_encode($user_list);
