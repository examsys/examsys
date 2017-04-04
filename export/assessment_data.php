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
* Report that exports responses in CSV format (raw or text).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/demo_replace.inc';
require_once '../include/errors.php';
require_once '../include/sort.inc';

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

$displayDebug = false; // Disable debug output in this script as it effects the output

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$demo = is_demo($userObject);

function get_random_question_details($question, $rand_id, $mysqli) {
  $result = $mysqli->prepare("SELECT q_id, q_type, correct, option_text, score_method FROM questions LEFT JOIN options ON questions.q_id = options.o_id  WHERE questions.q_id = ? ORDER BY id_num");
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
    $question['correct'] = fix_correct($q_type, $correct, $question['correct'], $option_text);
    $question['option_text'] = $option_text;
    $question['correct_text'] .= "\t" . $option_text;
  }
  $result->close();

  return $question;
}

function add_random_column_standard($i, $sec, &$csv, $subsec = ''){
  $csv .= ':user';
  $csv .= ',Q' . ($i+1) . chr($sec+64) . $subsec . ':correct';
}

function fix_correct($q_type, $correct, $old_correct, $option_text) {
  if ($q_type === 'blank') {
    // Fill in the blank questions only ever have one entry in the option table,
    // the blanks that need to be filled in are stored in the option_text field of the table.
    $old_correct = '';
    // All of the areas a student needs to fill in are surrounded by [blank][/blank]
    // with each option displayed to a student as a comma separated list.
    $split1 = explode('[blank', $option_text);
    for ($i=1; $i<count($split1); $i++) {
      // The first entry in the comma separated list is the correct answer.
      $split2 = explode(',', substr($split1[$i],1,strpos($split1[$i],'[/blank]')-1));
      $old_correct .= ',' . $split2[0];
    }
  } else if ($q_type == 'mcq' or $q_type == 'enhancedcalc') {
    $old_correct = ',' . $correct;
  } elseif ($q_type != 'extmatch' and $q_type != 'matrix') {
    $old_correct .= ',' . $correct;
  } else {
    $old_correct = ',' . str_replace('|',",",$correct);
    // If there is a comma at the end remove it.
    if (substr($old_correct, -1, 1) == ',') {
      $old_correct = substr($old_correct, 0, strlen($old_correct) - 1);
    }
  }

  return $old_correct;
}

function array_swap($array, $ix1, $ix2) {
  $tmp = $array[$ix1];
  $array[$ix1] = $array[$ix2];
  $array[$ix2] = $tmp;

  return $array;
}

/**
 * Convert a string to comma separated HEX numbers to the decimal equivalent
 * @param $data
 * @return string
 */
function hex_to_dec($data) {
  $items = explode(',', $data);
  $response = '';

  foreach ($items as $item) {
    $response .= hexdec($item) . ',';
  }

  return rtrim($response, ',');
}

$mode = (isset($_GET['mode']) and $_GET['mode'] == 'text') ? 'text' : 'numeric';
$numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii', 'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx');

$user_sql = '';
if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
  $tmp_moduleID_in = $_GET['repmodule'];
  $calendar_year = $propertyObj->get_calendar_year();
  $mod_query = $mysqli->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in) AND calendar_year = ?");
  $mod_query->bind_param('s', $calendar_year);
  $mod_query->execute();
  $mod_query->bind_result($idMod, $tmp_userID, $tmp_moduleid);
  $mod_query->store_result();
  while ($mod_query->fetch()) {
    $user_modules[$tmp_userID]['idMod'] = $idMod;
    if ($user_sql == '') {
      $user_sql = $tmp_userID;
    } else {
      $user_sql .= ',' . $tmp_userID;
    }
  }
  $mod_query->close();
  $user_sql = 'AND userID IN (' . $user_sql . ')';
}

// Get any questions to exclude.
$excluded = array();
$result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper = ?");
$result->bind_param('i', $paperID);
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

$result = $mysqli->prepare("SELECT q_id, q_type, screen, correct, option_text, score_method, settings FROM papers, questions LEFT JOIN options ON questions.q_id = options.o_id WHERE papers.question = questions.q_id AND papers.paper = ? AND q_type != 'info' ORDER BY screen, display_pos, id_num");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_id, $q_type, $screen, $correct, $option_text, $score_method, $settings);
while ($result->fetch()) {
  if ($old_q_id != $q_id and $old_q_id != -1) {
    $part = 0;
    $paper_buffer[$question_no]['ID'] = $old_q_id;
    $paper_buffer[$question_no]['type'] = $old_q_type;
    $paper_buffer[$question_no]['screen'] = $old_screen;
    $paper_buffer[$question_no]['correct'] = $old_correct;
    $paper_buffer[$question_no]['correct_text'] = $old_correct_text;
    $paper_buffer[$question_no]['score_method'] = $old_score_method;
    $paper_buffer[$question_no]['settings'] = $old_settings;
    if ($old_q_type == 'random') {
      $paper_buffer[$question_no]['rand_ids'] = $old_random_qids;
      $old_random_qids = array();
    }
    $question_no++;
    $old_correct_text = '';
    $old_correct = fix_correct($q_type, $correct, '', $option_text);
  } else {
    // A seperate option for the same question as the last loop.
    $old_correct = fix_correct($q_type, $correct, $old_correct, $option_text);
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
  $old_settings = $settings;
  $part++;
}
$result->close();
$paper_buffer[$question_no]['ID'] = $old_q_id;
$paper_buffer[$question_no]['type'] = $old_q_type;
$paper_buffer[$question_no]['screen'] = $old_screen;
$paper_buffer[$question_no]['correct'] = $old_correct;
$paper_buffer[$question_no]['correct_text'] = $old_correct_text;
$paper_buffer[$question_no]['score_method'] = $old_score_method;
$paper_buffer[$question_no]['settings'] = $settings;
if ($old_q_type == 'random') {
  $paper_buffer[$question_no]['rand_ids'] = $old_random_qids;
}
$question_no++;
$paper_title = $propertyObj->get_paper_title();

header('Pragma: public');
header('Content-type: application/octet-stream');
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper_title) . "_ER.csv");

$user_no = 0;
$number_of_questions = $propertyObj->get_question_no();

$exclude = '';
$csv = '';

// Get order of the class.
$student_list = '';
$paper_type = $propertyObj->get_paper_type();

if ($_GET['studentsonly'] == '1') {
  $userroles = " AND (users.roles LIKE '%Student%' OR users.roles = 'graduate')";
} else {
  $userroles = '';
}

if ($paper_type == '0') {
  $sql = "(SELECT log_metadata.userID, SUM(mark) AS total_mark FROM log0, log_metadata, users WHERE log0.metadataID = log_metadata.id AND log_metadata.userID = users.id AND paperID = ? AND started >= ? AND started <= ? $user_sql $userroles GROUP BY log_metadata.userID, paperID, started)"
    . " UNION ALL (SELECT log_metadata.userID, sum(mark) AS total_mark FROM log1, log_metadata, users WHERE log1.metadataID = log_metadata.id AND log_metadata.userID = users.id AND paperID = ? AND started >= ? AND started <= ? $user_sql $userroles GROUP BY log_metadata.userID, paperID, started) ORDER BY total_mark";
  $result = $mysqli->prepare($sql);
  $result->bind_param('ississ', $paperID, $startdate, $enddate, $paperID, $startdate, $enddate);
} else {
  $result = $mysqli->prepare("SELECT log_metadata.userID, SUM(mark) AS total_mark FROM log$paper_type, log_metadata, users WHERE log$paper_type.metadataID = log_metadata.id AND paperID = ? AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? $user_sql AND log_metadata.userID = users.id $userroles GROUP BY log_metadata.userID, paperID, started ORDER BY total_mark");
  $result->bind_param('iss', $paperID, $startdate, $enddate);
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
  $repcourse = check_var('repcourse', 'GET', true, false, true);
  // Capture the log data.
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT DISTINCT 
                                    username, 
                                    log_metadata.userID, 
                                    title, 
                                    surname, 
                                    first_names, 
                                    grade, 
                                    gender, 
                                    year, 
                                    started, 
                                    log0.q_id, 
                                    user_answer, 
                                    q_type, 
                                    screen, 
                                    settings 
                                  FROM 
                                    (log0, log_metadata, questions, users) 
                                  WHERE 
                                    log0.metadataID = log_metadata.id AND 
                                    log0.q_id = questions.q_id AND 
                                    log_metadata.userID IN ($student_list) AND 
                                    paperID = ? AND 
                                    users.id = log_metadata.userID 
                                    $userroles 
                                    $exclude AND 
                                    grade LIKE ? 
                                    AND started >= ? AND 
                                    started <= ?) 
                                 UNION ALL (
                                    SELECT DISTINCT 
                                        username, 
                                        log_metadata.userID, 
                                        title, 
                                        surname, 
                                        first_names, 
                                        grade, 
                                        gender, 
                                        year, 
                                        started, 
                                        log1.q_id, 
                                        user_answer, 
                                        q_type, 
                                        screen,
                                        settings
                                     FROM 
                                        (log1, log_metadata, questions, users) 
                                     WHERE log1.metadataID = log_metadata.id AND 
                                        log1.q_id = questions.q_id AND 
                                        log_metadata.userID IN ($student_list) AND 
                                        paperID = ? AND 
                                        users.id = log_metadata.userID
                                        $userroles 
                                        $exclude AND 
                                        grade LIKE ? 
                                        AND started >= ? 
                                        AND started <= ?) 
                                      ORDER BY 
                                        surname, 
                                        first_names, 
                                        started, 
                                        userID");
    $result->bind_param('isssisss', $paperID, $repcourse, $startdate, $enddate, $paperID, $repcourse, $startdate, $enddate);
  } else {
    $result = $mysqli->prepare("SELECT DISTINCT username, log_metadata.userID, title, surname, first_names, grade, gender, year, started, log$paper_type.q_id, user_answer, q_type, screen, settings FROM (log$paper_type, log_metadata, questions, users) WHERE log$paper_type.metadataID = log_metadata.id AND log$paper_type.q_id = questions.q_id AND log_metadata.userID IN ($student_list) AND paperID = ? AND users.id = log_metadata.userID $userroles $exclude AND grade LIKE ? AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY surname, first_names, started, userID");
    $result->bind_param('isss', $paperID, $repcourse, $startdate, $enddate);
  }
  $result->execute();
  $result->bind_result($username, $uID, $title, $surname, $first_names, $grade, $gender, $year, $started, $question_ID, $user_answer, $q_type, $screen, $settings);
  $old_username = '';
  $users = array();
  while ($result->fetch()) {
    if ($old_username != $username or $old_started != $started) {
      $rowID++;
    }
		
    $log_array[$rowID][$screen][$question_ID] = $user_answer;
    $log_array[$rowID]['userID'] = $uID;
    $users[$uID][] = $rowID;
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
  
  // Get student ids.
  if (count($users) > 0) {
    $users_list = implode(',', array_keys($users));
    $result = $mysqli->prepare("SELECT student_id, userID FROM sid WHERE userID IN ($users_list)");
    $result->execute();
    $result->bind_result($sid, $userid);
    while ($result->fetch()) {
      foreach ($users[$userid] as $row) {
        $log_array[$row]['student_id'] = demo_replace_number($sid, $demo);
      }
    }
    for ($rowID = 1; $rowID < count($log_array); $rowID++) {
      if (!isset($log_array[$rowID]['student_id'])) {
        $log_array[$rowID]['student_id'] = null;
      }
    }
    $result->close();
  }
  $sortby = 'name';
  $ordering = 'asc';
  $log_array = array_csort($log_array, $sortby, $ordering);

  $row_written = 0;
  foreach ($log_array as $individual) {
    $tmp_user_ID = $individual['userID'];
    // Write out the headings.
    if ($row_written == 0) {
      $csv .= $string['gender'] . ',' . $string['title'] . ',' . $string['surname'] . ',' . $string['firstnames'] . ',' . $string['studentid'] . ',' . $string['course'] . ',' . $string['year'] . ',' . $string['started'];
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
                  $csv .= ',Q' . ($i+1) . chr($sec + 64);
                  if ($is_random) {
                    add_random_column_standard($i, $sec, $csv);
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
                    $csv .= ',Q' . ($i+1) . $numerals[$sec-1];
                    if ($is_random) {
                      add_random_column_standard($i, $sec, $csv);
                    }
                  } else {
                    $num_ix = 0;
                    $correct_subparts = explode('$', $correct_parts[$sec]);
                    foreach ($correct_subparts as $subpart) {
                      $csv .= ',Q' . ($i+1) . $numerals[$sec-1] . chr($num_ix + 65);
                      if ($is_random) {
                        add_random_column_standard($i, $sec, $csv, $numerals[$num_ix]);
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
                  $csv .= ',Q' . ($i+1) . chr($sec + 65);
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
                    $csv .= ',Q' . ($i+1) . chr($sec+64);
                    if ($is_random) {
                      add_random_column_standard($i, $sec, $csv);
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
                  $csv .= ',Q' . ($i+1) . chr($sec+64);
                  if ($is_random) {
                    add_random_column_standard($i, $sec, $csv);
                  }
                }
              }
              break;
            case 'rank':
              if ($tmp_exclude{0} == '0') {
                for ($sec=1; $sec<=substr_count($question['correct'],','); $sec++) {
                  $csv .= ',Q' . ($i+1) . chr($sec+64);
                  if ($is_random) {
                    add_random_column_standard($i, $sec, $csv);
                  }
                }
              }
              break;
            case 'true_false':
            case 'dichotomous':
              for ($sec=1; $sec<=substr_count($question['correct'],','); $sec++) {
                if (substr($tmp_exclude,$sec-1,1) == '0') {
                  $csv .= ',Q' . ($i+1) . chr($sec+64);
                  if ($is_random) {
                    add_random_column_standard($i, $sec, $csv);
                  }
                }
              }
              break;
            case 'mrq':
              for ($sec=1; $sec<=substr_count($question['correct'],','); $sec++) {
                if (!isset($excluded[$tmp_question_ID])) {
                  $csv .= ',Q' . ($i+1) . chr($sec+64);
                  if ($is_random) {
                    add_random_column_standard($i, $sec, $csv);
                  }
                }
              }
              if ($question['score_method'] == 'other') $csv .= ',Q' . ($i+1) . '.other';
              break;
            case 'enhancedcalc':
              if (!isset($excluded[$tmp_question_ID])) {
                if ($is_random) {
                  $csv .= ',Q' . ($i+1) . ':formula';
                }
                $csv .= ',Q' . ($i+1) . ':user';
                $csv .= ',Q' . ($i+1) . ':correct';
                $csv .= ',Q' . ($i+1) . ':variables';
              }
              break;
            default:
              if (!isset($excluded[$tmp_question_ID])) {
                $csv .= ',Q' . ($i+1);
                if ($is_random) {
                  $csv .= ':user';
                  $csv .= ',Q' . ($i+1) . ':correct';
                }
              }
              break;
          }
        }
      }
      $csv .= "\n";
      // Write out correct answers line.
      $csv .= $string['correctanswers'] . ',,,,,,,';
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
            case 'area':
              if (!isset($excluded[$tmp_question_ID])) {
                if ($is_random) {
                  $csv .= ',,';
                } else {
                  $csv .= ',"' . hex_to_dec(ltrim($question['correct'], ',')) . '"';
                }
              }
              break;
            case 'blank':
              $correct_parts = explode(',', $question['correct']);
              for ($partID=1; $partID<count($correct_parts); $partID++) {
                if (substr($tmp_exclude,$partID-1,1) == '0') {
                  if ($is_random) {
                    $csv .= ',,';
                  } else {
                    $csv .= ',' . $correct_parts[$partID];
                  }
                }
              }
              break;
            case 'flash':
              $csv .= ',';
              break;
            case 'extmatch':
              $correct_parts = explode(',',$question['correct']);
              $correct_text_parts = explode("\t", $question['correct_text']);
              $partID = 1;
              for ($outer=1; $outer < count($correct_parts); $outer++) {
                if ($correct_parts[$outer] != '' and substr($tmp_exclude,$partID-1,1) == '0') {
                  if ($is_random) {
                    $csv .= str_repeat(',', 2 * (substr_count($correct_parts[$outer], '$') + 1));
                  } else {
                    if ($mode == 'numeric') {
                      $csv .= ',"' . str_replace('$', '","', $correct_parts[$outer]) . '"';
                    } else {
                      if (strpos($correct_parts[$outer], '$') === false) {
                        $csv .= ',"' . $correct_text_parts[$correct_parts[$outer]] . '"';
                      } else {
                        $correct_subparts = explode('$', $correct_parts[$outer]);
                        $csv .= ',"';
                        for ($k = 0; $k < count($correct_subparts); $k++) {
                          $subpart = $correct_subparts[$k];
                          if ($k > 0) $csv .= '","';
                          $csv .= $correct_text_parts[$subpart];
                        }
                        $csv .= '"';
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
                  $csv .= ',';
                  if ($is_random) {
                    $csv .= ',';
                  } else {
                    if ($mode == 'numeric') {
                    $csv .= $correct_parts[$partID];
                    } else {
                      $csv .= $correct_text_parts[$correct_parts[$partID]];
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
                  $csv .= str_repeat(',', substr_count($question['correct'], ',') * 2);
                } else {
                  if ($mode == 'numeric') {
                    $csv .= $question['correct'];
                  } else {
                    $correct_parts = explode(',', $question['correct']);
                    $correct_text_parts = explode("\t", $question['correct_text']);
                    for ($j = 1; $j < count($correct_parts); $j++) {
                      if ($question['type'] == 'mrq' and $correct_parts[$j] == 'y') {
                        $csv .= ',"' . $correct_text_parts[$j] . '"';
                      } elseif ($question['type'] == 'rank') {
                        $csv .= ',' . StringUtils::ordinal_suffix($correct_parts[$j], $language);
                      } else {
                        $csv .= ',';
                      }
                    }
                  }
                }
              }
              break;
            case 'hotspot':
              $correct_parts = explode('|', $question['correct']);
              for ($partID=0; $partID<count($correct_parts); $partID++) {
                if (substr($tmp_exclude,$partID-1,1) == '0') $csv .= ',';
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
                    $csv .= ',';
                    if ($is_random) {
                      $csv .= ',';
                    } else {
                      if ($mode == 'numeric') {
                        $csv .= $tmp_third_split[1];
                      } else {
                        if ($ans = strstr($tmp_third_split[0], '~', true)) {
                          $csv .= $ans;
                        } else {
                          $csv .= $tmp_third_split[0];
                        }
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
                  $csv .= ',';
                  if ($is_random) {
                    $csv .= ',';
                  } else {
                    $csv .= $correct_parts[$partID];
                  }
                }
              }
              break;
            case 'textbox':
              if (!isset($excluded[$tmp_question_ID])) $csv .= ',';
              break;
            case 'enhancedcalc':
              if (!isset($excluded[$tmp_question_ID])) {
                $settings = json_decode($question['settings'], true);
                if ($is_random) {
                  $csv .= ',,,';
                } else {
                  $csv .= ',,"' . $settings['answers'][0]['formula'] . '",';
                }
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
                $csv .= ',';
                if ($is_random) {
                  $csv .= ',';
                } else {
                  $csv .= '"' . $correct . '"';
                }
              }
              break;
            default:
              if (!isset($excluded[$tmp_question_ID])) {
                if ($is_random) {
                  $csv .= ',,';
                } else {
                  if ($mode == 'numeric') {
                    $csv .= $question['correct'];
                  } else {
                    $corr_index = ltrim($question['correct'], ',');
                    $correct_text_parts = explode("\t", $question['correct_text']);
                    if (isset($correct_text_parts[$corr_index])) {
                      $csv .= ',"' . $correct_text_parts[$corr_index] . '"';
                    } else {
                      $csv .= ',,';
                    }
                  }
                }
              }
              break;
          }
        }
      }
      $csv .= "\n";
    }
    // Write out the raw data.
    $csv .= $individual['gender'] . ',"' . $individual['title'] . '","' . $individual['surname'] . '","' . $individual['first_names'] . '","' . $individual['student_id'] . '","' . $individual['course'] . '",' . $individual['year'] . ',' . $individual['started'];
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
                // The id returned will either be that of the question the user answered or the id of the random question.
                // We are only interested if it is the id of the question the user answered.
                if ($tmp_id == $question['ID']) {
                  $is_random = true;
                  $tmp_question_ID = $tmp_id;
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
          case 'area':
            if (!isset($excluded[$tmp_question_ID])) {
              $csv .= ',"';
              if (isset($individual[$tmp_screen][$tmp_question_ID]) and $individual[$tmp_screen][$tmp_question_ID] != '') {
                $answer_parts = explode(';', $individual[$tmp_screen][$tmp_question_ID]);

                if (count($answer_parts) > 1) {
                  $csv .= hex_to_dec($answer_parts[1]);
                }
              }
              $csv .= '"';
              if ($is_random) {
                $csv .= ',"' . hex_to_dec(ltrim($question['correct'], ',')) . '"';
              }
            }
            break;
          case 'blank':
            $correct_parts = explode(',', $question['correct']);
            $tmp_answers = (isset($individual[$tmp_screen][$tmp_question_ID])) ? json_decode($individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), null);
            for ($partID=0; $partID < count($correct_parts) - 1; $partID++) {
              if (substr($tmp_exclude,$partID,1) == '0') {
                $csv .= ',';
                if ($tmp_answers[$partID] != 'u') {
                  $csv .= '"' . str_replace("\n", ' ', str_replace("\r", ' ', $tmp_answers[$partID])) . '"';
                }
                if ($is_random) {
                  $csv .= ',' . $correct_parts[$partID];
                }
              }
            }
            break;
          case 'enhancedcalc':
            if (!isset($excluded[$tmp_question_ID])) {
							if (isset($individual[$tmp_screen][$tmp_question_ID])) {		// Check for missing answers.
								$answer = json_decode($individual[$tmp_screen][$tmp_question_ID], true);
              } else {
								$answer = '';
							}
              // Random questions display the formula as the first column.
              if ($is_random and isset($answer['ans']['formula_used'])) {
                $csv .= ',' . $answer['ans']['formula_used'];
              } else if ($is_random) {
                $csv .= ',error';
              }
							if (isset($answer['uans'])) {
								$csv .= ',' . $answer['uans'];
							} else {
								$csv .= ',error';
							}
							if (isset($answer['cans'])) {
								$csv .= ',' . $answer['cans'];
							} else {
								$csv .= ',error';
							}
              $variables = '';
							if (isset($answer['vars'])) {
								foreach ($answer['vars'] as $var_name => $value) {
									if ($variables == '') {
										$variables .= $value;
									} else {
										$variables .= ',' . $value;
									}
								}
							}
              $csv .= ',"' . $variables . '"';
            }
            break;
          case 'true_false':
          case 'dichotomous':
            $correct_parts = explode(',',$question['correct']);
            for ($partID=0; $partID < count($correct_parts) - 1; $partID++) {
              if (substr($tmp_exclude, $partID, 1) == '0') {
                $csv .= ',';
                $part_ans = (isset($individual[$tmp_screen][$tmp_question_ID])) ? substr($individual[$tmp_screen][$tmp_question_ID],$partID,1) : 'u';
                if($part_ans != 'u') {
                  $csv .= $part_ans;
                }
                if ($is_random) {
                  $csv .= ',' . $correct_parts[$partID + 1];
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
                  $csv .= ',"';
                  for ($k = 0; $k < count($correct_subparts); $k++) {
                    if ($k > 0) $csv .= '","';

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
                        $csv .= $answer_subparts[$k];
                      } else {
                        if (isset($correct_text_parts[$subpart])) {
                          $csv .= $correct_text_parts[$subpart];
                        }
                      }
                    }
                    if ($is_random) {
                      if ($mode == 'numeric') {
                        $csv .= '","' . $correct_subparts[$k];
                      } else {
                        $csv .= '","' . $correct_text_parts[$correct_subparts[$k]];
                      }
                    }
                  }
                  $csv .= '"';
                } else {
                  for ($k = 0; $k < count($correct_subparts); $k++) {
                    $csv .= ',';
                    if ($is_random) {
                      if ($mode == 'numeric') {
                        $csv .= ',' . $correct_subparts[$k];
                      } else {
                        $csv .= ',"' . $correct_text_parts[$correct_subparts[$k]] . '"';
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
                $csv .= ',';
                if (isset($answer_parts[$partID]) and  $answer_parts[$partID] != '' and  $answer_parts[$partID] != 'u') {
                  if ($mode == 'numeric') {
                    $csv .= $answer_parts[$partID];
                  } else {
                    $csv .= $correct_text_parts[$answer_parts[$partID]];
                  }
                }
                if ($is_random) {
                  $csv .= ',';
                  if ($mode == 'numeric') {
                    $csv .= $correct_parts[$partID + 1];
                  } else {
                    $csv .= $correct_text_parts[$correct_parts[$partID + 1]];
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
                $csv .= ',';
                if ($answer_parts[$partID] != 'u') {
                  $csv .= ($mode == 'numeric') ? $answer_parts[$partID] : StringUtils::ordinal_suffix($answer_parts[$partID], $language);
                }
                if ($is_random) {
                  $csv .= ',';
                  $csv .= ($mode == 'numeric') ? $correct_parts[$partID + 1] : StringUtils::ordinal_suffix($correct_parts[$partID + 1], $language);
                }
              }
            }
            break;
          case 'hotspot':
            $correct_parts = explode('|', $question['correct']);
            $answer_parts = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode('|',$individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

            for ($partID = 0; $partID < count($correct_parts); $partID++) {
              if (substr($tmp_exclude, $partID, 1) == '0') {
                $csv .= ',';
                if (isset($answer_parts[$partID]) and $answer_parts[$partID] != 'u') {
                  $csv .= str_replace(',', 'x', substr($answer_parts[$partID], 2));
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
              $tmp_third_split = explode('|', $tmp_second_split[$label_no]);
              $lix = strstr($tmp_third_split[0], '~', true);
              if ($lix === false) {
                $lix = $tmp_third_split[0];
              }
              $label_indexes[$lix] = $tmp_third_split[1];
              if (substr($tmp_exclude, $sec-1, 1) == '0') {
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
              }
              $sec++;
            }
            for ($j = 0; $j < count($answers); $j++) {
              $answer = $answers[$j];
              $csv .= ',';
              if ($answer != '') {
                if ($mode == 'numeric') {
                  if (isset($label_indexes[$answer])) {
                    $csv .= $label_indexes[$answer];
                  }
                  if ($is_random) {
                    $csv .= ',' . $label_indexes[$correct[$j]];
                  }
                } else {
                  $csv .= $answer;
                  if ($is_random) {
                    $csv .= ',' . $correct[$j];
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
                  $csv .= ',"' . $part_ans . '"';
                } else {
                  if ($part_ans == 'y') {
                    $csv .= ',"' . $correct_text_parts[$char_pos + 1] . '"';
                  } else {
                    $csv .= ',';
                  }
                }
                if ($is_random) {
                  if ($mode == 'numeric') {
                    $csv .= ',' . substr($correct_clean, $char_pos, 1);
                  } else {
                    if (substr($correct_clean, $char_pos, 1) == 'y') {
                      $csv .= ',"' . $correct_text_parts[$char_pos + 1] . '"';
                    } else {
                      $csv .= ',';
                    }
                  }
                }
              }
              $char_pos = substr_count($question['correct'],',') + 1;
              if ($question['score_method'] == 'other') {
                $part_ans = (isset($individual[$tmp_screen][$tmp_question_ID])) ? substr($individual[$tmp_screen][$tmp_question_ID], $char_pos + 1) : '';
                $csv .= ',"' . $part_ans . '"';
                if ($is_random) {
                  $csv .= ',';
                }
              }
            }
            break;
          case 'textbox':
            if (!isset($excluded[$tmp_question_ID])) {
              if (isset($individual[$tmp_screen][$tmp_question_ID])) {
                $tmp_data = trim($individual[$tmp_screen][$tmp_question_ID]);
              } else {
                $tmp_data = '<unanswered>';
              }
              $tmp_data = preg_replace("/(\r\n|\n|\r)/", "", $tmp_data);
              $tmp_data = str_replace('"',"'",$tmp_data);

              if (substr($tmp_data,0,1) == '-') $tmp_data = trim(substr($tmp_data,1));
              $csv .= ',"' . $tmp_data . '"';
            }
            break;
          case 'sct':
            if (!isset($excluded[$tmp_question_ID])) {
              $correct_text_parts = explode("\t", $question['correct_text']);
              $csv .= ',"';
              if (isset($individual[$tmp_screen][$tmp_question_ID]) and $individual[$tmp_screen][$tmp_question_ID] != 'u') {
                if ($mode == 'numeric') {
                  $csv .= $individual[$tmp_screen][$tmp_question_ID];
                } else {
                  $csv .= $correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]];
                }
              }
              $csv .= '"';
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
                $csv .= ',"' . $correct . '"';
              }
            }
            break;
          case 'random':
            // This should only happen if the user answered a question that the user answered has been
            // unlinked from the random question.
            $csv .= ',"' . $string['error_random'] . '"';
            break;
          default:
            if (!isset($excluded[$tmp_question_ID])) {
              $correct_text_parts = explode("\t", $question['correct_text']);
              $csv .= ',"';
              if (isset($individual[$tmp_screen][$tmp_question_ID]) and $individual[$tmp_screen][$tmp_question_ID] != 'u') {
                if ($mode == 'numeric') {
                  $csv .= $individual[$tmp_screen][$tmp_question_ID];
                } else {
                  if (isset($correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]])) {
                    $csv .= $correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]];
                  }
                }
              }
              $csv .= '"';
              if ($is_random) {
                if ($mode =='numeric') {
                  $csv .= ',"' . ltrim($question['correct'], ',') . '"';
                } else {
                  $csv .= ',"' . $correct_text_parts[ltrim($question['correct'], ',')] . '"';
                }
              }
            }
            break;
        }
      }
    }
    $csv .= "\n";
    $row_written++;
  }
} else {
  $csv .= $string['nodata'];
}

echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");
