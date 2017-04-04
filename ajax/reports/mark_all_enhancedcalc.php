<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Marks all Calculation questions for a summative paper.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/errors.php';
require_once '../../plugins/questions/enhancedcalc/enhancedcalc.class.php';
require_once '../../plugins/questions/enhancedcalc/helpers/enhancedcalc_helper.php';

set_time_limit(0);

//header('Content-Type: text/html; charset=' + $configObject->get('cfg_page_charset'));

$paperID = check_var('paperID', 'REQUEST', true, false, true);
$startdate = check_var('startdate', 'REQUEST', true, false, true);
$enddate = check_var('enddate', 'REQUEST', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$questions = $properties->get_questions();
$paper_type = $properties->get_paper_type();

$error = false;

// Get the enhanced calculation questions on the paper.
$q_ids = array();
$result = $mysqli->prepare("SELECT question, settings FROM papers, questions WHERE papers.question = questions.q_id AND q_type = 'enhancedcalc' AND paper = ?");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_id, $settings);
while ($result->fetch()) {
  $q_ids[$q_id] = $settings;
}
$result->close();

$possible = array();

// Check random blocks questions for calculation questions
$random = $mysqli->prepare("SELECT q_id, settings FROM questions WHERE q_type ='enhancedcalc' AND q_id in ("
    . "SELECT DISTINCT random_link.q_id FROM random_link, papers WHERE question = random_link.id  AND paper = ?)");
$random->bind_param('i', $paperID);
$random->execute();
$random->bind_result($random_id, $random_settings);
while ($random->fetch()) {
    $possible[$random_id] = $random_settings;
}
$random->close();

// Check keyword based questions for calculation questions
$keyword = $mysqli->prepare("SELECT q_id, settings FROM questions WHERE q_type ='enhancedcalc' AND q_id in ("
    . "SELECT DISTINCT keywords_question.q_id FROM keywords_question, keywords_link, papers WHERE question = keywords_link.q_id AND keywordID = keyword_id AND paper = ?)");
$keyword->bind_param('i', $paperID);
$keyword->execute();
$keyword->bind_result($keyword_id, $keyword_settings);
while ($keyword->fetch()) {
    $possible[$keyword_id] = $keyword_settings;
}
$keyword->close();

// Find the questions used in the paper from the list of possible found from random blocks and keyword based questions.
if (count($possible) > 0) {
    $possible_string = implode(',', array_keys($possible));
    $check_possible = $mysqli->prepare("SELECT q_id from log$paper_type, log_metadata where metadataID = log_metadata.id "
      . "AND q_id in ($possible_string) AND paperID = ? AND started BETWEEN ? AND ?");
    $check_possible->bind_param('iss', $paperID, $startdate, $enddate);
    $check_possible->execute();
    $check_possible->bind_result($possible_id);
    while ($check_possible->fetch()) {
        $q_ids[$possible_id] = $possible[$possible_id];
    }
    $check_possible->close();
}

$server_connection = true;

$statuses = array();
// Should not get here but if we do throw a critical error.
if (count($q_ids) == 0) {
    // Critical error
    $error = true;
    $errline = __LINE__ - 3;
    $return_status = $string['noenhancedcalcdetected'];
} else {
    $return_status = 'Complete';
}

foreach ($q_ids as $q_id => $setting) {
    $data = enhancedcalc_remark($paper_type, $paperID, $q_id, $setting, $mysqli, 'all');
    if ($data[-3] > 0) {
        $server_connection = false;
    }
    $statuses[$q_id] = $data;
}

$problem_questions = array();

foreach($statuses as $qid => $data) {
    if ($data[Q_MARKING_UNMARKED] > 0 or $data[Q_MARKING_ERROR] > 0) {     // Record unmarked and marking error problems.
        $q_no = get_question_no($qid, $questions);
        // Use the is if we cannot find the number.
        // Most likely a random block / keyword based question.
        if ($q_no == '') {
            $q_no = 'qid: ' . $qid;
        }
        $problem_questions[] = $q_no;
    }
}

if (count($problem_questions) > 0) {
    $return_status = sprintf($string['problemsdetected'], implode(', ', $problem_questions));
    $errline = __LINE__ - 2;
    $error = true;
}

if ($error) {

    $userid = $userObject->get_user_ID();
    $username = $userObject->get_username();
    $error_type = 'Application Error';
    $errstr = $return_status;
    $errfile = $_SERVER['PHP_SELF'];
    $post_data = '';
    if (isset($_POST)) {
      foreach ($_POST as $key => $value) {
        if ($key != 'ROGO_PW') {
          if (is_array($value)) {
            $value = var_export($value, true);
          }
          if ($post_data == '') {
            $post_data = "$key=$value";
          } else {
            $post_data .= ", $key=$value";
          }
        } else {
          $post_data .= ", $key=<HIDDEN>";
        }
      }
    }

    $log_error = $mysqli->prepare("INSERT INTO sys_errors VALUES(NULL, NOW(), ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, NULL, NULL)");
    $log_error->bind_param('issssssssis', $userid, $username, $error_type, $errstr, $errfile, $errline, $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_METHOD'], $paperID, $post_data);
    $log_error->execute();
    $log_error->close();

    $support_email = $configObject->get('support_email');
    if ($support_email != '') {
            $return_status .= '<br /><br /><br />' . $string['pleasecontact'] . ' <a href="mailto:' . $support_email . '">' . $support_email . '</a>';
    }
}

echo $return_status;

function get_question_no($qid, $questions) {
    foreach($questions as $question) {
        if ($qid == $question['q_id']) {
            $problem_qid = $question['q_no'];
            return $problem_qid;
        }
    }
}
?>

