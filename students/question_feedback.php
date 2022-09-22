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
require_once '../include/calculate_marks.inc';
require_once '../include/errors.php';
require_once '../include/mapping.inc';
require_once '../include/finish_functions.inc';
require_once '../include/paper_security.php';

//HTML5 part
require_once '../lang/' . $language . '/paper/finish.php';

check_var('id', 'GET', true, false, false);

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);

$paperID    = $propertyObj->get_property_id();
$paper_type = $propertyObj->get_paper_type();
if (isset($_GET['type'])) {
    $log_type = $_GET['type'];
} else {
    $log_type = $propertyObj->get_paper_type();
}

// Check if paper can be released date wise
if (!$propertyObj->is_question_fb_released()) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

//lookup previous sessionid from log_metadata.started property_id
if (isset($_GET['userid'])) {
    if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
        $log_metadata = new LogMetadata($_GET['userid'], $paperID, $mysqli);
    } else {
        $notice->access_denied($mysqli, $string, $string['norights'], true, true);
    }
} else {
    $log_metadata = new LogMetadata($userObject->get_user_ID(), $paperID, $mysqli);
}
$log_metadata->get_record();
$metadataid = $log_metadata->get_metadata_id();

if ($metadataid === null) {
    $notice->access_denied($mysqli, $string, $string['nottaken'], true, true);
}

$preview_q_id = (isset($_GET['q_id'])) ? $_GET['q_id'] : null;
$moduleID = Paper_utils::get_modules($paperID, $mysqli);

if ($userObject->has_role('Student')) {
    // Check for additional password on the paper
    check_paper_password($propertyObj->get_property_id(), $propertyObj->get_password(), $string, $mysqli, true);

    $display_correct_answer     = 1;
    $display_question_mark      = 1;
    $display_students_response  = 1;
    $display_feedback           = 1;
} else {
    $display_correct_answer     = $propertyObj->get_display_correct_answer();
    $display_question_mark      = $propertyObj->get_display_question_mark();
    $display_students_response  = $propertyObj->get_display_students_response();
    $display_feedback           = $propertyObj->get_display_feedback();
}

$pass_mark = $propertyObj->get_pass_mark();

$logger = new Logger($mysqli);
if ($userObject->has_role('Student')) {
    $logger->record_access($userObject->get_user_ID(), 'Question-based feedback report', $paperID);  // Students write in the paperID
} else {
    $logger->record_access($userObject->get_user_ID(), 'Question-based feedback report', '/students/question_feedback.php?' . $_SERVER['QUERY_STRING']);    // Staff write in the URL details
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
        '/js/questionfeedback.min.js',
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
$contentdata['papertitle'] = $propertyObj->get_paper_title();
$contentdata['screen'] = array();
$contentdata['hidden'] = array();
$render->render($contentdata, $string, 'paper/header.html');
$current_screen = 1;

if (isset($_GET['userID'])) {
    if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
        if ($_GET['userID'] != '') {
            $userID = $_GET['userID'];
        } else {
            display_error($string['idmissing'], $string['idmissing_msg'], false, true, false);
        }
    } else {  // Student is trying to hack into another students userID on the URL.
        header('HTTP/1.0 404 Not Found');
        $contactemail = support::get_email();
        $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
        $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }
} else {
    $userID = $userObject->get_user_ID();
}

$old_q_id = 0;
$old_screen = 0;
// Get any marking override for the paper
$overrides = array();
$sql = "SELECT m.q_id, title, surname, date_marked, new_mark_type, adjmark
      FROM marking_override m INNER JOIN users u ON m.marker_id = u.id
      INNER JOIN log{$log_type} l ON m.log_id = l.id
      WHERE user_id = ? AND paper_id = ?";
$result = $mysqli->prepare($sql);
$result->bind_param('ii', $userID, $paperID);
$result->execute();
$result->store_result();
$result->bind_result($o_q_id, $o_title, $o_surname, $o_date_marked, $o_new_mark_type, $o_adjmark);
while ($result->fetch()) {
    $overrides[$o_q_id] = array('q_id' => $o_q_id, 'title' => $o_title, 'surname' => $o_surname, 'date_marked' => $o_date_marked, 'new_mark_type' => $o_new_mark_type, 'adjmark' => $o_adjmark);
}
$result->close();

$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
display_feedback($propertyObj, $userID, $log_type, $userObject, $log_metadata, $mysqli, $status_array, $overrides, $preview_q_id);

// Dataset.
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['language'] = $language;
$miscdataset['attributes']['rootpath'] = $cfg_root_path;
$render = new render($configObject);
$render->render($miscdataset, array(), 'dataset.html');
$render->render(array('rootpath' => $cfg_root_path), html5_helper::get_instance()->get_lang_strings(), 'html5_footer.html');
$mysqli->close();
$render->render(array(), array(), 'footer.html');
