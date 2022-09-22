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
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require '../include/errors.php';

$duplicate = false;

$add_faculty = check_var('add_faculty', 'POST', true, false, true);
$code = check_var('code', 'POST', false, false, true);
$externalid = check_var('externalid', 'POST', false, false, true);
$externalsys = check_var('externalsys', 'POST', false, false, true);
if (!is_null($add_faculty)) {
    // Check for existing faculty code.
    if (!is_null($code)) {
        if (FacultyUtils::get_facultyid_by_code($code, $mysqli)) {
            $duplicate = 'DUPLICATE_CODE';
        }
    } else {
        // Check for existing faculty name.
        if (FacultyUtils::facultyname_exists($add_faculty, $mysqli)) {
            $duplicate = 'DUPLICATE_NAME';
        }
    }
    if ($duplicate === false) {
        FacultyUtils::add_faculty($add_faculty, $mysqli, $code, $externalid, $externalsys);
    }
}
if (!$duplicate) {
    if ($add_faculty != '') {
        echo json_encode('SUCCESS');
    } else {
        echo json_encode('FAILURE');
    }
} else {
    echo json_encode($duplicate);
}
