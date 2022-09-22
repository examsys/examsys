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
 * Save the students break time.
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */

define('AJAX_REQUEST', true);

require '../include/staff_student_auth.inc';
require_once '../include/errors.php';
require_once '../include/paper_security.php';

try {
    $paperid = check_var('paperID', 'POST', true, false, true, param::INT);
    $time = check_var('time', 'POST', true, false, true, param::INT);
    if ($userObject->getRequiresBreaks()) {
        // Repeat the paper security checks before allowing a break to be recorded.
        $propertyObj = PaperProperties::get_paper_properties_by_id($paperid, $mysqli, $string, true);
        $modIDs = array_keys(Paper_utils::get_modules($paperid, $mysqli));
        // Check if enrolled.
        check_modules($userObject, $modIDs, $propertyObj->get_calendar_year(), $string, $mysqli);
        // Check for any metadata security restrictions.
        check_security_metadata($paperid, $userObject, $modIDs, $string, $mysqli);
        // Check if the student has clicked 'Finish'.
        check_finished($propertyObj, $userObject, $string, $mysqli);
        // Check the time.
        check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date(), $string, $mysqli, true);
        // Record break.
        LogBreakTime::setBreak($userObject->get_user_ID(), $paperid, $time);
    }
} catch (Exception $e) {
    echo json_encode('ERROR');
    exit();
}
echo json_encode('SUCCESS');
