<?php

// This file is part of Rogō
//
// Rog? is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rog? is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rog?.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * This script can only be called from start.php for AJAX saving.
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);
require_once '../include/staff_student_auth.inc';
require_once '../include/marking_functions.inc';
require_once '../include/errors.php';
require_once '../include/paper_security.php';
require $cfg_web_root . 'lang/' . $language . '/include/paper_security.php';
$answer_changed = param::optional('ans_changed', false, param::BOOLEAN);
$random_page_id = param::optional('randomPageID', 'ERR_NO_PAGE_ID', param::ALPHANUM, param::FETCH_POST);
if (!$answer_changed) {
    echo $random_page_id;
    exit();
}

$displayDebug = false;
// AJAX call so debug info messes up the output.

$id = check_var('id', 'GET', true, false, true, param::ALPHANUM);
// While it is an int, the numbers are too large for 32-bit PHP.
$retry = param::optional('retry', 0, param::INT);
$mode = param::optional('mode', '', param::ALPHA);
$submit_type = param::optional('submitType', '', param::ALPHA, param::FETCH_GET);
$old_screen = param::optional('old_screen', 0, param::INT, param::FETCH_POST);
$settimeout = $configObject->get_setting('core', 'paper_autosave_settimeout');
$retrylimit = $configObject->get_setting('core', 'paper_autosave_retrylimit');
$backofffactor = $configObject->get_setting('core', 'paper_autosave_backoff_factor');
// Calculate how long this request should be processed based on the config vars and the retry number.
if (!is_null($retry) and $retry > 0 and $retry <= $retrylimit) {
    $extra_time = 1 + ceil($backofffactor * intval($retry) *  $settimeout);
} else {
    $extra_time = 1;
}

// Kill this request if it is taking to long the JavaScript will retry if it can.
set_time_limit($settimeout + $extra_time);
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);
$original_paper_type = $propertyObj->get_paper_type();
// Store the original paper type - needed to retrieve answers from the correct log and functionality related decisions

$attempt = 1;
// Default attempt to 1 overwritten if the student is resit candidate by (check_modules)
$low_bandwidth = 0;
// Default to off overwritten by (check_labs) if lab has low_bandwidth set
$lab_name = null;
// Default overwritten by (check_labs)
$lab_id = null;
$current_address = null;
// Default overwritten by (check_labs)

$current_address = NetworkUtils::get_client_address();
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)) {
    $lab_name = $lab_object->get_name();
    $lab_id = $lab_object->get_id();
}
$moduleID = $propertyObj->get_modules();
if ($userObject->has_role('Staff') and check_staff_modules($moduleID, $userObject)) {
    // No further security checks.
} else {
    // Treat as student with extra security checks.
    // Get the module IDs for this paper
    $modIDs = array_keys(Paper_utils::get_modules($propertyObj->get_property_id(), $mysqli));
    // Check for additional password on the paper
    check_paper_password($propertyObj->get_property_id(), $propertyObj->get_password(), $string, $mysqli);
    // Check time security
    check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date(), $string, $mysqli);

    // Check room security
    $low_bandwidth = check_labs(
        $propertyObj->get_paper_type(),
        $propertyObj->get_labs(),
        $current_address,
        $propertyObj->get_password(),
        $string,
        $mysqli
    );
    // Check modules if the user is a student and the paper is not formative
    $attempt = check_modules($userObject, $modIDs, $propertyObj->get_calendar_year(), $string, $mysqli);
    // Check for any metadata security restrictions
    check_metadata($propertyObj->get_property_id(), $userObject, $modIDs, $string, $mysqli);
    // Check if the student has clicked 'Finish'.
    check_finished($propertyObj, $userObject, $string, $mysqli);
    // Check current IP address with that of attempt in log.
    // Warn user they are logged into mulitple devices in this exam.
    $papertype = $propertyObj->get_paper_type();
    if ($papertype == '2') {
        check_ipmismatch($propertyObj->get_property_id(), $current_address, $string, $userObject, $mysqli, $papertype);
    }
  
    $summative_exam_session_started = false;
}

$is_preview = ($mode === 'preview');
$paper_scheduled = ($propertyObj->get_start_date() !== null);
if ($propertyObj->get_exam_duration() != null and $propertyObj->get_paper_type() == '2') {
    $log_lab_end_time = new LogLabEndTime($lab_id, $propertyObj, $mysqli);
    $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();
}

if (!$is_preview and time() > $propertyObj->get_end_date() and ($propertyObj->get_paper_type() == '1' or ($propertyObj->get_paper_type() == '2' and $paper_scheduled and $summative_exam_session_started == false))) {
    $propertyObj->set_paper_type('_late');
}

$preview_q_id = param::optional('q_id', null, param::INT, param::FETCH_GET);
$log_metadata = new LogMetadata($userObject->get_user_ID(), $propertyObj->get_property_id(), $mysqli);
if ($log_metadata->get_record() === false) {
    $notice->access_denied($mysqli, $string, $string['error_paper'], false);
}
$metadataid = $log_metadata->get_metadata_id();
if ($submit_type === 'userSubmit') {
    $log_metadata->set_highest_screen($old_screen);
}

try {
    $ret = record_marks($propertyObj->get_property_id(), $mysqli, $propertyObj->get_paper_type(), $metadataid, $preview_q_id);
} catch (RandomQuestionNotFound $ex) {
    $ret = false;
}

if ($ret === true) {
    // Everthing worked.
    echo $random_page_id;
} else {
    echo 'ERROR';
}
