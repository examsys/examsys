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
 * Displays an HTML page in a suitable way that it could be printed
 * with the intention of making a student answer booklet (i.e. only
 * questions, no answers).
 *
 * @author Simon Wilkinson, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../config/index.inc';
require_once '../include/errors.php';

$id = check_var('id', 'GET', true, false, true, param::ALPHANUM); // While it is an int, the numbers are too large for 32-bit PHP.

$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);
$papertype = $propertyObj->get_paper_type();
// Get how many screens make up the question paper.
$screen_data = array();
$row_no = 0;
$stmt = $mysqli->prepare("SELECT property_id, labs, paper_title, paper_type, paper_prologue, marking, screen, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), bgcolor, fgcolor, themecolor, labelcolor, bidirectional, calendar_year, password FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND crypt_name=? AND papers.question=questions.q_id AND q_type != 'info' ORDER BY screen");
$stmt->bind_param('s', $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $labs, $paper_title, $paper_type, $paper_prologue, $marking, $screen, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $bidirectional, $calendar_year, $password);
if ($stmt->num_rows == 0) {  // No record found, the paper can't exist
    $stmt->close();
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
while ($stmt->fetch()) {
    $row_no++;
    $no_screens = $screen;
    if (!isset($screen_data[$no_screens])) {
        $screen_data[$no_screens] = 1;
    } else {
        $screen_data[$no_screens]++;
    }
    if ($row_no == 1) {
        $original_paper_type = $paper_type;
    }
}
$stmt->free_result();
$stmt->close();

$current_screen = 1;

$texteditorplugin = \plugins\plugins_texteditor::get_editor();
$renderpath = $texteditorplugin->get_render_paths();
$renderpath[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates';
$render = new render($configObject, $renderpath);
$headerdata = array(
    'css' => array(
        '/css/print.css',
        '/css/html5.css',
        '/node_modules/mediaelement/build/mediaelementplayer.min.css',
    ),
    'scripts' => array(
        '/js/printinit.min.js',
    ),
    'metadata' => array(
        'pragma' => 'no-cache',
    ),
);
if ($papertype == '3') {
    $lang['title'] = $string['survey'];
} else {
    $lang['title'] = $string['assessment'];
}

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
$render->render($headerdata, $lang, 'header.html');
$themedirectory = rogo_directory::get_directory('theme');
$logo_path = $themedirectory->url($configObject->get_setting('core', 'misc_logo_main'));
$contentdata['logopath'] = $logo_path;
$contentdata['papertitle'] = $propertyObj->get_paper_title();
$contentdata['print'] = Paper_utils::onPrintScreen();
$render->render($contentdata, $string, 'paper/header.html');

// Initialise for scenario filtering
$last_scenario = '';

$user_answers = array();
$question_no = 0;
$q_displayed = 0;
$marks = 0;
$hide_notes = param::optional('hidenotes', false, param::BOOLEAN, param::FETCH_GET);
$tmp_questions_array = $propertyObj->build_paper(false, null, null, $hide_notes);
//look for braching and random questions and overwrite as needed
$questions_array = array();
$tmp_q_no = 0;
foreach ($tmp_questions_array as &$question) {
    if ($question['q_type'] != 'info') {
        $tmp_q_no++;
    }
    if ($question['q_type'] == 'random') {
        $question = $propertyObj->randomQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
    } elseif ($question['q_type'] == 'keyword_based') {
        $question = $propertyObj->keywordQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
    }
    if ($question['q_type'] == 'enhancedcalc') {
        require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
        if (!isset($configObj)) {
            $configObj = Config::get_instance();
        }
        $question['object'] = new EnhancedCalc($configObj);
        $question['object']->load($question);
    }
    $questions_array[] = $question;
}
unset($tmp_questions_array);

//display the questions
$paperID = $propertyObj->get_property_id();
// Get linked question parents.
foreach ($questions_array as &$question) {
    $questionrender = new questionrender($question['q_type']);
    // Check if last scenario is the same as this one, and on the same screen,
    // for skipping repeat scenario display if turned on
    if ($configObject->get_setting('core', 'paper_hide_repeat_scenario')) {
        if (QuestionUtils::is_scenario_similar($question['scenario'], $last_scenario)) {
            $questionrender->add_override('displayscenario', false);
        }
        $last_scenario = $question['scenario'];
    }
    if (param::optional('break', false, param::BOOLEAN, param::FETCH_GET)) {
        $question['pagebreak'] = true;
    }
    $questionrender->display_question(0, $q_displayed, $string, $question, $paperID, $current_screen, $question_no, $user_answers);
    $q_displayed++;
}

// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render = new render($configObject);
$render->render($jsdataset, array(), 'dataset.html');
// Dataset.
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['language'] = $language;
$miscdataset['attributes']['rootpath'] = $cfg_root_path;
$render->render($miscdataset, array(), 'dataset.html');

$mysqli->close();

$render->render(array(), array(), 'footer.html');
