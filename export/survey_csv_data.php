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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

$paper_id = $_GET['paperID'];
$rep_year = (empty($_GET['repyear'])) ? '%' : $_GET['repyear'];
$rep_course = (empty($_GET['repcourse'])) ? '%' : $_GET['repcourse'];

// Capture the paper makeup.
$paper_buffer = array();
$question_no = 0;

$old_q_id = 0;
$option_no = 0;

$stmt = $mysqli->prepare("SELECT paper_title, q_id, q_type, screen, id_num, score_method FROM (properties, papers, questions, options) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND papers.paper=? AND q_type!='info' AND questions.q_id=options.o_id ORDER BY screen, display_pos, id_num");
$stmt->bind_param('i', $paper_id);
$stmt->execute();
$stmt->bind_result($paper_title, $q_id, $q_type, $screen, $id_num, $score_method);
while($stmt->fetch()) {
  if ($old_q_id != $q_id) {
    if ($old_q_id > 0) {
      $paper_buffer[$question_no]['ID'] = $old_q_id;
      $paper_buffer[$question_no]['type'] = $old_q_type;
      $paper_buffer[$question_no]['screen'] = $old_screen;
      $paper_buffer[$question_no]['option_no'] = $option_no;
      $paper_buffer[$question_no]['score_method'] = $old_score_method;
      $question_no++;
      $option_no = 0;
    }
  }
  $old_q_id = $q_id;
  $old_q_type = $q_type;
  $old_screen = $screen;
  $old_score_method = $score_method;
  $option_no++;
}
$stmt->close();
$paper_buffer[$question_no]['ID'] = $old_q_id;
$paper_buffer[$question_no]['type'] = $old_q_type;
$paper_buffer[$question_no]['screen'] = $old_screen;
$paper_buffer[$question_no]['option_no'] = $option_no;
$paper_buffer[$question_no]['score_method'] = $old_score_method;
$question_no++;


header('Pragma: public');
header('Content-type: application/octet-stream');
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper_title) . ".csv");

$user_no = 0;

$stmt = $mysqli->prepare("SELECT COUNT(question) AS number_of_questions FROM (papers, questions) WHERE papers.question=questions.q_id AND q_type!='info' AND paper=?");
$stmt->bind_param('i', $paper_id);
$stmt->execute();
$stmt->bind_result($number_of_questions);
$stmt->fetch();
$stmt->close();


$exclude = '';
if ($_GET['complete'] == 1) {
  $stmt = $mysqli->prepare("SELECT userID, COUNT(id) AS answer_no FROM log3 WHERE q_paper=? AND started>=? AND started<=? GROUP BY userID");
  $stmt->bind_param('iss', $paper_id, $_GET['startdate'], $_GET['enddate']);
  $stmt->execute();
  $stmt->bind_result($uID, $answer_no); //TODO replaced $userID with $uID
  while($stmt->fetch()) {
    if ($answer_no < $number_of_questions or $answer_no > $number_of_questions) {
      // log_metadata aliased as lm in queries below for brevity
      $exclude .= " AND lm.userID != $uID";
    }
  }
  $stmt->close();
}

$log_array = array();
$hits = 0;

$sql = <<< SQL
SELECT DISTINCT sid.student_id, u.username, u.title, u.surname, u.initials, u.grade,
u.gender, lm.year, lm.started, l.q_id, l.user_answer, l.screen
FROM log3 l INNER JOIN log_metadata lm ON l.metadataID = lm.id
INNER JOIN users u ON lm.userID = u.id
LEFT JOIN sid ON u.id = sid.userID
WHERE lm.paperID = ?
AND lm.year LIKE ?
AND (u.roles = 'Student' OR u.roles = 'graduate')$exclude
AND u.grade LIKE ?
AND lm.started >= ? AND lm.started <= ?
SQL;
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('issss', $paper_id, $rep_year, $rep_course, $_GET['startdate'], $_GET['enddate']);
$stmt->execute();
$stmt->bind_result($student_id, $username, $title, $surname, $initials, $grade, $gender, $year, $started, $q_id, $user_answer, $screen);
while($stmt->fetch()) {
  $log_array[$username][$screen][$q_id] = $user_answer;
  $log_array[$username]['student_id'] = $student_id;
  $log_array[$username]['username'] = $username;
  $log_array[$username]['course'] = $grade;
  $log_array[$username]['year'] = $year;
  $log_array[$username]['started'] = $started;
  $log_array[$username]['title'] = $title;
  $log_array[$username]['surname'] = $surname;
  $log_array[$username]['initials'] = $initials;
  $log_array[$username]['gender'] = $gender;
  $user_no++;
}
$stmt->close();

$row_written = 0;
foreach ($log_array as $individual) {
  $tmp_user_ID = $individual['username'];
  // Write out the headings.
  if ($row_written == 0) {
    // Only output personal data if assessment, do not show if survey.
    echo 'Gender,Student ID,Course,Year,Submitted,';
    for ($i=0; $i<$question_no; $i++) {
      $tmp_question_ID = $paper_buffer[$i]['ID'];
      $tmp_screen = $paper_buffer[$i]['screen'];
      if ($i>0) echo ',';
      switch ($paper_buffer[$i]['type']) {
        case 'blank':
          $sections = substr_count($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],'|');
          for ($sec=1; $sec<=$sections; $sec++) {
            if ($sec > 1) echo ',';
            echo 'Q' . ($i+1) . '.' . $sec;
          }
          break;
        case 'extmatch':
          $sections = substr_count($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],'|') + 1;
          for ($sec=1; $sec<=$sections; $sec++) {
            if ($sec > 1) echo ',';
            echo 'Q' . ($i+1) . '.' . $sec;
          }
          break;
        case 'matrix':
          $sections = substr_count($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],'|') + 1;
          for ($sec=1; $sec<=$sections; $sec++) {
            if ($sec > 1) echo ',';
            echo 'Q' . ($i+1) . '.' . $sec;
          }
          break;
        case 'rank':
          $sections = substr_count($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],',') + 1;
          for ($sec=1; $sec<=$sections; $sec++) {
            if ($sec > 1) echo ',';
            echo 'Q' . ($i+1) . '.' . $sec;
          }
          break;
        case 'dichotomous':
          $sections = strlen($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          for ($sec=1; $sec<=$sections; $sec++) {
            if ($sec > 1) echo ',';
            echo 'Q' . ($i+1) . '.' . $sec;
          }
          break;
        case 'mrq':
          $sections = $paper_buffer[$i]['option_no'];
          for ($sec=1; $sec<=$sections; $sec++) {
            if ($sec > 1) echo ',';
            echo 'Q' . ($i+1) . '.' . $sec;
          }
          if ($paper_buffer[$i]['score_method'] == 'other') echo ',Q' . ($i+1) . '.' . $sec;
          break;
        default:
          echo 'Q' . ($i+1);
          break;
      }
    }
    echo "\n";
  }
  // Write out the raw data.
  echo $individual['gender'] . ',' . $individual['student_id'] . ',' . $individual['course'] . ',' . $individual['year'] . ',' . $individual['started'] . ',';
  for ($i=0; $i<$question_no; $i++) {
    $tmp_question_ID = $paper_buffer[$i]['ID'];
    $tmp_screen = $paper_buffer[$i]['screen'];
    if ($i>0) echo ',';
    if(isset($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID])) {
      switch ($paper_buffer[$i]['type']) {
        case 'blank':
          $log_array[$tmp_user_ID][$tmp_question_ID] = $log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID];
          $tmp_answers = str_replace('|',',',$log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          echo substr($tmp_answers,1);
          break;
        case 'extmatch':
          $log_array[$tmp_user_ID][$tmp_question_ID] = $log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID];
          $tmp_answers = str_replace('|',',',$log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          echo $tmp_answers;
          break;
        case 'matrix':
          $log_array[$tmp_user_ID][$tmp_question_ID] = substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],1);
          $tmp_answers = str_replace('|',',',$log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          echo $tmp_answers;
          break;
        case 'rank':
          $buffer = $log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID];
          $buffer = str_replace('9999','',$buffer);
          $buffer = str_replace('9990','n/a',$buffer);
          echo $buffer;
          break;
        case 'hotspot':
          echo $log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID][0];
          break;
        case 'dichotomous':
        case 'mrq':
          $chars = $paper_buffer[$i]['option_no'];
          for ($char_pos=0; $char_pos<$chars; $char_pos++) {
            if ($char_pos > 0) echo ',';
            echo substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID], $char_pos, 1);
          }
          if ($paper_buffer[$i]['score_method'] == 'other') {
            if (substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID], $char_pos, 1) == 'n') {
              echo ',n';
            } else {
              echo ',' . substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID], $char_pos+1);
            }
          }
          break;
        case 'textbox':
          $tmp_data = trim($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          $tmp_data = preg_replace("/(\r\n|\n|\r)/", "", $tmp_data);
          $tmp_data = str_replace('"',"'",$tmp_data);

          if (substr($tmp_data,0,1) == '-') $tmp_data = trim(substr($tmp_data,1));
          echo '"' . $tmp_data . '"';
          break;
        default:
          echo str_replace('u','',$log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          break;
      }
    }
  }
  echo "\n";
  $row_written++;
}
$mysqli->close();
?>