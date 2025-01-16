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
 * Process the fill in teh blank remarking.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require '../include/errors.php';

$q_id     = check_var('q_id', 'POST', true, false, true);
$paperID  = check_var('paperID', 'POST', true, false, true);
$startdate = param::required('startdate', param::SQLDATETIME, param::FETCH_POST);
$enddate = param::required('enddate', param::SQLDATETIME, param::FETCH_POST);
$wordcount = param::required('word_count', param::INT, param::FETCH_POST);
$blank = param::required('blank', param::TEXT, param::FETCH_POST);
$studentsonly = param::optional('studentsonly', 1, param::BOOLEAN);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper_type = $propertyObj->get_paper_type();

// Read whole question from database.
$result = $mysqli->prepare('SELECT option_text FROM options WHERE o_id = ?');
$result->bind_param('i', $q_id);
$result->execute();
$result->bind_result($option_text);
$result->fetch();
$result->close();

// Read user properties from questions.
$result = $mysqli->prepare('SELECT score_method, marks_correct, marks_incorrect FROM questions, options WHERE questions.q_id = options.o_id AND q_id = ?');
$result->bind_param('i', $q_id);
$result->execute();
$result->bind_result($score_method, $marks_correct, $marks_incorrect);
$result->fetch();
$result->close();

// Read user answers from log.
if ($studentsonly) {
    $rolesjoin = \log::get_student_only('lm.userID');
} else {
    $rolesjoin = '';
}

$log_answers = array();
$time_int = \log::getStartInterval($paper_type);
if ($paper_type == \assessment::TYPE_FORMATIVE) {
    $progress_time_int = \log::getStartInterval(\assessment::TYPE_PROGRESS);
    $sql = "(
                SELECT 
                    0 AS type, l.id, l.user_answer 
                FROM 
                    log0 l, log_metadata lm $rolesjoin
                WHERE 
                    l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? 
                    AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ? AND lm.started <= ?
            ) UNION ALL (
                SELECT 
                    1 AS type, l.id, l.user_answer 
                FROM 
                    log1 l, log_metadata lm $rolesjoin
                WHERE 
                    l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? 
                    AND DATE_ADD(lm.started, INTERVAL $progress_time_int MINUTE) >= ? AND lm.started <= ?
            )";
    $result = $mysqli->prepare($sql);
    $result->bind_param('iissiiss', $q_id, $paperID, $startdate, $enddate, $q_id, $paperID, $startdate, $enddate);
} else {
    $sql = "SELECT 
                $paper_type AS type, l.id, l.user_answer 
            FROM 
                log$paper_type l, log_metadata lm $rolesjoin
            WHERE 
                l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ? 
                AND lm.started <= ?";
    $result = $mysqli->prepare($sql);
    $result->bind_param('iiss', $q_id, $paperID, $startdate, $enddate);
}
$result->execute();
$result->bind_result($type, $id, $user_answer);
while ($result->fetch()) {
    // Decode user answers into an array of lowercase strings.
    $tmp_answer = json_decode($user_answer);
    foreach ($tmp_answer as &$answer) {
        $answer = mb_strtolower(StringUtils::clean_and_trim($answer));
    }
    $log_answers[$type][$id] = $tmp_answer;
}
$result->close();


$option_list_array = array();

// Iterate around all words marked for correction
for ($i = 0; $i < $wordcount; $i++) {
    $postword = param::optional('word' . $i, null, param::TEXT, param::FETCH_POST);
    if (!is_null($postword)) {
        // Encode commas.
        $word = str_replace(',', '&#44;', $postword);
        $option_list_array[] = $word;
    }
}
$option_list = implode(',', $option_list_array);

$blank_details = explode('[blank', $option_text);
for ($i = 1; $i < count($blank_details); $i++) {
    $end_start_tag = mb_strpos($blank_details[$i], ']');
    $start_end_tag = mb_strpos($blank_details[$i], '[/blank]');
    $blank_options = mb_substr($blank_details[$i], ($end_start_tag + 1), ($start_end_tag - 1));

    $new_option_text = mb_substr($blank_details[$i], 0, ($end_start_tag + 1));
}

for ($i = 1; $i < count($blank_details); $i++) {
    $tmp_parts = explode('[/blank]', $blank_details[$i]);

    if ($i == $blank) {
        $blank_details[$i] = ']' . $option_list . '[/blank]' . $tmp_parts[1];
    }
}

$new_option_text = $blank_details[0];
for ($i = 1; $i < count($blank_details); $i++) {
    $new_option_text .= '[blank' . $blank_details[$i];
}

// Save the new option text back to the Questions table.
$result = $mysqli->prepare('UPDATE options SET option_text = ? WHERE o_id = ?');
$result->bind_param('si', $new_option_text, $q_id);
$result->execute();
$result->close();

$logger = new Logger($mysqli);
$success = $logger->track_change('Post-Exam Blank correction', $q_id, $userObject->get_user_ID(), $option_text, $new_option_text, 'Question/Stem');

// Remark student answers
$blank_details = explode('[blank', $new_option_text);
$no_answers = count($blank_details) - 1;

$totalpos = 0;
for ($i = 1; $i <= $no_answers; $i++) {
    if (preg_match('|mark="([0-9]{1,3})"|', $blank_details[$i], $mark_matches)) {
        $totalpos += $mark_matches[1];
        $individual_q_mark = $mark_matches[1];
    } else {
        $totalpos += $marks_correct;
        $individual_q_mark = $marks_correct;
    }
}

foreach ($log_answers as $log_type => $log_data) {
    foreach ($log_data as $id => $user_parts) {
        $mark = 0;
        $have_answer = false;
        $saved_response = '';
        // Required to shift array indexes.
        $blank_details_redo = array();
        $j = 0;
        $blank_details = explode('[blank', $new_option_text);
        for ($i = 1; $i <= $no_answers; $i++) {
            // Strip out answers from $blank_details
            // n.b. First item in $blank_details not required
            // Step 1. get all contents after ]
            $blank_details_redo[$j] = mb_substr($blank_details[$i], (mb_strpos($blank_details[$i], ']') + 1));
            // Step 2. get all contents before [/blank]
            $blank_details_redo[$j] = mb_substr($blank_details_redo[$j], 0, (mb_strpos($blank_details[$i], '[/blank]') - 1));
            // $blank_details_redo is now what was between ] and [/blank]
            $answer_list = explode(',', $blank_details_redo[$j]);
            if ($user_parts[$j] != 'u' and $user_parts[$j] != '') {
                $have_answer = true;
                $is_correct = false;
                foreach ($answer_list as $individual_answer) {
                    if ($user_parts[$j] == mb_strtolower(StringUtils::clean_and_trim($individual_answer))) {
                        $is_correct = true;
                        break;
                    }
                }
                $mark += ($is_correct) ? $individual_q_mark : $marks_incorrect;
            }
            $j++;
        }
        // Recalculate if mark per question
        if ($score_method == 'Mark per Question') {
            if ($have_answer) {
                $mark = ($mark == $totalpos) ? $marks_correct : $marks_incorrect;
            }
        }
        // Update marks in the database
        $result = $mysqli->prepare("UPDATE log$log_type SET mark = ? WHERE id = ?");
        $result->bind_param('ii', $mark, $id);
        $result->execute();
        $result->close();
    }
}

// Set paper to re-cache marks again after the change.
$propertyObj->set_recache_marks(1);
$propertyObj->save();

echo json_encode('SUCCESS');
