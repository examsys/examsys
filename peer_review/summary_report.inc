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

function padDecimals($number, $rounding)
{
    $number = round($number, $rounding);

    $parts = explode('.', $number);

    $integer = $parts[0];
    if (isset($parts[1])) {
        $decimals = $parts[1];
    } else {
        $decimals = '';
    }

    $dec_len = mb_strlen($decimals);

    for ($i = $dec_len; $i < $rounding; $i++) {
        $decimals .= '0';
    }

    return $integer . '.' . $decimals;
}

// Get questions on the paper
$questions = array();
$sql = 'SELECT q_type, question, leadin, display_method, option_text '
    . 'FROM (papers, questions, options) '
    . 'WHERE papers.question = questions.q_id AND questions.q_id = options.o_id AND paper = ? AND options.o_id = papers.question '
    . 'ORDER BY display_pos, id_num';
$result = $mysqli->prepare($sql);
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_type, $questionID, $leadin, $display_method, $option_text);
while ($result->fetch()) {
    $questions[$questionID]['leadin'] = $leadin;
    if ($q_type == 'likert') {
        $questions[$questionID]['scale'] = $display_method;
        $questions[$questionID]['scale_size'] = mb_substr_count($display_method, '|');
    } elseif ($q_type == 'mcq') {
        if (isset($questions[$questionID]['scale'])) {
            $questions[$questionID]['scale_size']++;
        } else {
            $questions[$questionID]['scale_size'] = 1;
        }
        $questions[$questionID]['scale'][] = $option_text;
    }
}
$result->close();

$group = '%';
$type = $propertyObj->get_rubric();
foreach ($_GET as $key => $value) {
    if (mb_substr($key, 0, 4) == 'meta') {
        $md_parts = explode('=', $value);
        if ($md_parts[0] == $type) {
            $group = $md_parts[1];
        }
    }
}

// Load basic user information
$user_data = array();
$groups = array();
$modules_array = Paper_utils::get_modules($paperID, $mysqli);
$calendar_year = $propertyObj->get_calendar_year();
if ($group == '%') {
    $result = $mysqli->prepare('SELECT users_metadata.userID, value, surname, first_names, title, student_id FROM (users_metadata, users) LEFT JOIN sid ON users.id = sid.userID WHERE users_metadata.userID = users.id AND idMod IN (' . implode(',', array_keys($modules_array)) . ') AND calendar_year = ? AND type = ? ORDER BY surname, initials');
    $result->bind_param('ss', $calendar_year, $type);
} else {
    $result = $mysqli->prepare('SELECT users_metadata.userID, value, surname, first_names, title, student_id FROM (users_metadata, users) LEFT JOIN sid ON users.id = sid.userID WHERE users_metadata.userID = users.id AND idMod IN (' . implode(',', array_keys($modules_array)) . ') AND calendar_year = ? AND type = ? AND value = ? ORDER BY surname, initials');
    $result->bind_param('sss', $calendar_year, $type, $group);
}
$result->execute();
$result->bind_result($userID, $group, $surname, $first_names, $title, $student_id);
$demo = \demo::is_demo($userObject);
while ($result->fetch()) {
    $user_data[$userID]['group']        = $group;
    $user_data[$userID]['surname']      = \demo::demo_replace($surname, $demo);
    $user_data[$userID]['first_names']  = \demo::demo_replace($first_names, $demo);
    $user_data[$userID]['title']        = $title;
    $user_data[$userID]['student_id']   = \demo::demo_replace_number($student_id, $demo);
    $user_data[$userID]['userID']       = $userID;

    $groups[$group][$student_id]        = $student_id;
}
$result->close();


$users = array_keys($user_data);
$users = implode(',', $users);
$questionids = implode(',', array_keys($questions));

if ($users != '' and $questionids != '') {
    // Load results from Log6
    $reviewers = array();

    $review_sql = 'SELECT reviewerID, peerID, q_id, rating '
        . 'FROM log6 '
        . "WHERE reviewerID IN ($users) AND q_id IN($questionids) "
        . 'AND paperID = ? AND started > ? AND started < ? ORDER BY started';
    $result = $mysqli->prepare($review_sql);
    $result->bind_param('iss', $paperID, $startdate, $enddate);
    $result->execute();
    $result->bind_result($reviewerID, $peerID, $q_id, $rating);
    while ($result->fetch()) {
        $user_data[$peerID]['data'][$q_id][$reviewerID] = $rating;
        $reviewers[$reviewerID] = $reviewerID;
    }
    $result->close();
}

// Parse the data calculating means
$review_type = $propertyObj->get_display_question_mark();
if ($review_type == '1') {
    parse_group_data($questions, $user_data);
} else {
    parse_single_data($questions, $user_data);
}


function parse_group_data($questions, &$user_data)
{
    global $reviewers;

    foreach ($user_data as $studentID => $student) {
        $available_marks = 0;
        $student_marks = 0;
        if (isset($student['data'])) {
            foreach ($student['data'] as $questionID => $question_data) {
                if (empty($questions[$questionID]['scale_size'])) {
                    // Not a valid peer review question type,
                    // we should not bother trying to analyse it.
                    continue;
                }
                $review_no      = 0;
                $review_total   = 0;
                $sub_student    = 0;
                $sub_available  = 0;
                foreach ($question_data as $individual_question) {
                    if ($individual_question != '') {
                        $review_no++;
                        $review_total += $individual_question;
                        $available_marks += $questions[$questionID]['scale_size'];
                        $sub_available += $questions[$questionID]['scale_size'];
                        $student_marks += $individual_question;
                        $sub_student += $individual_question;
                    }
                }
                $user_data[$studentID]['review_no'] = $review_no;
                if ($review_no > 0) {
                    $user_data[$studentID]['means'][$questionID] = $review_total / $review_no;
                    $user_data[$studentID]['percent'][$questionID] = ($sub_student / $sub_available) * 100;
                } else {
                    $user_data[$studentID]['means'][$questionID]    = 0;
                    $user_data[$studentID]['percent'][$questionID]  = 0;
                }
                $user_data[$studentID]['review_total'][$questionID] = $student_marks;
                $user_data[$studentID]['total_percent'][$questionID] = ($student_marks / $available_marks) * 100;
                $user_data[$studentID]['have_review'] = $review_no;
            }
        }
        if (array_key_exists('userID', $student)) {
            $user_data[$studentID]['have_review'] = isset($reviewers[$student['userID']]);
        } else {
            $user_data[$studentID]['have_review'] = 0;
        }
    }
}

function parse_single_data($questions, &$user_data)
{
    global $reviewers;

    foreach ($user_data as $studentID => $student) {
        $available_marks = 0;
        $student_marks = 0;
        if (isset($student['data'])) {
            foreach ($student['data'] as $questionID => $question_data) {
                if (empty($questions[$questionID]['scale_size'])) {
                    // Not a valid peer review question type,
                    // we should not bother trying to analyse it.
                    continue;
                }
                $review_no = 0;
                $review_total = 0;
                $sub_student = 0;
                $sub_available = 0;
                foreach ($question_data as $individual_question) {
                    if ($individual_question != '') {
                        $review_no++;
                        $review_total += $individual_question;
                        $available_marks += $questions[$questionID]['scale_size'];
                        $sub_available += $questions[$questionID]['scale_size'];
                        $student_marks += $individual_question;
                        $sub_student += $individual_question;
                    }
                }
                $user_data[$studentID]['review_no'] = $review_no;
                if ($review_no > 0) {
                    $user_data[0]['means'][$questionID] = $review_total / $review_no;
                    $user_data[0]['percent'][$questionID] = ($sub_student / $sub_available) * 100;
                }
                $user_data[0]['review_total'][$questionID] = $student_marks;
                $user_data[0]['total_percent'][$questionID] = ($student_marks / $available_marks) * 100;
            }
        }
        if (array_key_exists('userID', $student)) {
            $user_data[$studentID]['have_review'] = isset($reviewers[$student['userID']]);
        } else {
            $user_data[$studentID]['have_review'] = 0;
        }
    }
}
