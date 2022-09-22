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
 * Completes final log of the last screen to the 'logX' table and then will display feedback if the paper is in 'formative'
 * mode or will display a confirmation notice to the examinee stating all answers and marks have been successfully recorded.
 *
 * @author Simon Wilkinson, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_student_auth.inc';
require_once '../include/marking_functions.inc';
require_once '../include/calculate_marks.inc';
require_once '../include/errors.php';
require_once '../include/mapping.inc';
require_once '../include/finish_functions.inc';
require_once '../include/paper_security.php';
require_once '../LTI/ims-lti/UoN_LTI.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';
//HTML5 part

$id = check_var('id', 'GET', true, false, true, param::ALPHANUM); // While it is an int, the numbers are too large for 32-bit PHP.
$mode = param::optional('mode', '', param::ALPHA);
$getmode = param::optional('mode', '', param::ALPHA, param::FETCH_GET);
$q_id = param::optional('q_id', null, param::INT, param::FETCH_GET);
$getuser = param::optional('userID', 0, param::INT, param::FETCH_GET);
$metadataid = param::optional('metadataID', 0, param::INT, param::FETCH_GET);
$do_not_record = param::optional('dont_record', false, param::BOOLEAN, param::FETCH_GET);
$log_override = param::optional('log_type', -1, param::INT, param::FETCH_GET);

$demo = \demo::is_demo($userObject);
$userID = $userObject->get_user_ID();

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

$paperID                    = $propertyObj->get_property_id();
$labs                       = $propertyObj->get_labs();
$calendar_year              = $propertyObj->get_calendar_year();
$display_correct_answer     = $propertyObj->get_display_correct_answer();
$display_question_mark      = $propertyObj->get_display_question_mark();
$display_students_response  = $propertyObj->get_display_students_response();
$display_feedback           = $propertyObj->get_display_feedback();
$hide_if_unanswered         = $propertyObj->get_hide_if_unanswered();
$paper_title                = $propertyObj->get_paper_title();
$paper_type                 = $propertyObj->get_paper_type();
$start_date                 = $propertyObj->get_start_date();
$end_date                   = $propertyObj->get_end_date();
$marking                    = $propertyObj->get_marking();
$paper_postscript           = $propertyObj->get_paper_postscript();
$pass_mark                  = $propertyObj->get_pass_mark();
$password                   = $propertyObj->get_password();
$moduleID                   = $propertyObj->get_modules();

$show_feedback              = can_display_feedback($paper_type, $moduleID, $userObject);

$attempt = 1; // Default attempt to 1 overwritten if the student is resit candidate

$log_type = $paper_type;    // Set log_type to current type of the paper.

// Formative and progress tests can be changed into each other.
// Unfortunatly this means that the students answers may not be in the log table that the paper currently suggests.
// So we will allow this parameter to modify which log table we look at in these cases.
if (($paper_type == '0' or $paper_type == '1') and ($log_override == 0 or $log_override == 1)) {
    $log_type = $log_override;
}

$low_bandwidth = 0;
$lab_id = null;

// Get lab info
$current_address = NetworkUtils::get_client_address();
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)) {
    $lab_name = $lab_object->get_name();
    $lab_id = $lab_object->get_id();
}

$summative_exam_session_started = false;
$paper_scheduled = ($propertyObj->get_start_date() !== null);
if ($propertyObj->get_exam_duration() != null and $propertyObj->get_paper_type() == '2') {
    // Has this lab had an end time set?
    $log_lab_end_time = $propertyObj->getLogLabEndTime($lab_id);
    $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();
}

if ($getuser) {
    if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff', 'External Examiner'))) {
        $log_metadata = new LogMetadata($getuser, $paperID, $mysqli);
    } else {   // Student is hacking the userid parameter.
        $contactemail = support::get_email();
        $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
        $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }
} else {
    $log_metadata = new LogMetadata($userObject->get_user_ID(), $paperID, $mysqli);
}

if ($userObject->has_role(array('External Examiner'))) {
    // No further security checks.
    if (!ReviewUtils::is_external_on_paper($userObject->get_user_ID(), $paperID, $mysqli)) {
        $contactemail = support::get_email();
        $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
        $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
    }
} elseif (($userObject->has_role('Staff') and check_staff_modules($moduleID, $userObject)) or $userObject->has_role('SysCron')) {
    // No further security checks.
} else {
    $modIDs = array_keys($moduleID);

    // Check for additional password on the paper
    check_paper_password($propertyObj->get_property_id(), $password, $string, $mysqli);

    // Check time security
    check_datetime($start_date, $end_date, $string, $mysqli);

    // Check room security
    $low_bandwidth = check_labs($paper_type, $labs, $current_address, $password, $string, $mysqli);

    // Get modules if the user is a student and the paper is not formative
    $attempt = check_modules($userObject, $modIDs, $calendar_year, $string, $mysqli);

    // Check for any metadata security restrictions
    check_security_metadata($paperID, $userObject, $modIDs, $string, $mysqli);

    // Check for Safe Exam Browser restrictions
    check_seb_headers($paperID, $userObject, $string, $mysqli);

    if ($propertyObj->shouldLogLate($lab_id, $log_metadata)) {
        $paper_type = '_late';
    }
}

// Are we in a staff test and preview mode?
$is_preview_mode = ($userObject->has_role(array('Staff', 'SysAdmin')) and $mode === 'preview');
$is_summative_preview_mode = ($is_preview_mode and $propertyObj->get_paper_type() == '2');

// Are we in a staff test and preview mode and on the first screen?
$is_preview_mode_first_launch = ($is_preview_mode == true and $getmode === 'preview');

// Are we in a staff single question testmode?
$is_question_preview_mode = (!is_null($q_id));

$is_exam_review_mode = ($userObject->has_role(array('Staff', 'External Examiner')) and $getuser != $userObject->get_user_ID());

$is_formative_review = (!empty($metadataid) and $paper_type == '0');

if ($is_exam_review_mode or $is_question_preview_mode or $is_summative_preview_mode) {
    // Turn on all feedback if staff and a student exam script is being reviewed.
    $display_correct_answer     = 1;
    $display_question_mark      = 1;
    $display_students_response  = 1;
    $display_feedback           = 1;
    $hide_if_unanswered         = 0;
    $is_exam_review_mode        = true;
}

if (!empty($metadataid)) {
    $log_metadata->get_record($metadataid);
} else {
    $log_metadata->get_record();
    $metadataid = $log_metadata->get_metadata_id();
}

if (!$is_exam_review_mode and !$is_question_preview_mode and !$is_formative_review) {
    // Only update log metadata if we are ending an exam.
    $log_metadata->set_completed_to_now();
}

// Check if student has taken the paper. If they have not we need to stop them in their tracks as there is no feedback for them to review.
// Access allowed if user role is staff,student (admins should ensure staff,student users are not students in modules they are staff on)
if ($userObject->has_role('Student') and !$userObject->has_role('Staff')) {
    if (!$userObject->user_completed_paper($paperID)) {
        $contactemail = support::get_email();
        $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
        $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
    }
}

if (isset($low_bandwidth) and $low_bandwidth == 1) {
    // Low bandwidth enable compression
    ob_start('ob_gzhandler');
}

$texteditorplugin = \plugins\plugins_texteditor::get_editor();
$renderpath = $texteditorplugin->get_render_paths();
$renderpath[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates';
$render = new render($configObject, $renderpath);
$headerdata = array(
    'css' => array(
        '/css/start.css',
        '/css/finish.css',
        '/css/html5.css',
        '/node_modules/mediaelement/build/mediaelementplayer.min.css',
    ),
    'scripts' => array(
        '/js/finishinit.min.js',
    ),
);
$lang['title'] = $string['examscript'];
$headerdata['mathjax'] = false;
if ($configObject->get_setting('core', 'paper_mathjax')) {
    $headerdata['mathjax'] = true;
}
// Check if 3d file types are enabled and load js.
$headerdata['three'] = false;
if ($configObject->get_setting('core', 'paper_threejs')) {
    $headerdata['three'] = true;
    $headerdata['scripts'] = array_merge($headerdata['scripts'], threed_handler::get_js());
    $headerdata['css'] = array_merge($headerdata['css'], threed_handler::get_css());
}
$headerdata['mee'] = $configObject->get_setting('core', 'paper_mee');
$headerdata['texteditor'] = $texteditorplugin->get_header_file();
$editor = \plugin_manager::get_plugin_type_enabled('plugin_texteditor');
$headerdata['editor'] = $editor[0];

$css = PaperProperties::paperCss(
    $userObject,
    $propertyObj->get_bgcolor(),
    $propertyObj->get_fgcolor(),
    UserObject::TEXTSIZE,
    UserObject::MARKSCOLOUR,
    $propertyObj->get_themecolor(),
    $propertyObj->get_labelcolor(),
);
$render->render($headerdata, $lang, 'header.html', '', $css);

$themedirectory = rogo_directory::get_directory('theme');
$logo_path = $themedirectory->url($configObject->get_setting('core', 'misc_logo_main'));
$contentdata['logopath'] = $logo_path;
$contentdata['examclarification'] = '';
$contentdata['papertitle'] = $paper_title;
$contentdata['screen'] = array();
$contentdata['hidden'] = array();
$contentdata['previewmode'] = $is_question_preview_mode;


if (isset($getuser)) {
    $temp_userID = $getuser;
    $result = $mysqli->prepare('SELECT title, initials, surname, student_id FROM users LEFT JOIN sid ON users.id = sid.userID WHERE id = ? LIMIT 1');
    $result->bind_param('i', $temp_userID);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_title, $tmp_initials, $tmp_surname, $tmp_student_id);
    $result->fetch();
    $result->close();
} else {
    $temp_userID    = $userObject->get_user_ID();
    $tmp_title      = $userObject->get_title();
    $tmp_initials   = $userObject->get_initials();
    $tmp_surname    = $userObject->get_surname();
    $tmp_student_id = '';
}

if (is_null($q_id)) {
    if ($userObject->has_role('External Examiner')) {
        $contentdata['student']['id'] = $tmp_student_id;
    } elseif ($paper_type < 2 or $userObject->has_role(array('Staff', 'Admin', 'SysAdmin', 'External Examiner'))) {
        $contentdata['student']['id'] = \demo::demo_replace_number($tmp_student_id, $demo);
        $contentdata['student']['name'] = $tmp_title . ' ' . \demo::demo_replace(
            $tmp_surname,
            $demo
        ) . ', ' . \demo::demo_replace($tmp_initials, $demo);
    }
}
$render->render($contentdata, $string, 'paper/header.html');

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$papertype = $properties->get_paper_type();
$feedbackdata['remote'] = $properties->getSetting('remote_summative');
$feedbackdata['papertype'] = $papertype;
$feedbackdata['fullscreen'] = $properties->get_fullscreen();
$feedbackdata['displayfeedback'] = $show_feedback;

$preview_q_id = (!is_null($q_id)) ? $q_id : null;

$current_screen = param::optional('current_screen', 1, param::INT, param::FETCH_POST);
if ($current_screen > 1 and !$do_not_record) {
    // Record answers from the previous screen.
    record_marks($paperID, $mysqli, $paper_type, $metadataid, $preview_q_id);
}

$old_q_id = 0;
$old_screen = 0;
// Get any marking override for the paper
$paper_utils = Paper_utils::get_instance();
$overrides = $paper_utils->get_marking_overrides($log_type, $temp_userID, $paperID);

$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
$remote = $propertyObj->getSetting('remote_summative');
if ($show_feedback) {
    $feedbackdata['feedback'] = display_feedback($propertyObj, $temp_userID, $log_type, $userObject, $log_metadata, $mysqli, $status_array, $overrides, $preview_q_id);

    // Record the fact that the script has been viewed.
    $logger = new Logger($mysqli);
    if ($userObject->has_role(array('SysAdmin','Admin','Staff','External Examiner'))) {
        $logger->record_access($userObject->get_user_ID(), 'Assessment script', '/paper/finish.php?' . $_SERVER['QUERY_STRING']);    // Staff write in the URL details
    } else {
        $logger->record_access($userObject->get_user_ID(), 'Assessment script', $paperID);  // Students write in the paperID
    }
} else {
    if ($paper_type == '2' and $remote) {
        $feedbackdata['issuelink'] = $configObject->get_setting('core', 'summative_issuelink2');
    }
    $feedbackdata['papertitle'] = sprintf($string['msg1'], $paper_title);
    $feedbackdata['postscript'] = $paper_postscript;
}

$render->render($feedbackdata, $string, 'paper/feedback.html');

// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render = new render($configObject);
$render->render($jsdataset, array(), 'dataset.html');
// Dataset.
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['language'] = $language;
$miscdataset['attributes']['rootpath'] = $cfg_root_path;
$miscdataset['attributes']['papertype'] = $paper_type;
$miscdataset['attributes']['remotesummative'] = $remote;
$render->render($miscdataset, array(), 'dataset.html');

$render->render(array('rootpath' => $cfg_root_path), html5_helper::get_instance()->get_lang_strings(), 'html5_footer.html');
$render->render(array(), array(), 'footer.html');
$mysqli->close();
