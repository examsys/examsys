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

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$metadataID = check_var('metadataID', 'POST', true, false, true);
$userID     = check_var('userID', 'POST', true, false, true);
$paperID    = check_var('paperID', 'POST', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper_type = $properties->get_paper_type();
$remote = $properties->getSetting('remote_summative');
// Only allow reset of timer for Progress tests and Remote Summative exams.
if ($paper_type != '1' and ($paper_type == '2' and !$remote)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

$log_metadata = new LogMetadata($userID, $paperID, $mysqli);

if ($log_metadata->get_record($metadataID) === false) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

$user_details = UserUtils::get_user_details($userID, $mysqli);
if ($user_details === false) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

$log_metadata->set_started_to_null();
// Delete break time.
LogBreakTime::deleteBreak($userID, $paperID);

$mysqli->close();
echo json_encode('SUCCESS');
