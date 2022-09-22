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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$schoolid = check_var('schoolid', 'GET', true, false, true);

$result = $mysqli->prepare('SELECT school, facultyID, code, externalid, externalsys FROM schools WHERE id = ? AND deleted IS NULL');
$result->bind_param('i', $schoolid);
$result->execute();
$result->store_result();
$result->bind_result($school, $curr_faculty, $curr_code, $curr_externalid, $curr_externalsys);
$result->fetch();
if ($result->num_rows == 0) {
    $result->close();
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$result->close();


$school_tmp = check_var('school', 'POST', true, false, true);
$faculty = check_var('faculty', 'POST', true, false, true);
$code = check_var('code', 'POST', false, false, true);
$duplicate = false;
$changed = ($curr_faculty != $faculty or $school != $school_tmp or $curr_code != $code);
if (!SchoolUtils::update_school($schoolid, $faculty, $school_tmp, $code, $curr_externalid, $curr_externalsys, $mysqli)) {
    $duplicate = true;
}

if ($changed and $duplicate) {
    echo json_encode($school_tmp);
} else {
    if ($changed) {
        $logger = new Logger($mysqli);
        if ($school != $school_tmp) {
            $logger->track_change('School', $schoolid, $userObject->get_user_ID(), $school, $school_tmp, $string['name']);
        }
        if ($curr_faculty != $faculty) {
            $logger->track_change('School', $schoolid, $userObject->get_user_ID(), $curr_faculty, $faculty, $string['faculty']);
        }
        if ($curr_code != $code) {
            $logger->track_change('School', $schoolid, $userObject->get_user_ID(), $curr_code, $code, $string['code']);
        }
    }
    echo json_encode('SUCCESS');
}
