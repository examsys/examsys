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

require '../../include/staff_student_auth.inc';
require_once '../../include/errors.php';

$paperID = check_var('paperID', 'POST', true, false, true);
$type = param::optional('type', 0, param::INT, param::FETCH_POST);
if ($userObject->has_role('Invigilator')) {
    $userID  = check_var('userID', 'POST', true, false, true);
} else {
    // Students can only add rest breaks for themselves if they require them and on remote summative papers.
    $userID = $userObject->get_user_ID();
    $properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
    if (!$properties->getSetting('remote_summative') or !$userObject->getRequiresBreaks()) {
        exit();
    }
}


// Does the paper exist?
if (!Paper_utils::paper_exists($paperID, $mysqli)) {
    exit();
}
// Does the student exist?
if (!UserUtils::userid_exists($userID, $mysqli)) {
    exit();
}

if ($type === Breaks::TYPE_ACCOMIDATION) {
    Breaks::addBreak($userID, $paperID);
} else {
    ToiletBreaks::add_toilet_break($userID, $paperID, $mysqli);
}
$mysqli->close();
