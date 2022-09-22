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
 * Edit the faculty
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$facultyID = check_var('facultyID', 'REQUEST', true, false, true);
$faculty = check_var('new_faculty', 'POST', true, false, true);
$code = check_var('code', 'POST', false, false, true);
$details = FacultyUtils::get_faculty_details_by_id($facultyID, $mysqli);
if (!FacultyUtils::update_faculty($facultyID, $faculty, $code, $details['externalid'], $details['externalsys'], $mysqli)) {
    echo json_encode('ERROR');
} else {
    $logger = new Logger($mysqli);
    if ($details['name'] != $faculty) {
        $logger->track_change('Faculty', $facultyID, $userObject->get_user_ID(), $details['name'], $faculty, $string['name']);
    }
    if ($details['code'] != $code) {
        $logger->track_change('Faculty', $facultyID, $userObject->get_user_ID(), $details['code'], $code, $string['code']);
    }
    echo json_encode('SUCCESS');
}
