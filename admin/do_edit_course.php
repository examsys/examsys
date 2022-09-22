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
 * Edit the course
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$courseID = check_var('courseID', 'REQUEST', true, false, true);

if (!CourseUtils::courseid_exists($courseID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

$result = $mysqli->prepare('SELECT schoolid, name, description, externalid, externalsys FROM courses WHERE id = ?');
$result->bind_param('i', $courseID);
$result->execute();
$result->bind_result($current_school, $coursename, $description, $current_externalid, $current_externalsys);
$result->fetch();
$result->close();
$course_exists = false;

$tmp_course = param::required('course', param::TEXT, param::FETCH_POST);
$old_course = param::required('old_course', param::TEXT, param::FETCH_POST);

if ($tmp_course != $old_course) {
    // Check for unique course name
    $course_exists = CourseUtils::course_exists($tmp_course, $mysqli);
}

if ($course_exists == false) {
    $new_school = param::required('school', param::INT, param::FETCH_POST);
    $new_description = param::required('description', param::TEXT, param::FETCH_POST);

    if (CourseUtils::update_course($courseID, $new_school, $tmp_course, $new_description, $current_externalid, $current_externalsys, $mysqli)) {
        $logger = new Logger($mysqli);
        if ($coursename != $tmp_course) {
            $logger->track_change('Course', $courseID, $userObject->get_user_ID(), $coursename, $tmp_course, 'code');
        }
        if ($description != $new_description) {
            $logger->track_change('Course', $courseID, $userObject->get_user_ID(), $description, $new_description, 'name');
        }
        if ($current_school != $new_school) {
            $logger->track_change('Course', $courseID, $userObject->get_user_ID(), $current_school, $new_school, 'school');
        }
    } else {
        echo json_encode('ERROR');
        exit();
    }

    $mysqli->close();
    echo json_encode('SUCCESS');
} else {
    echo json_encode($tmp_course);
}
