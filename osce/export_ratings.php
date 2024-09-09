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
 * Export cohort ratings in CSV format.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require_once '../include/errors.php';
$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate        = check_var('enddate', 'GET', true, false, true);
$repcourse  = check_var('repcourse', 'GET', true, false, true);
$studentsonly = param::optional('studentsonly', 1, param::BOOLEAN);
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$questions      = $propertyObj->get_questions();
$paper_title    = $propertyObj->get_paper_title();
header('Pragma: public');
header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . \file_handler::make_filename_safe($paper_title) . '.csv"');
$log_array = array();
$hits = 0;
$user_no = 0;
// Capture the log data first.
$time_int = \log::getStartInterval(\assessment::TYPE_OSCE);
if ($studentsonly) {
    $rolesjoin = \log::get_student_only('student.id');
} else {
    $rolesjoin = '';
}
$sql = <<<SQL
SELECT DISTINCT sid.student_id, student.username, student.title, student.surname, student.initials, examiner.title, examiner.surname, examiner.initials, student.grade, student.gender,
 started, log4.q_id, rating, year, feedback, numeric_score
FROM (log4, log4_overall, questions, users examiner, users student $rolesjoin) LEFT JOIN sid ON student.id = sid.userID
WHERE log4.log4_overallID = log4_overall.id AND log4.q_id = questions.q_id AND q_paper = ?
 AND student.id = log4_overall.userID
 AND examiner.id = log4_overall.examinerID
 AND student.grade LIKE ?
 AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ? AND started <= ?
SQL;
$result = $mysqli->prepare($sql);
$result->bind_param('isss', $paperID, $repcourse, $startdate, $enddate);
$result->execute();
$result->bind_result($user_ID, $username, $student_title, $student_surname, $student_initials, $examiner_title, $examiner_surname, $examiner_initials, $grade, $gender, $started, $q_id, $rating, $year, $feedback, $numeric_score);
while ($result->fetch()) {
    $log_array[$user_ID]['student_id'] = $user_ID;
    $log_array[$user_ID]['username'] = $username;
    $log_array[$user_ID][$q_id] = $rating;
    $log_array[$user_ID]['course'] = $grade;
    $log_array[$user_ID]['started'] = $started;
    $log_array[$user_ID]['title'] = $student_title;
    $log_array[$user_ID]['surname'] = $student_surname;
    $log_array[$user_ID]['initials'] = $student_initials;
    $log_array[$user_ID]['gender'] = $gender;
    $log_array[$user_ID]['year'] = $year;
    $log_array[$user_ID]['examiner'] = $examiner_title . ' ' .  $examiner_initials . ' ' . $examiner_surname;
    $log_array[$user_ID]['feedback'] = $feedback;
    $log_array[$user_ID]['numeric_score'] = $numeric_score;
    $user_no++;
}
  $result->close();

  $row_written = 0;
foreach ($log_array as $individual) {
    $tmp_user_ID = $individual['username'];
    if ($row_written == 0) {
        // Write out the headings.
        echo 'OSCE Station,Examiner,Gender,Title,Surname,Initials,Username,Student ID,Course,Year,Date';
        $i = 1;
        foreach ($questions as $question) {
            if ($question['type'] != 'info') {
                echo ',Q' . $i;
                $i++;
            }
        }
        echo ",Overall Score,Feedback\n";
    }

    // Write out the raw data.
    echo $paper_title . ',' . $individual['examiner'] . ',' . $individual['gender'] . ',' . $individual['title'] . ',' . $individual['surname'] . ',' . $individual['initials'] . ',' . $individual['username'] . ',' . $individual['student_id'] . ',' . $individual['course'] . ',' . $individual['year'] . ',' . $individual['started'];
    foreach ($questions as $question) {
        if ($question['type'] != 'info') {
            // Skip over information block questions.
                $tmp_question_ID = $question['q_id'];
            echo ',' . $individual[$tmp_question_ID];
        }
    }
    echo ',' . $individual['numeric_score'] . ',"' . $individual['feedback'] . '"';
    echo "\n";
    $row_written++;
}
  $mysqli->close();
