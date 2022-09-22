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
 * Add School
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$exists = false;

$school = check_var('school', 'POST', true, false, true);
$faculty = check_var('facultyID', 'POST', true, false, true);
$code = check_var('code', 'POST', false, false, true);
$externalid = check_var('externalid', 'POST', false, false, true);
$externalsys = check_var('externalsys', 'POST', false, false, true);
if (!is_null($code)) {
    $exists = SchoolUtils::get_schoolid_by_code($code, $mysqli);
} elseif (SchoolUtils::school_exists_in_faculty($faculty, $school, $mysqli)) {
    $exists = true;
}
if ($exists === false) {
    if (SchoolUtils::add_school($faculty, $school, $mysqli, $code, $externalid, $externalsys)) {
        echo json_encode('SUCCESS');
    } else {
        echo json_encode('ERROR');
    }
} else {
    echo json_encode($school);
}
