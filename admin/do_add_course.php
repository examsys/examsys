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
 * Add course
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$unique_course = true;
// Check for unique username
$tmp_course = param::required('course', param::TEXT, param::FETCH_POST);

if (CourseUtils::course_exists($tmp_course, $mysqli)) {
    $unique_course = false;
} else {
    $unique_course = true;
}

if ($unique_course == true) {
    $tmp_school = param::required('school', param::INT, param::FETCH_POST);
    $tmp_description = param::required('description', param::TEXT, param::FETCH_POST);
    $externalid = check_var('externalid', 'POST', false, false, true);
    $externalsys = check_var('externalsys', 'POST', false, false, true);
    if (!CourseUtils::add_course($tmp_school, $tmp_course, $tmp_description, $externalid, $externalsys, $mysqli)) {
        echo json_encode('ERROR');
        exit();
    }
    echo json_encode('SUCCESS');
} else {
    echo json_encode($tmp_course);
}
