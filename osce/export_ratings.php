<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  $paperID = $_GET['paperID'];
  
  // Capture the paper makeup.
  $paper_buffer = array();
  $question_no = 0;

  $result = $mysqli->prepare("SELECT paper_title, q_id FROM (papers, questions, properties) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND papers.paper=? AND q_type='likert' ORDER BY display_pos");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title, $q_id);
  while ($row = $result->fetch()) {
    $paper_buffer[$question_no]['ID'] = $q_id;
    $question_no++;
  }
  $result->close();

  header('Content-type: application/octet-stream');
  header("Content-Disposition: attachment; filename=" . $paper_title . ".csv");

  $log_array = array();
  $hits = 0;
  $user_no = 0;
  // Capture the log data first.
  $result = $mysqli->prepare("SELECT DISTINCT sid.student_id, users.username, title, surname, initials, grade, gender, started, log4.q_id, rating FROM (log4, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log4.q_id=questions.q_id AND q_paper=? AND users.id=log4.userID AND (users.roles='Student' OR users.roles='graduate') AND grade LIKE ? AND started>=? AND started<=?");
  $result->bind_param('isss', $paperID, $_GET['repdegree'], $_GET['startdate'], $_GET['enddate']);
  $result->execute();
  $result->bind_result($user_ID, $username, $title, $surname, $initials, $grade, $gender, $started, $q_id, $rating);
  while ($row = $result->fetch()) {
    $log_array[$user_ID]['student_id'] = $user_ID;
    $log_array[$user_ID]['username'] = $username;
    $log_array[$user_ID][$q_id] = $rating;
    $log_array[$user_ID]['course'] = $grade;
    $log_array[$user_ID]['started'] = $started;
    $log_array[$user_ID]['title'] = $title;
    $log_array[$user_ID]['surname'] = $surname;
    $log_array[$user_ID]['initials'] = $initials;
    $log_array[$user_ID]['gender'] = $gender;
    $user_no++;
  }
  $result->close();
  
  //echo "SELECT student_id, overall_rating, numeric_score, feedback, log4_overall.year, title, surname, initials FROM (log4_overall, sid, users) WHERE log4_overall.examinerID=users.id AND log4_overall.userID=sid.userID AND q_paper=$paperID AND started>='" . $_GET['startdate'] . "' AND started<='" . $_GET['enddate'] . "'<br />";
  $result = $mysqli->prepare("SELECT student_id, overall_rating, numeric_score, feedback, log4_overall.year, title, surname, initials FROM (log4_overall, sid, users) WHERE log4_overall.examinerID=users.id AND log4_overall.userID=sid.userID AND q_paper=? AND started>=? AND started<=?");
  $result->bind_param('iss', $paperID, $_GET['startdate'], $_GET['enddate']);
  $result->execute();
  $result->bind_result($user_ID, $overall_rating, $numeric_score, $feedback, $year, $title, $surname, $initials);
  while ($row = $result->fetch()) {
    $log_array[$user_ID]['year'] = $year;
    $log_array[$user_ID]['examiner'] = $title . ' ' .  $initials . ' ' . $surname;
    $log_array[$user_ID]['feedback'] = $feedback;
    $log_array[$user_ID]['numeric_score'] = $numeric_score;
  }
  $result->close();


  $row_written = 0;
  foreach ($log_array as $individual) {
    $tmp_user_ID = $individual['username'];
    // Write out the headings.
    if ($row_written == 0) {
      // Only output personal data if assessment, do not show if survey.
      echo 'OSCE Station,Examiner,Gender,Title,Surname,Initials,Username,Student ID,Course,Year,Date';
      for ($i=0; $i<$question_no; $i++) {
        echo ',Q' . ($i+1);
      }
      echo ",Overall Score,Feedback\n";
    }
    // Write out the raw data.
    echo $paper_title . ',' . $individual['examiner'] . ',' . $individual['gender'] . ',' . $individual['title'] . ',' . $individual['surname'] . ',' . $individual['initials'] . ',' . $individual['username'] . ',' . $individual['student_id'] . ',' . $individual['course'] . ',' . $individual['year'] . ',' . $individual['started'];
    for ($i=0; $i<$question_no; $i++) {
      $tmp_question_ID = $paper_buffer[$i]['ID'];
      echo ',' . $individual[$tmp_question_ID];
    }
    echo ',' . $individual['numeric_score'] . ',' . $individual['feedback'];
    echo "\n";
    $row_written++;
  }
  $mysqli->close();
?>