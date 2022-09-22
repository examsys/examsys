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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/admin_auth.inc';
require_once '../include/errors.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);

// Check the paperID exists
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$clarif_types = $configObject->get_setting('core', 'summative_midexam_clarification');
if ($properties->get_paper_type() == '2' and $userObject->has_role(array('SysAdmin', 'Admin')) and $properties->is_live() and $properties->get_bidirectional() == '1' and count($clarif_types) > 0) {
    $exam_clarifications = true;
} else {
    $exam_clarifications = false;
}

// Check the paper is not set to be linear.
// Check if paper is Summative Exam.
// Check if paper is not live.
if (!$exam_clarifications) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

// Check that the questionID exists
$q_id = check_var('q_id', 'REQUEST', true, false, true);
if (!QuestionUtils::question_exists($q_id, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

$exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);

$screenNo = check_var('screenNo', 'POST', true, false, true);
$questionNo = check_var('questionNo', 'POST', true, false, true);
$msg = check_var('msg', 'POST', true, false, true);

$exam_announcementObj->replace_announcement($q_id, $questionNo, $screenNo, $msg);
echo json_encode('SUCCESS');
