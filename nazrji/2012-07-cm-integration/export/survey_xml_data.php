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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
  
  // Capture the paper makeup.
  $paper_buffer = array();
  $question_no = 0;

  $result = $mysqli->prepare("SELECT paper_title, q_id, q_type, paper_type FROM (papers, questions, properties) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND papers.paper=? AND q_type!='info' ORDER BY screen, display_pos");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper, $q_id, $q_type, $paper_type);
  while ($row = $result->fetch()) {
    $paper_buffer[$question_no]['ID'] = $q_id;
    $paper_buffer[$question_no]['type'] = $q_type;
    $paper_type = $paper_type;
    $question_no++;
  }
  $result->close();
  
  header('Content-type: text/xml');
  header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".xml");

  $log_array = array();
  $hits = 0;
  $exclude = '';
  if ($_GET['complete'] == 1) {
    $result = $mysqli->prepare("SELECT COUNT(question) AS question_no FROM papers WHERE paper=?");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($number_of_questions);
    $result->fetch();
    $result->close();
  
    $result = $mysqli->prepare("SELECT userID, COUNT(id) AS answer_no FROM log$paper_type WHERE q_paper=? AND started>=? AND started<=? GROUP BY userID");
    $result->bind_param('iss', $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
    $result->bind_result($tmp_username, $answer_no);
    while ($row = $result->fetch()) {
      if ($answer_no < $number_of_questions or $answer_no > $number_of_questions) {
        $exclude .= ' AND log$paper_type.userID != "' . $tmp_username . '"';
      }
    }
    $result->close();
  }

  // Capture the log data first.
  $user_no = 0;
  $result = $mysqli->prepare("SELECT log3.q_id, grade, DATE_FORMAT(log_metadata.started,\"%d/%m/%Y %T\") AS started, log_metadata.year, surname, initials, title, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, log3.userID FROM (log3, log_metadata, questions, users) WHERE log3.q_paper=log_metadata.paperID AND log3.userID=log_metadata.userID AND log3.started=log_metadata.started AND log3.q_id=questions.q_id AND q_paper=? AND log_metadata.year LIKE ? AND users.id=log3.userID AND grade LIKE ? AND (users.roles='Student' OR users.roles='graduate')$exclude AND log_metadata.started>=? AND log_metadata.started<=? ORDER BY surname, initials");
  $result->bind_param('issss', $_GET['paperID'], $_GET['repyear'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
  $result->execute();
  $result->bind_result($question_ID, $grade, $started, $year, $surname, $initials, $title, $user_answer, $q_type, $user_ID);
  while ($row = $result->fetch()) {
    $log_array[$user_ID][$question_ID] = $user_answer;
    $log_array[$user_ID]['username'] = $user_ID;
    $log_array[$user_ID]['course'] = $grade;
    $log_array[$user_ID]['year'] = $year;
    $log_array[$user_ID]['started'] = $started;
    $log_array[$user_ID]['title'] = $title;
    $log_array[$user_ID]['surname'] = $surname;
    $log_array[$user_ID]['initials'] = $initials;
    $user_no++;
  }
  $result->close();
  $mysqli->close();

  $row_written = 0;
  echo "<?xml version=\"1.0\"?>\n<document>\n";
  foreach ($log_array as $individual) {
    $tmp_user_ID = $individual['username'];
    // Write out the raw data.
    echo "<user>\n";
    if ($paper_type < 3) {
      echo "<title>" . $individual['title'] . "</title>\n";
      echo "<lastname>" . $individual['surname'] . "</lastname>\n";
      echo "<initials>" . $individual['initials'] . "</initials>\n";
      echo "<username>" . $individual['username'] . "</username>\n";
    }
    echo "<course>" . $individual['course'] . "</course>\n<year>" . $individual['year'] . "</year>\n<submitted>" . $individual['started'] . "</submitted>\n";
    $Qno = 1;
    for ($i=0; $i<$question_no; $i++) {
      $tmp_question_ID = $paper_buffer[$i]['ID'];
      switch ($paper_buffer[$i]['type']) {
        case 'blank':
        case 'matching':
          $sub_part = 1;
          $log_array[$tmp_user_ID][$tmp_question_ID] = $log_array[$tmp_user_ID][$tmp_question_ID];
          $tmp_answers = explode('$',$log_array[$tmp_user_ID][$tmp_question_ID]);
          foreach ($tmp_answers as $individual_answer) {
            echo "<question no=\"$Qno.$sub_part\">$individual_answer</question>\n";
            $sub_part++;          
          }
          break;
        case 'matrix':
          $sub_part = 1;
          if (isset($log_array[$tmp_user_ID][$tmp_question_ID])) {
            $tmp_answers = explode('|',$log_array[$tmp_user_ID][$tmp_question_ID]);
            foreach ($tmp_answers as $individual_answer) { 
              echo "<question no=\"$Qno.$sub_part\">$individual_answer</question>\n";
              $sub_part++;
            }
          }
          break;
        case 'rank':
          $sub_part = 1;
          if (isset($log_array[$tmp_user_ID][$tmp_question_ID])) {
            $tmp_answers = explode(',',$log_array[$tmp_user_ID][$tmp_question_ID]);
            foreach ($tmp_answers as $individual_answer) {
              if ($individual_answer != '') {
                if ($individual_answer == '9990') {
                  echo "<question no=\"$Qno.$sub_part\">n/a</question>\n";
                } else {
                  echo "<question no=\"$Qno.$sub_part\">$individual_answer</question>\n";
                }
                $sub_part++;          
              }
            }
          }
          break;
        case 'hotspot':
          echo "<question no=\"$Qno\">";
          echo $log_array[$tmp_user_ID][$tmp_question_ID][0];
          echo "</question>\n";
          break;
        case 'dichotomous':
        case 'mrq':
          $sub_part = 1;
          $chars = strlen($log_array[$tmp_user_ID][$tmp_question_ID]);
          for ($char_pos=0; $char_pos<$chars; $char_pos++) {
            echo "<question no=\"$Qno.$sub_part\">";
            echo substr($log_array[$tmp_user_ID][$tmp_question_ID], $char_pos, 1);
            echo "</question>\n";
            $sub_part++;
          }
          break;
        case 'textbox':
          echo "<question no=\"$Qno\">";
          if (isset($log_array[$tmp_user_ID][$tmp_question_ID])) {
            $tmp_data = $log_array[$tmp_user_ID][$tmp_question_ID];
            $buffer = '';
            for ($character=0; $character<strlen($tmp_data); $character++) {
              if (ord($tmp_data{$character}) > 31 and ord($tmp_data{$character}) < 127) {
                $buffer .= $tmp_data{$character};
              }
            }
            echo htmlspecialchars($buffer);
          }
          echo "</question>\n";
          break;
        case 'mcq':
        case 'likert':
          echo "<question no=\"$Qno\">";
          if (isset($log_array[$tmp_user_ID][$tmp_question_ID])) {
            echo $log_array[$tmp_user_ID][$tmp_question_ID];
          }
          echo "</question>\n";
          break;
      }
      $Qno++;
    }
    echo "</user>\n";
    $row_written++;
  }
  echo "</document>\n";
?>
