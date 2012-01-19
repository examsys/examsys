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
  require '../include/demo_replace.inc';
  if (strpos($userroles,'Demo') !== false) {
    $demo = true;
  } else {
    $demo = false;
  }

  // Get any questions to exclude.
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($q_id, $parts);
  while ($result->fetch()) {
    $excluded[$q_id] = $parts;
  }
  $result->close();

  // Capture the paper makeup.
  $paper_buffer = array();
  $question_no = 0;
  $old_q_id = -1;
  $part = 0;
  $old_correct = '';
  
  $result = $mysqli->prepare("SELECT paper_title, q_id, q_type, paper_type, screen, correct, option_text, score_method FROM (papers, questions, properties, options) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND questions.q_id=options.o_id AND papers.paper=? AND q_type!='info' ORDER BY screen, display_pos, id_num");
  $result->bind_param('i',$_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_title, $q_id, $q_type, $paper_type, $screen, $correct, $option_text, $score_method);
  while ($result->fetch()) {
    if ($old_q_id != $q_id and $old_q_id != -1) {
      $part = 0;
      $paper_buffer[$question_no]['ID'] = $old_q_id;
      $paper_buffer[$question_no]['type'] = $old_q_type;
      $paper_buffer[$question_no]['screen'] = $old_screen;
      $paper_buffer[$question_no]['correct'] = $old_correct;
      $paper_buffer[$question_no]['score_method'] = $old_score_method;
      $question_no++;
      if ($q_type != 'extmatch' and $q_type != 'matrix') {
        $old_correct = ',' . $correct;
      }
      if ($q_type == 'blank') {
        $old_correct = '';
        $split1 = explode('[blank]',$option_text);
        for ($i=1; $i<count($split1); $i++) {
          $split2 = explode(',', substr($split1[$i],0,strpos($split1[$i],'[/blank]')));
          $old_correct .= ',' . $split2[0];
        }
      }
    } else {
      if ($q_type == 'mcq' or $q_type == 'calculation') {
        $old_correct = ',' . $correct;
      } elseif ($q_type != 'extmatch' and $q_type != 'matrix') {
        $old_correct .= ',' . $correct;
      } else {
        $old_correct = ',' . str_replace('|',",",$correct);
        if (substr($old_correct,-1,1) == ',') $old_correct = substr($old_correct,0,strlen($old_correct)-1);
      }
    }
    $old_q_id = $q_id;
    $old_q_type = $q_type;
    $old_screen = $screen;
    $old_score_method = $score_method;
    $part++;
  }
  $result->close();
  $paper_buffer[$question_no]['ID'] = $old_q_id;
  $paper_buffer[$question_no]['type'] = $old_q_type;
  $paper_buffer[$question_no]['screen'] = $old_screen;
  $paper_buffer[$question_no]['correct'] = $old_correct;
  $paper_buffer[$question_no]['score_method'] = $old_score_method;
  $question_no++;

  header('Content-type: application/octet-stream');
  header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper_title) . ".csv");

  $user_no = 0;
  $result = $mysqli->prepare("SELECT COUNT(question) AS question_no FROM (papers, questions) WHERE papers.question=questions.q_id AND q_type!='info' AND paper=?");
  $result->bind_param('i',$_GET['paperID']);
  $result->execute();
  $result->bind_result($number_of_questions);
  $result->fetch();
  $result->close();

  $exclude = '';
  //if ($_GET['complete'] == 1) {
  //  $result = $mysqli->prepare("SELECT userID, COUNT(id) AS answer_no FROM log WHERE q_paper=? AND started>=? AND started<=? GROUP BY userID");
  //  $result->bind_param('iss',$_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
  //  $result->execute();
  //  $result->bind_result($tmp_userID, $answer_no);
  //  while ($result->fetch()) {
  //    if ($answer_no < $number_of_questions or $answer_no > $number_of_questions) {
  //      $exclude .= ' AND log.userID != ' . $tmp_userID;
  //    }
  //  }
  //  $result->close();
  //}

  // Get order of the class.
  $student_list = '';
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT log0.userID, sum(mark) AS total_mark FROM log0,log_metadata WHERE log0.userID = log_metadata.userID AND log0.q_paper = log_metadata.paperID AND log0.started = log_metadata.started AND q_paper=? AND log_metadata.started>=? AND log_metadata.started<=? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE '%staff%' AND student_grade NOT LIKE '%nhs%' GROUP BY userID, q_paper, log_metadata.started) UNION ALL (SELECT log1.userID, sum(mark) AS total_mark FROM log1,log_metadata WHERE log1.userID = log_metadata.userID AND log1.q_paper = log_metadata.paperID AND log1.started = log_metadata.started AND q_paper=? AND log_metadata.started>=? AND log_metadata.started<=? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE '%staff%' AND student_grade NOT LIKE '%nhs%' GROUP BY log_metadata.userID, q_paper, log_metadata.started) ORDER BY total_mark " . $_GET['direction']);
    $result->bind_param('ississ', $_GET['paperID'], $_GET['startdate'], $_GET['enddate'], $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
  } else {
    $result = $mysqli->prepare("SELECT log$paper_type.userID, sum(mark) AS total_mark FROM log$paper_type,log_metadata WHERE log$paper_type.userID = log_metadata.userID AND log$paper_type.q_paper = log_metadata.paperID AND log$paper_type.started = log_metadata.started AND  q_paper=? AND DATE_ADD(log_metadata.started, INTERVAL 2 MINUTE)>=? AND log_metadata.started<=? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE '%staff%' AND student_grade NOT LIKE '%nhs%' GROUP BY userID, q_paper, log_metadata.started ORDER BY total_mark " . $_GET['direction']);
    $result->bind_param('iss', $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
  }
  $result->execute();
  $result->bind_result($tmp_userID, $total_mark);
  $result->store_result();
  $user_no = round(($result->num_rows/100)*$_GET['percent']);
  $student_no = 0;
  while ($result->fetch() and $student_no < $user_no) {
    if ($student_list == '') {
      $student_list = $tmp_userID;
    } else {
      $student_list .= ',' . $tmp_userID;
    }
    $student_no++;
  }
  $result->free_result();
  $result->close();
  
  $log_array = array();
  $hits = 0;
  $rowID = 0;
  // Capture the log data.
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT DISTINCT sid.student_id, username, log0.userID, title, surname, first_names, grade, gender, log_metadata.year, log_metadata.started, log0.q_id, user_answer, q_type, screen FROM (log0, log_metadata, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log0.userID = log_metadata.userID AND log0.q_paper = log_metadata.paperID AND log0.started = log_metadata.started AND log0.q_id=questions.q_id AND log0.userID IN ($student_list) AND q_paper=? AND  users.id=log0.userID AND (users.roles='Student' OR users.roles='graduate')$exclude AND grade LIKE ? AND log_metadata.started>=? AND log_metadata.started<=?) UNION ALL (SELECT DISTINCT sid.student_id, username, log1.userID, title, surname, first_names, grade, gender, log_metadata.year, log_metadata.started, log1.q_id, user_answer, q_type, screen FROM (log1, log_metadata, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log1.userID = log_metadata.userID AND log1.q_paper = log_metadata.paperID AND log1.started = log_metadata.started AND log1.q_id=questions.q_id AND log1.userID IN ($student_list) AND q_paper=? AND users.id=log1.userID AND (users.roles='Student' OR users.roles='graduate')$exclude AND grade LIKE ? AND log_metadata.started>=? AND log_metadata.started<=?) ORDER BY surname, first_names, started, userID");
    $result->bind_param('isssisss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate'], $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
  } else {
    $result = $mysqli->prepare("SELECT DISTINCT sid.student_id, username, log$paper_type.userID, title, surname, first_names, grade, gender, log_metadata.year, log_metadata.started, log$paper_type.q_id, user_answer, q_type, screen FROM (log$paper_type, log_metadata, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log_metadata.userID = log$paper_type.userID AND log_metadata.paperID = log$paper_type.q_paper AND log_metadata.started = log$paper_type.started AND log$paper_type.q_id=questions.q_id AND log$paper_type.userID IN ($student_list) AND q_paper=? AND users.id=log$paper_type.userID AND (users.roles='Student' OR users.roles='graduate')$exclude AND grade LIKE ? AND DATE_ADD(log_metadata.started, INTERVAL 2 MINUTE)>=? AND log_metadata.started<=? ORDER BY surname, first_names, log_metadata.started, userID");
    $result->bind_param('isss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
  }

  $result->execute();
  $result->bind_result($student_id, $username, $userID, $title, $surname, $first_names, $grade, $gender, $year, $started, $question_ID, $user_answer, $q_type, $screen);
  $old_username = '';
  while ($result->fetch()) {
    if ($old_username != $username or $old_started != $started) {
      $rowID++;
    }
    $log_array[$rowID][$screen][$question_ID] = $user_answer;
    $log_array[$rowID]['student_id'] = demo_replace_number($student_id,$demo);
    $log_array[$rowID]['userID'] = $userID;
    $log_array[$rowID]['username'] = $username;
    $log_array[$rowID]['course'] = $grade;
    $log_array[$rowID]['year'] = $year;
    $log_array[$rowID]['started'] = $started;
    $log_array[$rowID]['title'] = $title;
    $log_array[$rowID]['surname'] = demo_replace($surname,$demo);
    $log_array[$rowID]['first_names'] = demo_replace($first_names,$demo);
    $log_array[$rowID]['gender'] = $gender;
    
    $user_no++;
    $old_username = $username;
    $old_started = $started;
  }
  $result->close();

  $row_written = 0;
  foreach ($log_array as $individual) {
    $tmp_user_ID = $individual['userID'];
    // Write out the headings.
    if ($row_written == 0) {
      echo $string['gender'] . ',' . $string['title'] . ',' . $string['surname'] . ',' . $string['firstnames'] . ',' . $string['studentid'] . ',' . $string['course'] . ',' . $string['year'] . ',' . $string['started'];
      for ($i=0; $i<$question_no; $i++) {
        $tmp_question_ID = $paper_buffer[$i]['ID'];
        $tmp_screen = $paper_buffer[$i]['screen'];
        if (array_key_exists($tmp_question_ID,$excluded)) {
          $tmp_exclude = $excluded[$tmp_question_ID];
        } else {
          $tmp_exclude = '0000000000000000000000000000000000000000';
        }
        switch ($paper_buffer[$i]['type']) {
          case 'blank':
            for ($sec=1; $sec<=substr_count($paper_buffer[$i]['correct'],','); $sec++) {
              if (substr($tmp_exclude,$sec-1,1) == '0') echo ',Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'extmatch':
            $correct_parts = explode(',',$paper_buffer[$i]['correct']);
            $partID = 0;
            for ($sec=1; $sec<substr_count($paper_buffer[$i]['correct'],',') + 1; $sec++) {
              if (substr($tmp_exclude,$partID,1) == '0') echo ',Q' . ($i+1) . '.' . $sec;
              $partID += substr_count($correct_parts[$sec],'$') + 1;
            }
            break;
          case 'matrix':
            for ($sec=1; $sec<substr_count($paper_buffer[$i]['correct'],',') + 1; $sec++) {
              if (substr($tmp_exclude,$sec-1,1) == '0') echo ',Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'rank':
            for ($sec=1; $sec<=substr_count($paper_buffer[$i]['correct'],','); $sec++) {
              if (substr($tmp_exclude,$sec-1,1) == '0') echo ',Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'dichotomous':
            for ($sec=1; $sec<=substr_count($paper_buffer[$i]['correct'],','); $sec++) {
              if (substr($tmp_exclude,$sec-1,1) == '0') echo ',Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'mrq':
            for ($sec=1; $sec<=substr_count($paper_buffer[$i]['correct'],','); $sec++) {
	      if (!isset($excluded[$tmp_question_ID])) echo ',Q' . ($i+1) . '.' . $sec;
            }
            if ($paper_buffer[$i]['score_method'] == 'other') echo ',Q' . ($i+1) . '.other';
            break;
          case 'calculation':
            if (!isset($excluded[$tmp_question_ID])) {
              echo ',Q' . ($i+1) . ':user';
              echo ',Q' . ($i+1) . ':correct';
              echo ',Q' . ($i+1) . ':variables';
            }
            break;
          default:
            if (!isset($excluded[$tmp_question_ID])) echo ',Q' . ($i+1);
            break;
        }
      }
      echo "\n";
      // Write out correct answers line.
      echo $string['correctanswers'] . ',,,,,,,';
      for ($i=0; $i<$question_no; $i++) {
        $tmp_question_ID = $paper_buffer[$i]['ID'];
        $tmp_screen = $paper_buffer[$i]['screen'];
        if (array_key_exists($tmp_question_ID,$excluded)) {
          $tmp_exclude = $excluded[$tmp_question_ID];
        } else {
          $tmp_exclude = '0000000000000000000000000000000000000000';
        }
        switch ($paper_buffer[$i]['type']) {
          case 'blank':
            $correct_parts = explode(',',$paper_buffer[$i]['correct']);
            for ($partID=1; $partID<count($correct_parts); $partID++) {
              if (substr($tmp_exclude,$partID-1,1) == '0') echo ',' . $correct_parts[$partID];
            }
            break;
          case 'extmatch':
            $correct_parts = explode(',',$paper_buffer[$i]['correct']);
            $partID=1;
            for ($outer=1; $outer<=count($correct_parts)-1; $outer++) {
              if (substr($tmp_exclude,$partID-1,1) == '0') echo ',' . $correct_parts[$outer];
              $partID += substr_count($correct_parts[$outer],'$') + 1;
            }
            break;
          case 'matrix':
            $correct_parts = explode(',',$paper_buffer[$i]['correct']);
            for ($partID=1; $partID<count($correct_parts); $partID++) {
              if (substr($tmp_exclude,$partID-1,1) == '0') echo ',' . $correct_parts[$partID];
            }
            break;
          case 'rank':
            $paper_buffer[$i]['correct'] = str_replace('0','N/A',$paper_buffer[$i]['correct']);
            if (!isset($excluded[$tmp_question_ID])) echo $paper_buffer[$i]['correct'];
            break;
          case 'hotspot':
            if (!isset($excluded[$tmp_question_ID])) echo ',';
            break;
          case 'labelling':
            if (!isset($excluded[$tmp_question_ID])) echo ',';
            break;
          case 'dichotomous':
            $correct_parts = explode(',',$paper_buffer[$i]['correct']);
            for ($partID=1; $partID<count($correct_parts); $partID++) {
              if (substr($tmp_exclude,$partID-1,1) == '0') echo ',' . $correct_parts[$partID];
            }
            break;
          case 'mrq':
            if (!isset($excluded[$tmp_question_ID])) echo $paper_buffer[$i]['correct'];
            break;
          case 'textbox':
            if (!isset($excluded[$tmp_question_ID])) echo ',';
            break;
          case 'calculation':
            echo ',' . $paper_buffer[$i]['correct'] . ',';
            break;
          case 'sct':
            if (!isset($excluded[$tmp_question_ID])) {
              $correct = '';
              $parts = explode(',', $paper_buffer[$i]['correct']);
              $max_correct = 0;
              for ($partID=1; $partID<count($parts); $partID++) {
                if ($parts[$partID] > $max_correct) {
                  $max_correct = $parts[$partID];
                  $correct = $partID;
                }            
              }
              echo ',' . $correct;
            }
            break;
          default:
            if (!isset($excluded[$tmp_question_ID])) echo $paper_buffer[$i]['correct'];
            break;
        }
      }
      echo "\n";
    }
    // Write out the raw data.
    echo $individual['gender'] . ',' . $individual['title'] . ',' . $individual['surname'] . ',' . $individual['first_names'] . ',' . $individual['student_id'] . ',' . $individual['course'] . ',' . $individual['year'] . ',' . $individual['started'];
    for ($i=0; $i<$question_no; $i++) {
      $tmp_question_ID = $paper_buffer[$i]['ID'];
      $tmp_screen = $paper_buffer[$i]['screen'];
      if (array_key_exists($tmp_question_ID,$excluded)) {
        $tmp_exclude = $excluded[$tmp_question_ID];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      switch ($paper_buffer[$i]['type']) {
        case 'blank':
          $tmp_answers = explode('|',$individual[$tmp_screen][$tmp_question_ID]);
          $correct_parts = explode(',',$paper_buffer[$i]['correct']);
          for ($partID=1; $partID<count($correct_parts); $partID++) {
            if (substr($tmp_exclude,$partID-1,1) == '0') echo ',' . $tmp_answers[$partID];
          }
          break;
        case 'calculation':
          if (isset($individual[$tmp_screen][$tmp_question_ID])) {
            $answer_parts = explode('|',$individual[$tmp_screen][$tmp_question_ID]);
          } else {
            $answer_parts = array('u','u','u');
          }
          if (!isset($excluded[$tmp_question_ID])) {
            echo ',"' . $answer_parts[0] . '",' . $answer_parts[1] . ',"' . $answer_parts[2] . '"';
          }
          break;
        case 'dichotomous':
          $correct_parts = explode(',',$paper_buffer[$i]['correct']);
          for ($partID=0; $partID<count($correct_parts)-1; $partID++) {
            if (substr($tmp_exclude,$partID,1) == '0') {
              echo ',';
              if(isset($individual[$tmp_screen][$tmp_question_ID])) {
                echo substr($individual[$tmp_screen][$tmp_question_ID],$partID,1);
              } else {
                echo 'u';
              }
            }
          }
          break;
        case 'extmatch':
          $answer_parts = explode('|',$individual[$tmp_screen][$tmp_question_ID]);
          $correct_parts = explode(',',$paper_buffer[$i]['correct']);
          $partID = 0;
          for ($outer=1; $outer<=count($correct_parts)-1; $outer++) {
            if (substr($tmp_exclude,$partID,1) == '0') echo ',' . $answer_parts[$outer-1];
            $partID += substr_count($correct_parts[$outer],'$') + 1;
          }
          break;
        case 'matrix':
          $answer_parts = explode('|',$individual[$tmp_screen][$tmp_question_ID]);
          $correct_parts = explode(',',$paper_buffer[$i]['correct']);
          for ($partID=0; $partID<count($correct_parts)-1; $partID++) {
            if (substr($tmp_exclude,$partID,1) == '0') {
              echo ',';
              if($answer_parts[$partID] != '') {
                echo $answer_parts[$partID];
              } else {
                echo 'u';
              }
            }
          }
          break;
        case 'rank':
          $individual[$tmp_screen][$tmp_question_ID] = str_replace('0','N/A',$individual[$tmp_screen][$tmp_question_ID]);
          if (!isset($excluded[$tmp_question_ID])) echo ',' . $individual[$tmp_screen][$tmp_question_ID];
          break;
        case 'hotspot':
          if (!isset($excluded[$tmp_question_ID])) {
            echo ',';
            if(isset($individual[$tmp_screen][$tmp_question_ID][0])) {
              echo $individual[$tmp_screen][$tmp_question_ID][0];
            } else {
              echo 'u';
            }
          } 
          break;
        case 'dichotomous':
        case 'mrq':
          for ($char_pos=0; $char_pos<substr_count($paper_buffer[$i]['correct'],','); $char_pos++) {
            echo ',"' . substr($individual[$tmp_screen][$tmp_question_ID], $char_pos, 1) , '"';
          }
          $char_pos = substr_count($paper_buffer[$i]['correct'],',') + 1;
          if ($paper_buffer[$i]['score_method'] == 'other') echo ',' . substr($individual[$tmp_screen][$tmp_question_ID], $char_pos+1);
          break;
        case 'textbox':
          if(isset($individual[$tmp_screen][$tmp_question_ID])) {
            $tmp_data = trim($individual[$tmp_screen][$tmp_question_ID]);
          } else {
            $tmp_data = '<unanswered>';
          }
          $tmp_data = preg_replace("/(\r\n|\n|\r)/", "", $tmp_data);
          $tmp_data = str_replace('"',"'",$tmp_data);
          
          if (substr($tmp_data,0,1) == '-') $tmp_data = trim(substr($tmp_data,1));
          echo ',"' . $tmp_data . '"';
          break;
        default:
          if (!isset($excluded[$tmp_question_ID])) {
            echo ',"';
            if (isset($individual[$tmp_screen][$tmp_question_ID])) {
              echo $individual[$tmp_screen][$tmp_question_ID];
            } else {
              echo 'u';
            }
            echo '"';
          }
          break;
      }
    }
    echo "\n";
    $row_written++;
  }
  $mysqli->close();
?>