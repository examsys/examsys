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
  require '../include/errors.inc';
  require_once '../classes/stringutils.class.php';
  require_once '../include/sort.inc';

  check_var('paperID', 'GET', true, false);
  check_var('startdate', 'GET', true, false);
  check_var('enddate', 'GET', true, false);
  
  if (strpos($userroles,'Demo') !== false) {
    $demo = true;
  } else {
    $demo = false;
  }

  function get_random_question_details($question, $rand_id, $mysqli) {
    $result = $mysqli->prepare("SELECT q_id, q_type, correct, option_text, score_method FROM (questions, options) WHERE questions.q_id=options.o_id AND questions.q_id=? ORDER BY id_num");
    $result->bind_param('i', $rand_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id, $q_type, $correct, $option_text, $score_method);
    $question['correct'] = '';
    $question['correct_text'] = '';
    while ($result->fetch()) {
      $result->bind_result($q_id, $q_type, $correct, $option_text, $score_method);
      $question['ID'] = $q_id;
      $question['type'] = $q_type;
      $question['score_method'] = $score_method;
      $question['correct'] = fix_correct($q_type, $correct, $question['correct']);
      $question['option_text'] = $option_text;
      $question['correct_text'] .= "\t" . $option_text;
    }
    if ($question['type'] == 'blank') {
      $old_correct = '';
      $split1 = explode('[blank', $question['option_text']);
      for ($i=1; $i<count($split1); $i++) {
        $split2 = explode(',', substr($split1[$i], 1, strpos($split1[$i], '[/blank]') - 1));
        $old_correct .= ',' . $split2[0];
      }
      $question['correct'] = $old_correct;
    }
    $result->close();

    return $question;
  }

  function add_random_column_standard($i, $sec, $subsec=''){
    echo ':user';
    echo ',Q' . ($i+1) . chr($sec+64) . $subsec . ':correct';
  }

  function fix_correct($q_type, $correct, $old_correct) {
    if ($q_type == 'mcq' or $q_type == 'calculation') {
      $old_correct = ',' . $correct;
    } elseif ($q_type != 'extmatch' and $q_type != 'matrix') {
      $old_correct .= ',' . $correct;
    } else {
      $old_correct = ',' . str_replace('|',",",$correct);
      if (substr($old_correct,-1,1) == ',') $old_correct = substr($old_correct,0,strlen($old_correct)-1);
    }

    return $old_correct;
  }

  function array_swap($array, $ix1, $ix2) {
    $tmp = $array[$ix1];
    $array[$ix1] = $array[$ix2];
    $array[$ix2] = $tmp;

    return $array;
  }

  $mode = (isset($_GET['mode']) and $_GET['mode'] == 'text') ? 'text' : 'numeric';
  $numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii', 'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx');

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
  $old_correct_text = '';
  $old_random_qids = array();
  
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
      $paper_buffer[$question_no]['correct_text'] = $old_correct_text;
      $paper_buffer[$question_no]['score_method'] = $old_score_method;
      if ($old_q_type == 'random') {
        $paper_buffer[$question_no]['rand_ids'] = $old_random_qids;
        $old_random_qids = array();
      }
      $question_no++;
      if ($old_q_type == 'blank') {
        $old_correct = '';
        $split1 = explode('[blank', $old_option_text);
        for ($i=1; $i<count($split1); $i++) {
          $split2 = explode(',', substr($split1[$i],1,strpos($split1[$i],'[/blank]')-1));
          $old_correct .= ',' . $split2[0];
        }
        $paper_buffer[$question_no-1]['correct'] = $old_correct;
      }
      if ($q_type != 'extmatch' and $q_type != 'matrix') {
        $old_correct = ',' . $correct;
      }
      $old_correct_text = '';
    } else {
      $old_correct = fix_correct($q_type, $correct, $old_correct);
    }
    $old_correct_text .= "\t" . $option_text;

    if ($q_type == 'random') {
      $old_random_qids[] = $option_text;
    }
    $old_q_id = $q_id;
    $old_q_type = $q_type;
    $old_screen = $screen;
    $old_score_method = $score_method;
    $old_option_text = $option_text;
    $part++;
  }
  $result->close();
  $paper_buffer[$question_no]['ID'] = $old_q_id;
  $paper_buffer[$question_no]['type'] = $old_q_type;
  $paper_buffer[$question_no]['screen'] = $old_screen;
  $paper_buffer[$question_no]['correct'] = $old_correct;
  $paper_buffer[$question_no]['correct_text'] = $old_correct_text;
  $paper_buffer[$question_no]['score_method'] = $old_score_method;
  if ($old_q_type == 'random') {
    $paper_buffer[$question_no]['rand_ids'] = $old_random_qids;
  }
  $question_no++;

  header('Pragma: public');
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
  $user_no = round(($result->num_rows/100) * $_GET['percent']);
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

  if ($student_no > 0) {
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
      $log_array[$rowID]['student_id'] = demo_replace_number($student_id, $demo);
      $log_array[$rowID]['userID'] = $userID;
      $log_array[$rowID]['username'] = $username;
      $log_array[$rowID]['course'] = $grade;
      $log_array[$rowID]['year'] = $year;
      $log_array[$rowID]['started'] = $started;
      $log_array[$rowID]['title'] = $title;
      $log_array[$rowID]['surname'] = demo_replace($surname, $demo);
      $log_array[$rowID]['first_names'] = demo_replace($first_names, $demo);
      $log_array[$rowID]['name'] = str_replace("'", "", $surname) . ',' . $first_names;
      $log_array[$rowID]['gender'] = $gender;

      $user_no++;
      $old_username = $username;
      $old_started = $started;
    }
    $result->close();

    $sortby = 'name';
    $ordering = 'asc';
    $log_array = array_csort($log_array, $sortby, $ordering);

    $row_written = 0;
    foreach ($log_array as $individual) {
      $tmp_user_ID = $individual['userID'];
      // Write out the headings.
      if ($row_written == 0) {
        echo $string['gender'] . ',' . $string['title'] . ',' . $string['surname'] . ',' . $string['firstnames'] . ',' . $string['studentid'] . ',' . $string['course'] . ',' . $string['year'] . ',' . $string['started'];
        for ($i = 0; $i < $question_no; $i++) {
          $tmp_question_ID = $paper_buffer[$i]['ID'];
          $tmp_screen = $paper_buffer[$i]['screen'];
          if (array_key_exists($tmp_question_ID, $excluded)) {
            $tmp_exclude = $excluded[$tmp_question_ID];
          } else {
            $tmp_exclude = '0000000000000000000000000000000000000000';
          }

          // If a random question, get the first of the associated questions from the block. If none exist, output nothing
          $question = $paper_buffer[$i];
          $skip_random = false;
          $is_random = false;
          if ($question['type'] == 'random' and isset($question['rand_ids'])) {
            $tmp_id = $question['ID'];
            $question = get_random_question_details($question, $question['rand_ids'][0], $mysqli);
            if ($tmp_id != $question['ID']) {
              $is_random = true;
            } else {
              $skip_random = true;
            }
          }
          if (!$skip_random) {
            switch ($question['type']) {
              case 'blank':
                for ($sec=1; $sec<=substr_count($question['correct'], ','); $sec++) {
                  if (substr($tmp_exclude, $sec - 1, 1) == '0') {
                    echo ',Q' . ($i+1) . chr($sec + 64);
                    if ($is_random) {
                      add_random_column_standard($i, $sec);
                    }
                  }
                }
                break;
              case 'extmatch':
                $correct_parts = explode(',', $question['correct']);
                $partID = 0;
                for ($sec=1; $sec < count($correct_parts); $sec++) {
                  if ($correct_parts[$sec] != '' and substr($tmp_exclude, $partID, 1) == '0') {
                    if (strpos($correct_parts[$sec], '$') === false) {
                      echo ',Q' . ($i+1) . $numerals[$sec-1];
                      if ($is_random) {
                        add_random_column_standard($i, $sec);
                      }
                    } else {
                      $num_ix = 0;
                      $correct_subparts = explode('$', $correct_parts[$sec]);
                      foreach ($correct_subparts as $subpart) {
                        echo ',Q' . ($i+1) . $numerals[$sec-1] . chr($num_ix + 65);
                        if ($is_random) {
                          add_random_column_standard($i, $sec, $numerals[$num_ix]);
                        }
                        $num_ix++;
                      }
                    }
                  }
                  $partID += substr_count($correct_parts[$sec],'$') + 1;
                }
                break;
              case 'hotspot':
                $correct_parts = explode('|', $question['correct']);
                for ($sec=0; $sec<count($correct_parts); $sec++) {
                  if (substr($tmp_exclude,$sec,1) == '0') {
                    echo ',Q' . ($i+1) . chr($sec + 65);
                  }
                }
                break;
              case 'labelling':
                $sec = 1;
                $tmp_first_split = explode(';', $question['correct']);
                $tmp_second_split = explode('$', $tmp_first_split[11]);
                for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
                  if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
                    if (substr($tmp_exclude,$sec-1,1) == '0') {
                      echo ',Q' . ($i+1) . chr($sec+64);
                      if ($is_random) {
                        add_random_column_standard($i, $sec);
                      }
                    }
                    $sec++;
                  }
                }
                break;
              case 'matrix':
                $correct_parts = explode(',', $question['correct']);
                for ($sec = 1; $sec < count($correct_parts); $sec++) {
                  if (substr($tmp_exclude, $sec - 1, 1) == '0' and $correct_parts[$sec] != '') {
                    echo ',Q' . ($i+1) . chr($sec+64);
                    if ($is_random) {
                      add_random_column_standard($i, $sec);
                    }
                  }
                }
                break;
              case 'rank':
                for ($sec=1; $sec<=substr_count($question['correct'],','); $sec++) {
                  if (substr($tmp_exclude,$sec-1,1) == '0') {
                    echo ',Q' . ($i+1) . chr($sec+64);
                    if ($is_random) {
                      add_random_column_standard($i, $sec);
                    }
                  }
                }
                break;
              case 'true_false':
              case 'dichotomous':
                for ($sec=1; $sec<=substr_count($question['correct'],','); $sec++) {
                  if (substr($tmp_exclude,$sec-1,1) == '0') {
                    echo ',Q' . ($i+1) . chr($sec+64);
                    if ($is_random) {
                      add_random_column_standard($i, $sec);
                    }
                  }
                }
                break;
              case 'mrq':
                for ($sec=1; $sec<=substr_count($question['correct'],','); $sec++) {
                  if (!isset($excluded[$tmp_question_ID])) {
                    echo ',Q' . ($i+1) . chr($sec+64);
                    if ($is_random) {
                      add_random_column_standard($i, $sec);
                    }
                  }
                }
                if ($question['score_method'] == 'other') echo ',Q' . ($i+1) . '.other';
                break;
              case 'calculation':
                if (!isset($excluded[$tmp_question_ID])) {
                  if ($is_random) {
                    echo ',Q' . ($i+1) . ':formula';
                  }
                  echo ',Q' . ($i+1) . ':user';
                  echo ',Q' . ($i+1) . ':correct';
                  echo ',Q' . ($i+1) . ':variables';
                }
                break;
              default:
                if (!isset($excluded[$tmp_question_ID])) {
                  echo ',Q' . ($i+1);
                  if ($is_random) {
                    echo ':user';
                    echo ',Q' . ($i+1) . ':correct';
                  }
                }
                break;
            }
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

          // If a random question, get the first of the associated questions from the block. If none exist, output nothing
          $question = $paper_buffer[$i];
          $skip_random = false;
          $is_random = false;
          if ($question['type'] == 'random' and isset($question['rand_ids'])) {
            $tmp_id = $question['ID'];
            $question = get_random_question_details($question, $question['rand_ids'][0], $mysqli);
            if ($tmp_id != $question['ID']) {
              $is_random = true;
            } else {
              $skip_random = true;
            }
          }

          if (!$skip_random) {
            switch ($question['type']) {
              case 'blank':
                $correct_parts = explode(',',$question['correct']);
                for ($partID=1; $partID<count($correct_parts); $partID++) {
                  if (substr($tmp_exclude,$partID-1,1) == '0') {
                    if ($is_random) {
                      echo ',,';
                    } else {
                      echo ',' . $correct_parts[$partID];
                    }
                  }
                }
                break;
              case 'extmatch':
                $correct_parts = explode(',',$question['correct']);
                $correct_text_parts = explode("\t", $question['correct_text']);
                $partID=1;
                for ($outer=1; $outer < count($correct_parts); $outer++) {
                  if ($correct_parts[$outer] != '' and substr($tmp_exclude,$partID-1,1) == '0') {
                    if ($is_random) {
                      echo str_repeat(',', 2 * (substr_count($correct_parts[$outer], '$') + 1));
                    } else {
                      if ($mode == 'numeric') {
                        echo ',"' . str_replace('$', '","', $correct_parts[$outer]) . '"';
                      } else {
                        if (strpos($correct_parts[$outer], '$') === false) {
                          echo ',"' . $correct_text_parts[$correct_parts[$outer]] . '"';
                        } else {
                          $correct_subparts = explode('$', $correct_parts[$outer]);
                          echo ',"';
                          for ($k = 0; $k < count($correct_subparts); $k++) {
                            $subpart = $correct_subparts[$k];
                            if ($k > 0) echo '","';
                            echo $correct_text_parts[$subpart];
                          }
                          echo '"';
                        }
                      }
                    }
                  }
                  $partID += substr_count($correct_parts[$outer],'$') + 1;
                }
                break;
              case 'matrix':
                $correct_parts = explode(',', $question['correct']);
                $correct_text_parts = explode("\t", $question['correct_text']);
                for ($partID=1; $partID < count($correct_parts); $partID++) {
                  if (substr($tmp_exclude,$partID-1,1) == '0' and $correct_parts[$partID] != '') {
                    echo ',';
                    if ($is_random) {
                      echo ',';
                    } else {
                      if ($mode == 'numeric') {
                      echo $correct_parts[$partID];
                      } else {
                        echo $correct_text_parts[$correct_parts[$partID]];
                      }
                    }
                  }
                }
                break;
              case 'mrq':
              case 'rank':
                if ($question['type'] == 'rank') $question['correct'] = str_replace('0','N/A',$question['correct']);
                if (!isset($excluded[$tmp_question_ID])) {
                  if ($is_random) {
                    echo str_repeat(',', substr_count($question['correct'], ',') * 2);
                  } else {
                    if ($mode == 'numeric') {
                      echo $question['correct'];
                    } else {
                      $correct_parts = explode(',', $question['correct']);
                      $correct_text_parts = explode("\t", $question['correct_text']);
                      for ($j = 1; $j < count($correct_parts); $j++) {
                        if ($question['type'] == 'mrq' and $correct_parts[$j] == 'y') {
                          echo ',"' . $correct_text_parts[$j] . '"';
                        } elseif ($question['type'] == 'rank') {
                          echo ',' . StringUtils::ordinal_suffix($correct_parts[$j], $language);
                        } else {
                          echo ',';
                        }
                      }
                    }
                  }
                }
                break;
              case 'hotspot':
                $correct_parts = explode('|', $question['correct']);
                for ($partID=0; $partID<count($correct_parts); $partID++) {
                  if (substr($tmp_exclude,$partID-1,1) == '0') echo ',';
                }
                break;
              case 'labelling':
                $sec = 1;
                $tmp_first_split = explode(';', $question['correct']);
                $tmp_second_split = explode('$', $tmp_first_split[11]);
                for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
                  if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
                    if (substr($tmp_exclude,$sec-1,1) == '0') {
                      $tmp_third_split = explode('|', $tmp_second_split[$label_no]);
                      echo ',';
                      if ($is_random) {
                        echo ',';
                      } else {
                        if ($mode == 'numeric') {
                          echo $tmp_third_split[1];
                        } else {
                          echo $tmp_third_split[0];
                        }
                      }
                    }
                    $sec++;
                  }
                }
                break;
              case 'true_false':
              case 'dichotomous':
                $correct_parts = explode(',',$question['correct']);
                for ($partID=1; $partID<count($correct_parts); $partID++) {
                  if (substr($tmp_exclude,$partID-1,1) == '0') {
                    echo ',';
                    if ($is_random) {
                      echo ',';
                    } else {
                      echo $correct_parts[$partID];
                    }
                  }
                }
                break;
              case 'textbox':
                if (!isset($excluded[$tmp_question_ID])) echo ',';
                break;
              case 'calculation':
                if (!isset($excluded[$tmp_question_ID])) {
                  echo ',,';
                  if ($is_random) {
                    echo ',';
                  } else {
                    echo '"' . substr($question['correct'],1) . '"';
                  }
                  echo ',';
                }
                break;
              case 'sct':
                $correct_text_parts = explode("\t", $question['correct_text']);
                if (!isset($excluded[$tmp_question_ID])) {
                  $correct = '';
                  $parts = explode(',', $question['correct']);
                  $max_correct = 0;
                  for ($partID = 1; $partID < count($parts); $partID++) {
                    if ($parts[$partID] > $max_correct) {
                      $max_correct = $parts[$partID];
                      if ($mode == 'numeric') {
                        $correct = $partID;
                      } else {
                        $correct = $correct_text_parts[$partID];
                      }
                    } elseif ($parts[$partID] == $max_correct and $max_correct > 0) {
                      if ($mode == 'numeric') {
                        $correct .= ',' . $partID;
                      } else {
                        $correct .= ' OR ' . $correct_text_parts[$partID];
                      }
                    }
                  }
                  echo ',';
                  if ($is_random) {
                    echo ',';
                  } else {
                    echo '"' . $correct . '"';
                  }
                }
                break;
              default:
                if (!isset($excluded[$tmp_question_ID])) {
                  if ($is_random) {
                    echo ',,';
                  } else {
                    if ($mode == 'numeric') {
                      echo $question['correct'];
                    } else {
                      $corr_index = ltrim($question['correct'], ',');
                      $correct_text_parts = explode("\t", $question['correct_text']);
                      if (isset($correct_text_parts[$corr_index])) {
                        echo ',"' . $correct_text_parts[$corr_index] . '"';
                      } else {
                        echo ',,';
                      }
                    }
                  }
                }
                break;
            }
          }
        }
        echo "\n";
      }
      // Write out the raw data.
      echo $individual['gender'] . ',"' . $individual['title'] . '","' . $individual['surname'] . '","' . $individual['first_names'] . '","' . $individual['student_id'] . '","' . $individual['course'] . '",' . $individual['year'] . ',' . $individual['started'];
      for ($i=0; $i<$question_no; $i++) {
        $tmp_question_ID = $paper_buffer[$i]['ID'];
        $tmp_screen = $paper_buffer[$i]['screen'];
        if (array_key_exists($tmp_question_ID,$excluded)) {
          $tmp_exclude = $excluded[$tmp_question_ID];
        } else {
          $tmp_exclude = '0000000000000000000000000000000000000000';
        }

        // If a random question, get the one that the user answered
        $question = $paper_buffer[$i];
        $skip_random = false;
        $is_random = false;
        if ($question['type'] == 'random') {
          if (isset($question['rand_ids']) and count($question['rand_ids']) > 0) {
            $rnd_found = false;
            if (isset($individual[$tmp_screen])) {
              $screen_ids = array_keys($individual[$tmp_screen]);
              foreach ($question['rand_ids'] as $tmp_id) {
                if (in_array($tmp_id, $screen_ids)) {
                  $rnd_found = true;
                  $question = get_random_question_details($question, $tmp_id, $mysqli);
                  if ($tmp_id != $tmp_question_ID) {
                    $is_random = true;
                    $tmp_question_ID = $tmp_id;
  //                  $question['correct'] = fix_correct($question['type'], $question['correct'], $question['correct']);
                  } else {
                    $skip_random = true;
                  }
                  break;
                }
              }
            }
            if (!$rnd_found) {
              reset($question['rand_ids']);
              $tmp_question_ID = key($question['rand_ids']);
              $question = get_random_question_details($question, $tmp_question_ID, $mysqli);
            }
          } else {
            $skip_random = true;
          }
        }

        if (!$skip_random) {
          switch ($question['type']) {
            case 'blank':
              $correct_parts = explode(',',$question['correct']);
              $tmp_answers = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode('|',$individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');
              $correct_parts = explode(',',$question['correct']);
              for ($partID=1; $partID<count($correct_parts); $partID++) {
                if (substr($tmp_exclude,$partID-1,1) == '0') {
                  echo ',';
                  if ($tmp_answers[$partID] != 'u') {
                    echo str_replace("\n", ' ', str_replace("\r", ' ', $tmp_answers[$partID]));
                  }
                  if ($is_random) {
                    echo ',' . $correct_parts[$partID];
                  }
                }
              }
              break;
            case 'calculation':
              if (isset($individual[$tmp_screen][$tmp_question_ID])) {
                $answer_parts = explode('|',$individual[$tmp_screen][$tmp_question_ID]);
              } else {
                $answer_parts = array('','','');
              }
              if (!isset($excluded[$tmp_question_ID])) {
                if ($is_random) {
                  echo ',"' . substr($question['correct'],1) . '"';
                }
                $vars = explode(',', $answer_parts[2]);
                $variables = '';
                foreach ($vars as $var) {
                  if ($variables == '') {
                    $variables = $var;
                  } else {
                    if ($var != '') $variables .= ',' . $var;
                  }
                }
                echo ',"' . $answer_parts[0] . '",' . $answer_parts[1] . ',"' . $variables . '"';
              }
              break;
            case 'true_false':
            case 'dichotomous':
              $correct_parts = explode(',',$question['correct']);
              for ($partID=0; $partID < count($correct_parts) - 1; $partID++) {
                if (substr($tmp_exclude, $partID, 1) == '0') {
                  echo ',';
                  $part_ans = (isset($individual[$tmp_screen][$tmp_question_ID])) ? substr($individual[$tmp_screen][$tmp_question_ID],$partID,1) : 'u';
                  if($part_ans != 'u') {
                    echo $part_ans;
                  }
                  if ($is_random) {
                    echo ',' . $correct_parts[$partID + 1];
                  }
                }
              }
              break;
            case 'extmatch':
              $correct_parts = explode(',',$question['correct']);
              $answer_parts = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode('|',$individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

              $partID = 0;
              for ($outer=1; $outer < count($correct_parts); $outer++) {
                if ($correct_parts[$outer] != '' and substr($tmp_exclude,$partID,1) == '0') {
                  $correct_subparts = explode('$', $correct_parts[$outer]);
                  $correct_text_parts = explode("\t", $question['correct_text']);
                  if (isset($answer_parts[$outer-1])) {
                    $answer_subparts = explode('$', $answer_parts[$outer-1]);
                    echo ',"';
                    for ($k = 0; $k < count($correct_subparts); $k++) {
                      if ($k > 0) echo '","';

                      $diff = count($correct_subparts) - count($answer_subparts);
                      if ($diff > 0) {
                        $answer_subparts = array_pad($answer_subparts, -1 * ($diff + count($answer_subparts)), '-1');
                      }

                      if (count($correct_subparts) > 1) {
                        $corr_index = array_search($correct_subparts[$k], $answer_subparts);
                        if ($corr_index !== false and $corr_index > $k) {
                          $answer_subparts = array_swap($answer_subparts, $k, $corr_index);
                        }
                      }

                      if ($answer_subparts[$k] != -1) {
                        $subpart = $answer_subparts[$k];
                        if ($mode == 'numeric') {
                          echo $answer_subparts[$k];
                        } else {
                          if (isset($correct_text_parts[$subpart])) {
                            echo $correct_text_parts[$subpart];
                          }
                        }
                      }
                      if ($is_random) {
                        if ($mode == 'numeric') {
                          echo '","' . $correct_subparts[$k];
                        } else {
                          echo '","' . $correct_text_parts[$correct_subparts[$k]];
                        }
                      }
                    }
                    echo '"';
                  } else {
                    for ($k = 0; $k < count($correct_subparts); $k++) {
                      echo ',';
                      if ($is_random) {
                        if ($mode == 'numeric') {
                          echo ',' . $correct_subparts[$k];
                        } else {
                          echo ',"' . $correct_text_parts[$correct_subparts[$k]] . '"';
                        }
                      }
                    }
                  }
                }
                $partID += substr_count($correct_parts[$outer],'$') + 1;
              }
              break;
            case 'matrix':
              $correct_parts = explode(',', $question['correct']);
              $correct_text_parts = explode("\t", $question['correct_text']);
              $answer_parts = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode('|',$individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

              for ($partID=0; $partID < count($correct_parts) - 1; $partID++) {
                // $correct_parts[0] is always empty
                if (substr($tmp_exclude,$partID,1) == '0' and $correct_parts[$partID + 1] != '') {
                  echo ',';
                  if (isset($answer_parts[$partID]) and  $answer_parts[$partID] != '' and  $answer_parts[$partID] != 'u') {
                    if ($mode == 'numeric') {
                      echo $answer_parts[$partID];
                    } else {
                      echo $correct_text_parts[$answer_parts[$partID]];
                    }
                  }
                  if ($is_random) {
                    echo ',';
                    if ($mode == 'numeric') {
                      echo $correct_parts[$partID + 1];
                    } else {
                      echo $correct_text_parts[$correct_parts[$partID + 1]];
                    }
                  }
                }
              }
              break;
            case 'rank':
              $individual[$tmp_screen][$tmp_question_ID] = (isset($individual[$tmp_screen][$tmp_question_ID])) ? str_replace('0','N/A',$individual[$tmp_screen][$tmp_question_ID]) : '';
              if (!isset($excluded[$tmp_question_ID])) {
                $correct_parts = explode(',', $question['correct']);
                $answer_parts = ($individual[$tmp_screen][$tmp_question_ID] != '') ? explode(',',$individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

                for ($partID=0; $partID < count($correct_parts) - 1; $partID++) {
                  echo ',';
                  if ($answer_parts[$partID] != 'u') {
                    echo ($mode == 'numeric') ? $answer_parts[$partID] : StringUtils::ordinal_suffix($answer_parts[$partID], $language);
                  }
                  if ($is_random) {
                    echo ',';
                    echo ($mode == 'numeric') ? $correct_parts[$partID + 1] : StringUtils::ordinal_suffix($correct_parts[$partID + 1], $language);
                  }
                }
              }
              break;
            case 'hotspot':
              $correct_parts = explode('|', $question['correct']);
              $answer_parts = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode('|',$individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

              for ($partID = 0; $partID < count($correct_parts); $partID++) {
                if (substr($tmp_exclude, $partID, 1) == '0') {
                  echo ',';
                  if (isset($answer_parts[$partID]) and $answer_parts[$partID] != 'u') {
                    echo str_replace(',', 'x', substr($answer_parts[$partID], 2));
                  }
                }
              }
              break;
            case 'labelling':
              $tmp_first_split = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode(';', $individual[$tmp_screen][$tmp_question_ID]) : array('', '');
              $tmp_answers = explode('$', $tmp_first_split[1]);
              $user_answers = array();
              for ($label_no = 0; $label_no <= count($tmp_answers)-4; $label_no += 4) {
                $user_answers[$tmp_answers[$label_no] . 'x' . $tmp_answers[$label_no+1]] = $tmp_answers[$label_no+2];
              }

              $sec = 1;
              $cix = 0;
              $tmp_first_split = explode(';', $question['correct']);
              $tmp_second_split = explode('$', $tmp_first_split[11]);
              $label_indexes = array();
              $answers = array();
              $correct = array();
              for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
                if (substr($tmp_exclude,$sec-1,1) == '0') {
                  $tmp_third_split = explode('|', $tmp_second_split[$label_no]);
                  $label_indexes[$tmp_third_split[0]] = $tmp_third_split[1];
                  if (substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no-2] > 219) {
                    $location = $tmp_second_split[$label_no-2] . 'x' . ($tmp_second_split[$label_no-1] - 25);
                    $correct[$cix] = $tmp_third_split[0];
                    $cix++;
                    if (isset($user_answers[$location])) {
                      $answers[] = $user_answers[$location];
                    } else {
                      $answers[] = '';
                    }
                  }
                  $sec++;
                }
              }
              for ($j = 0; $j < count($answers); $j++) {
                $answer = $answers[$j];
                echo ',';
                if ($answer != '') {
                  if ($mode == 'numeric') {
                    if (isset($label_indexes[$answer])) {
                      echo $label_indexes[$answer];
                    }
                    if ($is_random) {
                      echo ',' . $label_indexes[$correct[$j]];
                    }
                  } else {
                    echo $answer;
                    if ($is_random) {
                      echo ',' . $correct[$j];
                    }
                  }
                }
              }
              break;
            case 'mrq':
              if (!isset($excluded[$tmp_question_ID])) {
                $correct_clean = str_replace(',', '', $question['correct']);
                $correct_text_parts = explode("\t", $question['correct_text']);
                for ($char_pos = 0; $char_pos < substr_count($question['correct'], ','); $char_pos++) {
                  $part_ans = (isset($individual[$tmp_screen][$tmp_question_ID])) ? substr($individual[$tmp_screen][$tmp_question_ID], $char_pos, 1) : '';
                  if ($mode == 'numeric') {
                    echo ',"' . $part_ans . '"';
                  } else {
                    if ($part_ans == 'y') {
                      echo ',"' . $correct_text_parts[$char_pos + 1] . '"';
                    } else {
                      echo ',';
                    }
                  }
                  if ($is_random) {
                    if ($mode == 'numeric') {
                      echo ',' . substr($correct_clean, $char_pos, 1);
                    } else {
                      if (substr($correct_clean, $char_pos, 1) == 'y') {
                        echo ',"' . $correct_text_parts[$char_pos + 1] . '"';
                      } else {
                        echo ',';
                      }
                    }
                  }
                }
                $char_pos = substr_count($question['correct'],',') + 1;
                if ($question['score_method'] == 'other') {
                  $part_ans = (isset($individual[$tmp_screen][$tmp_question_ID])) ? substr($individual[$tmp_screen][$tmp_question_ID], $char_pos + 1) : '';
                  echo ',"' . $part_ans . '"';
                  if ($is_random) {
                    echo ',';
                  }
                }
              }
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
            case 'sct':
              $correct_text_parts = explode("\t", $question['correct_text']);
              if (!isset($excluded[$tmp_question_ID])) {
                echo ',"';
                if (isset($individual[$tmp_screen][$tmp_question_ID]) and $individual[$tmp_screen][$tmp_question_ID] != 'u') {
                  if ($mode == 'numeric') {
                    echo $individual[$tmp_screen][$tmp_question_ID];
                  } else {
                    echo $correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]];
                  }
                }
                echo '"';
                if ($is_random) {
                  $correct = '';
                  $parts = explode(',', $question['correct']);
                  $max_correct = 0;
                  for ($partID = 1; $partID < count($parts); $partID++) {
                    if ($parts[$partID] > $max_correct) {
                      $max_correct = $parts[$partID];
                      $correct = ($mode =='numeric') ? $partID : $correct_text_parts[$partID];
                    } elseif ($parts[$partID] == $max_correct and $max_correct > 0) {
                      if ($mode =='numeric') {
                        $correct .= ',' . $partID;
                      } else {
                        $correct .= ' OR ' . $correct_text_parts[$partID];
                      }
                    }
                  }
                  echo ',"' . $correct . '"';
                }
              }
              break;
            default:
              if (!isset($excluded[$tmp_question_ID])) {
                $correct_text_parts = explode("\t", $question['correct_text']);
                echo ',"';
                if (isset($individual[$tmp_screen][$tmp_question_ID]) and $individual[$tmp_screen][$tmp_question_ID] != 'u') {
                  if ($mode == 'numeric') {
                    echo $individual[$tmp_screen][$tmp_question_ID];
                  } else {
                    if (isset($correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]])) {
                      echo $correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]];
                    }
                  }
                }
                echo '"';
                if ($is_random) {
                  if ($mode =='numeric') {
                  echo ',"' . ltrim($question['correct'], ',') . '"';
                  } else {
                    echo ',"' . $correct_text_parts[ltrim($question['correct'], ',')] . '"';
                  }
                }
              }
              break;
          }
        }
      }
      echo "\n";
      $row_written++;
    }
  } else {
    echo $string['nodata'];
  }
  $mysqli->close();

?>