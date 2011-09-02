<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
  
  require '../include/staff_auth.inc';
  require '../include/class_totals.inc';
  
  /*
  function writeUserResults($tmp, &$master_array, &$user_number, $tmp_user_duration, $tmp_submarks) {
    global $userroles;
    
    if (strpos($userroles,'Demo') !== false) $demo = true;
    
    $master_array[$user_number]['name'] = $tmp['name'];
    $master_array[$user_number]['mark'] = $tmp_submarks;
    $total = 0;
    foreach ($tmp_submarks as $individual_mark) $total += $individual_mark;
    $master_array[$user_number]['total'] = $total;
    $master_array[$user_number]['started'] = $tmp['started'];
    $master_array[$user_number]['username'] = $tmp['username'];
    $master_array[$user_number]['degree'] = $tmp['grade'];
    $master_array[$user_number]['year'] = $tmp['year'];
    $master_array[$user_number]['display_started'] = $tmp['display_started'];
    $master_array[$user_number]['title'] = $tmp['title'];
    $master_array[$user_number]['surname'] = demo_replace($tmp['surname'],$demo);
    $master_array[$user_number]['initials'] = demo_replace($tmp['initials'],$demo);
    $master_array[$user_number]['first_names'] = demo_replace($tmp['first_names'],$demo);
    $master_array[$user_number]['student_id'] = demo_replace_number($tmp['student_id'],$demo);
    $master_array[$user_number]['gender'] = $tmp['gender'];
    $master_array[$user_number]['ipaddress'] = $tmp['ipaddress'];
    $master_array[$user_number]['duration'] = $tmp_user_duration;
    $master_array[$user_number]['questions'] = $tmp['questions'];
    $master_array[$user_number]['paper_type'] = $tmp['paper_type'];
    $master_array[$user_number]['visible'] = 1;    // Default to visible unless switched off below.
    $user_number++;
  }

  function getUserMark($q_id, $tmp_user_answer, $tmp_user_mark, $exclude, &$tmp_mark) {
    global $paper_buffer, $user_results;
  
    if ($paper_buffer[$q_id]['q_type'] == 'extmatch' or $paper_buffer[$q_id]['q_type'] == 'matrix') {
      $paper_answers = split("\|",$paper_buffer[$q_id]['correct'][0]);
      $user_answers = split("\|",$tmp_user_answer);
      if (array_key_exists($q_id,$exclude)) {
        $tmp_exclude = $exclude[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      $std_part = 0;
      for ($a=0; $a<count($paper_answers); $a++) {
        if ($paper_answers[$a] != '') {
          $answers_correct = 0;
          if ($paper_buffer[$q_id]['q_type'] == 'extmatch') {
            $sub_paper_answers = split('\$',$paper_answers[$a]);
            $sub_user_answers = split('\$',$user_answers[$a]);
            $exclude_on = true;
            for ($b=0; $b<count($sub_paper_answers); $b++) {
              if (substr($tmp_exclude,$std_part,1) == 0) {
                for ($c=0; $c<count($sub_user_answers); $c++) {
                  if ($sub_paper_answers[$b] == $sub_user_answers[$c]) $answers_correct++;
                }
                $exclude_on = false;
              }
              $std_part++;
            }
            if ($exclude_on == false) $tmp_mark[] = $answers_correct;
          } else {  // Matrix question type
            if (substr($tmp_exclude,$a,1) == '0') {
              if ($paper_answers[$a] == $user_answers[$a+1]) {
                $tmp_mark[] = 1;
              } else {
                $tmp_mark[] = 0;
              }
            }
          }
        }
      }
    } elseif ($paper_buffer[$q_id]['q_type'] == 'blank') {
      $user_answers = split("\|",$tmp_user_answer);
      if (array_key_exists($q_id,$exclude)) {
        $tmp_exclude = $exclude[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      for ($a=0; $a<count($paper_buffer[$q_id]['correct']); $a++) {
        if (substr($tmp_exclude,$a,1) == 0) {
          if ($paper_buffer[$q_id]['correct'][$a] == $user_answers[$a+1]) {
            $tmp_mark[] = 1;
          } else {
            $tmp_mark[] = 0;
          }
        }
      }
    } elseif ($paper_buffer[$q_id]['q_type'] == 'dichotomous') {
      for ($a=0; $a<count($paper_buffer[$q_id]['correct']); $a++) {
        if (array_key_exists($q_id,$exclude)) {
          $tmp_exclude = $exclude[$q_id];
        } else {
          $tmp_exclude = '0000000000000000000000000000000000000000';
        }
        if (substr($tmp_exclude,$a,1) == '0') {
          if ($paper_buffer[$q_id]['correct'][$a] == substr($tmp_user_answer,$a,1)) {
            $tmp_mark[] = 1;
          } else {
            if (substr($tmp_user_answer,$a,1) == 'a' or substr($tmp_user_answer,$a,1) == 'u') {
              $tmp_mark[] = 0; 
            } elseif ($paper_buffer[$q_id]['score_method'] == 'TF_NegativeAbstain' or $paper_buffer[$q_id]['score_method'] == 'YN_NegativeAbstain') {
              $tmp_mark[] = -1;
            } elseif ($paper_buffer[$q_id]['score_method'] == 'TF_NegativeAbstainHalf') {
              $tmp_mark[] = -0.5;
            } else {
              $tmp_mark[] = 0;
            }
          }
        }
      }
    } elseif ($paper_buffer[$q_id]['q_type'] == 'labelling') {
      $user_split1 = split(';',$tmp_user_answer);
      $user_split2 = split('\$',$user_split1[1]);
      if (array_key_exists($q_id,$exclude)) {
        $tmp_exclude = $exclude[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      
      $i = 0;
        
      foreach($paper_buffer[$q_id]['correct'] as $label) {
        $found = false;
        if (substr($tmp_exclude,$i,1) == '0') {
          for ($a=0; $a<count($user_split2); $a+=4) {
            if ($user_split2[$a] == $label['x'] and $user_split2[$a+1] == $label['y']-25 and $user_split2[$a+2] == $label['ans']) $found = true;
          }
          if ($found == true) {
            $tmp_mark[] = 1;
          } else {
            $tmp_mark[] = 0;
          }
          $a=0;
        }
        $i++;
      }
    } else {
      if (!array_key_exists($q_id,$exclude)) {
        $tmp_mark[] = $tmp_user_mark;
      }
    }
  }

  function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
    foreach ($marray as $row) {
      $sortarr[] = $row[$column];
    }
    $sortarr = array_map('strtolower',$sortarr);
    $sort_method = SORT_STRING;
    if ($column == 'mark' or $column == 'duration' or $column == 'total') $sort_method = SORT_NUMERIC;
    if ($sort_order == 'asc') {
      array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
    } else {
      array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
    }
    return $marray;
  }

  // Get any questions to exclude.
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $result->bind_param("i", $_GET['paperID']);
  $result->execute();
  $result->bind_result($q_id, $parts);
  while ($row = $result->fetch()) {
    $excluded[$q_id] = $parts;
  }
  $result->close();
  
  // Capture the paper makeup.
  $paper_buffer = array();
  $question_no = 0;

  // Load the correct answers into 'paper_buffer' array.
  $old_q_id = '';
  $result = $mysqli->prepare("SELECT paper_title, q_id, q_type, paper_type, correct, score_method, option_text FROM (papers, questions, options, properties) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND papers.paper=? AND questions.q_id=options.o_id AND q_type!='info' ORDER BY screen, display_pos, id_num");
  $result->bind_param("i", $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper, $q_id, $q_type, $paper_type, $correct, $score_method, $option_text);
  while ($row = $result->fetch()) {
    if ($q_id != $old_q_id) {
      $option_no = 0;
      $question_id = $q_id;
      $paper_buffer[$question_id]['id'] = $question_id;
      $paper_buffer[$question_id]['q_type'] = $q_type;
      $paper_buffer[$question_id]['score_method'] = $score_method;
      if ($q_type == 'blank') {
        $not_used = ereg("mark=\"([0-9]{1,3})\"",$option_text,$results);
        $blank_details = split("\[blank",$option_text);
        $no_answers = count($blank_details) - 1;
        for ($i=1; $i<=$no_answers; $i++) {
          $blank_details[$i] = ereg_replace(" mark=\"([0-9]{1,3})\"","",$blank_details[$i]);
          $blank_details[$i] = ereg_replace(" size=\"([0-9]{1,3})\"","",$blank_details[$i]);
          $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
          $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
          $answer_list = split(',',$blank_details[$i]);
          $answer_list[0] = str_replace("[/blank]",'',$answer_list[0]);
          if ($score_method == 'textboxes') {
            foreach ($answer_list as $individual_answer) {
              $paper_buffer[$question_id]['correct'][] = html_entity_decode(trim($individual_answer));
            }
          } else {
            $paper_buffer[$question_id]['correct'][] = html_entity_decode(trim($answer_list[0]));
          }
        }
      } elseif ($q_type == 'labelling') {
        $tmp_first_split = split(';', $correct);
        $tmp_second_split = split('\$', $tmp_first_split[8]);
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|') {
            if ($tmp_second_split[$label_no-2] > 219) {
              $paper_buffer[$question_id]['correct'][] = Array('x'=>$tmp_second_split[$label_no-2],'y'=>$tmp_second_split[$label_no-1],'ans'=>trim(substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'))));
            }
          }
        }
      } elseif ($q_type == 'dichotomous') {
        $paper_buffer[$question_id]['correct'][] = $correct;
      } else {
        $paper_buffer[$question_id]['correct'][$option_no] = $correct;
      }
    } else {
      $paper_buffer[$question_id]['correct'][$option_no] = $correct;
    }
    $option_no++;
    $old_q_id = $q_id;
  }
  $result->close();


  $log_array = array();
  $user_no = 0;
  $hits = 0;
  $exclude = '';
  if ($_GET['complete'] == 1) {
    $result = $mysqli->prepare("SELECT COUNT(question) AS question_no FROM papers WHERE paper=?");
    $result->bind_param('i',$_GET['paperID']);
    $result->execute();
    $result->bind_result($number_of_questions);
    $result->fetch();
    $result->close();

    $result = $mysqli->prepare("SELECT username, COUNT(id) AS answer_no FROM log$paper_type WHERE q_paper=? AND started>=? AND started<=? GROUP BY username");
    $result->bind_param('iss',$_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
    $result->bind_result($tmp_username, $answer_no);
    while ($row = $result->fetch()) {
      if ($answer_no < $number_of_questions or $answer_no > $number_of_questions) {
        $exclude .= ' AND log.username != "' . $tmp_username . '"';
      }
    }
    $result->close();
  }

  // Get student's log data.
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT log0.q_id, grade, 0 AS paper_type, screen, duration, started, user_answer, DATE_FORMAT(started,'%d/%m/%Y %H:%i') AS display_started, log0.year, title, surname, initials, first_names, gender, ipaddress, student_id, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, username, mark FROM (log0, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log0.q_id=questions.q_id AND roles='Student' AND q_paper=? AND log0.year LIKE ? AND users.id=log0.userID AND grade LIKE ? AND (users.roles='Student' OR users.roles='graduate')$exclude AND started>=? AND started<=?) UNION ALL (SELECT log1.q_id, grade, 1 AS paper_type, screen, duration, started, user_answer, DATE_FORMAT(started,'%d/%m/%Y %H:%i') AS display_started, log1.year, title, surname, initials, first_names, gender, ipaddress, student_id, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, username, mark FROM (log1, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log1.q_id=questions.q_id AND q_paper=? AND log1.year LIKE ? AND users.id=log1.userID AND grade LIKE ? AND (users.roles='Student' OR users.roles='graduate')$exclude AND started>=? AND started<=?) ORDER BY username, started, screen");
    $result->bind_param('issssissssissss', $_GET['paperID'], $_GET['repyear'], $_GET['repdegree'], $_GET['startdate'], $_GET['enddate'], $_GET['paperID'], $_GET['repyear'], $_GET['repdegree'], $_GET['startdate'], $_GET['enddate'], $_GET['paperID'], $_GET['repyear'], $_GET['repdegree'], $_GET['startdate'], $_GET['enddate']);
   } elseif ($paper_type == '2') {
    $result = $mysqli->prepare("SELECT log$paper_type.q_id, $paper_type AS paper_type, grade, screen, duration, started, user_answer, DATE_FORMAT(started,'%d/%m/%Y %H:%i') AS display_started, log$paper_type.year, title, surname, initials, first_names, gender, ipaddress, student_id, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, username, mark FROM (log$paper_type, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log$paper_type.q_id=questions.q_id AND q_paper=? AND log$paper_type.year LIKE ? AND users.id=log$paper_type.userID AND grade LIKE ? AND (users.roles='Student' OR users.roles='graduate')$exclude AND DATE_ADD(started, INTERVAL 2 MINUTE)>=? AND started<=? ORDER BY username, started, screen");
    $result->bind_param('issss', $_GET['paperID'], $_GET['repyear'], $_GET['repdegree'], $_GET['startdate'], $_GET['enddate']);
  } else {
    $result = $mysqli->prepare("SELECT log$paper_type.q_id, $paper_type AS paper_type, grade, screen, duration, started, user_answer, DATE_FORMAT(started,'%d/%m/%Y %H:%i') AS display_started, log$paper_type.year, title, surname, initials, first_names, gender, ipaddress, student_id, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, username, mark FROM (log$paper_type, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log$paper_type.q_id=questions.q_id AND q_paper=? AND log$paper_type.year LIKE ? AND users.id=log$paper_type.userID AND grade LIKE ? AND (users.roles='Student' OR users.roles='graduate')$exclude AND started>=? AND started<=? ORDER BY username, started, screen");
    $result->bind_param('issss', $_GET['paperID'], $_GET['repyear'], $_GET['repdegree'], $_GET['startdate'], $_GET['enddate']);
  }
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $paper_type, $grade, $screen, $duration, $started, $user_answer, $display_started, $year, $title, $surname, $initials, $first_names, $gender, $ipaddress, $student_id, $user_answer, $q_type, $tmp_username, $mark);

  $user_no = 0;
  $submarks = array();
  $user_duration = 0;
  $tmp_array = array();
  $user_results = array();
  $median = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch()) {
      if ($old_screen != $screen or $old_username != $tmp_username or $old_started != $started) {
        $user_duration += $old_duration;
      }
      if (($old_username != $tmp_username) or ($old_started != $started)) {
        if ($old_username != '') {
          writeUserResults($tmp_array, $user_results, $user_no, $user_duration, $submarks);
          $tmp_array['questions'] = 0;
          $submarks = array();
          $user_duration = 0;
        }
        $tmp_array = array();
        $tmp_array['started'] = $started;
        $tmp_array['username'] = $tmp_username;
        $tmp_array['grade'] = $grade;
        $tmp_array['year'] = $year;
        $tmp_array['display_started'] = $display_started;
        $tmp_array['title'] = $title;
        $tmp_array['name'] = $surname . ',' . $first_names;
        $tmp_array['surname'] = $surname;
        $tmp_array['initials'] = $initials;
        $tmp_array['first_names'] = $first_names;
        $tmp_array['student_id'] = $student_id;
        $tmp_array['gender'] = $gender;
        $tmp_array['ipaddress'] = $ipaddress;
        $tmp_array['duration'] = $user_duration;
        $tmp_array['paper_type'] = $paper_type;
      }
      getUserMark($q_id, $user_answer, $mark, $excluded, $submarks);
      $tmp_array['questions']++;
      $old_username = $tmp_username;
      $old_started = $started;
      $old_duration = $duration;
      $old_screen = $screen;
    }
    writeUserResults($tmp_array, $user_results, $user_no, $user_duration, $submarks);
    
    if ($_GET['percent'] < 100) {
      // Sort by user total order.
      $sortby = 'total';
      $ordering = $_GET['direction'];
      $user_results = array_csort($user_results,$sortby,$ordering);
      $cohort_size = round(($user_no/100)*$_GET['percent']);
      // Set visible/invisible flag where necessary.
      for ($i=0; $i<$user_no; $i++) {
        if ($i >= $cohort_size) {
          $user_results[$i]['visible'] = 0;
        }
      }
    }
    
    // Sort the arrays
    $sortby = $_GET['sortby'];
    if (!$sortby) $sortby = 'name';
    $ordering = $_GET['ordering'];
    if (!$ordering) $ordering = 'asc';
    $degree = $_GET['degree'];
    $year = $_GET['repyear'];
    $percent = $_GET['percent'];
    $direction = $_GET['direction'];
    $user_results = array_csort($user_results,$sortby,$ordering);
  }
  $result->free_result();
  $result->close();
*/
  header('Content-type: application/octet-stream');
  header("Content-Disposition: attachment; filename=new_" . str_replace(' ', '_', $paper) . ".csv");
  $row_written = 0;
  foreach ($user_results as $individual) {
    $tmp_user_ID = $individual['username'];
    // Write out the headings.
    if ($row_written == 0) {
      // Only output personal data if assessment, do not show if survey.
      if ($paper_type < 3) {
        echo '"Gender","Title","Surname","First Names","Student ID","Degree","Year","Submitted"';
      } else {
        echo '"Gender","Degree","Year","Submitted"';
      }
      $q_no = 1;
      foreach ($paper_buffer as $q_id => $question) {
        //$q_id = $question['id'];
        if ($question['q_type'] == 'extmatch' or $question['q_type'] == 'matrix') {
          $sub_parts = 0;
          $paper_answers = explode("|",$question['correct'][0]);
          if (array_key_exists($q_id,$excluded)) {
            $tmp_exclude = $excluded[$q_id];
          } else {
            $tmp_exclude = '0000000000000000000000000000000000000000';
          }
          for ($a=0; $a<count($paper_answers); $a++) {
            $sub_parts += substr_count($paper_answers[$a],'$');
          
            if ($paper_answers[$a] != '' and substr($tmp_exclude,$a+$sub_parts,1) == '0') echo ',Q' . $q_no . '.' . ($a+1);
          }
        } elseif ($question['q_type'] == 'dichotomous' or $question['q_type'] == 'labelling' or $question['q_type'] == 'blank') {
          if (array_key_exists($q_id,$excluded)) {
            $tmp_exclude = $excluded[$q_id];
          } else {
            $tmp_exclude = '0000000000000000000000000000000000000000';
          }
          for ($a=0; $a<count($question['correct']); $a++) {
            if (substr($tmp_exclude,$a,1) == '0') echo ',Q' . $q_no . '.' . ($a+1);
          }
        } else {
          if (!array_key_exists($q_id,$excluded)) echo ',Q' . $q_no;
        }
        $q_no++;
      }
      echo "\n";
    }
    // Write out the raw data.
    if ($individual['visible'] == 1) {
      if ($paper_type < 3) {
        echo '"' . $individual['gender'] . '","' . $individual['title'] . '","' . $individual['surname'] . '","' . $individual['first_names'] . '","' . $individual['student_id'] . '","' .$individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
      } else {
        echo '"' . $individual['gender'] . '","' . $individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
      }
      foreach ($individual['mark_array'] as $individual_mark) echo ",$individual_mark";
      echo "\n";
    }
    $row_written++;
  }
  $mysqli->close();
?>