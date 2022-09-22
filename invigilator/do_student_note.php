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
 * Save student note.
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/invigilator_auth.inc';
require_once '../include/errors.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);
$userID = check_var('userID', 'REQUEST', true, false, true);  // User ID is the student ID.

// Does the paper exist?
if (!Paper_utils::paper_exists($paperID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}
// Does the student exist?
if (!UserUtils::userid_exists($userID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

$noteid = param::optional('note_id', 0, param::INT, param::FETCH_POST);
$note = param::optional('note', '', param::TEXT, param::FETCH_POST);
if ($noteid === 0) {
    if ($note != '') {  // Check we are not saving nothing.
        StudentNotes::add_note($userID, $note, $paperID, $userObject->get_user_ID(), $mysqli);
    } else {
        echo json_encode('ERROR');
        exit();
    }
} else {
    StudentNotes::update_note($note, $noteid, $mysqli);
}

echo json_encode('SUCCESS');
