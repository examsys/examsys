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

require_once '../include/staff_student_auth.inc';
require_once '../include/marking_functions.inc';
require_once '../include/errors.inc';
require_once '../include/paper_security.inc';
require_once '../classes/paperutils.class.php';
require_once '../classes/logmetadata.class.php';
require_once '../classes/lab_factory.class.php';
require_once '../classes/lab.class.php';
require_once '../classes/log_extra_time.class.php';
require_once '../classes/log_lab_end_time.class.php';
require_once '../classes/paperproperties.class.php';
require_once '../classes/exceptions.inc.php';

if ($_GET['ans_changed'] == '0') {
  echo $_POST['randomPageID'];
  exit();
}

$displayDebug = false; // AJAX call so debug info messes up the output.

check_var('id', 'GET', true, false, false);

// Calculate how long this request should be processed based on the config vars and the retry number.
if ( isset($_GET['retry']) and is_numeric($_GET['retry']) and $_GET['retry'] > 0 and $_GET['retry'] <= $configObject->get('cfg_autosave_retrylimit') ) {
  $extra_time = 1 + ceil($configObject->get('cfg_autosave_backoff_factor') * intval($_GET['retry']) *  $configObject->get('cfg_autosave_settimeout'));
} else {
  $extra_time = 1;
}

// Kill this request if it is taking to long the JavaScript will retry if it can.
set_time_limit($configObject->get('cfg_autosave_settimeout') + $extra_time);

$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);

$original_paper_type = $propertyObj->get_paper_type(); // Store the original paper type - needed to retrieve answers from the correct log and functionality related decisions

$attempt = 1;              // Default attempt to 1 overwritten if the student is resit candidate by (check_modules)
$low_bandwidth = 0;        // Default to off overwritten by (check_labs) if lab has low_bandwidth set
$lab_name = NULL;          // Default overwritten by (check_labs)
$lab_id = NULL;
$current_address = NULL;   // Default overwritten by (check_labs)

$current_address = NetworkUtils::get_client_address();
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)){
  $lab_name = $lab_object->get_name();
  $lab_id = $lab_object->get_id();
}
$moduleID = $propertyObj->get_modules();

if ($userObject->has_role('Staff') and check_staff_modules($moduleID, $userObject)) {
  // No further security checks.
} else {    // Treat as student with extra security checks.
  // Get the module IDs for this paper
  $modIDs = array_keys(Paper_utils::get_modules($propertyObj->get_property_id(), $mysqli));

  // Check for additional password on the paper
  check_paper_password($propertyObj->get_password(), $string, $mysqli);

  // Check time security
  check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date(), $string, $mysqli);

  // Check room security
  $low_bandwidth = check_labs(  $propertyObj->get_paper_type(),
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

  $summative_exam_session_started = false;
}

$is_preview = (isset($_POST['mode']) and $_POST['mode'] == 'preview');

$paper_scheduled = ($propertyObj->get_start_date() !== null);
if ($propertyObj->get_exam_duration() != null and $propertyObj->get_paper_type() == '2') {
  $log_lab_end_time = new LogLabEndTime($lab_id, $propertyObj, $mysqli);
  $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();
}

if (!$is_preview and time() > $propertyObj->get_end_date() and ( $propertyObj->get_paper_type() == '1' or ( $propertyObj->get_paper_type() == '2' and $paper_scheduled and $summative_exam_session_started == false))) {
  $propertyObj->set_paper_type('_late');
}

$preview_q_id = (isset($_GET['q_id'])) ? $_GET['q_id'] : null;

$log_metadata = new LogMetadata($userObject->get_user_ID(), $propertyObj->get_property_id(), $mysqli);
if ($log_metadata->get_record() === false) {
  $notice->access_denied($mysqli, $string, $string['error_paper'], false);
}
$metadataid = $log_metadata->get_metadata_id();

if ($_GET['submitType'] == 'userSubmit') {
  $log_metadata->set_highest_screen($_POST['old_screen']);
}

try {
  $ret = record_marks($propertyObj->get_property_id(), $mysqli, $propertyObj->get_paper_type(), $metadataid, $preview_q_id);
} catch (RandomQuestionNotFound $ex) {
  $ret = false;
}

if ($ret === true) {
  // Everthing worked.
  echo $_POST['randomPageID'];
} else {
  echo 'ERROR';
}
?>
