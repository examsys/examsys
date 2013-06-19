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
* @author Nikodem Miranowicz, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/class_totals.inc';
require_once '../include/errors.inc';

require_once '../classes/paperutils.class.php';
require_once '../classes/paperproperties.class.php';

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli);
if ($propertyObj == false) {  // No properties found, this crypt_name
  $notice->access_denied($mysqli, $string, $string['error_paper'], true, true);    //this will exit php
}

header('Pragma: public');
header('Content-type: application/octet-stream');
header("Content-Disposition: attachment; filename=new_" . str_replace(' ', '_', $paper) . "_EB.csv");

$displayDebug = false; //disable debug output in this script as it effects the output

function get_correct_labels($question, $tmp_exclude) {
  $correct_labels = array();

  $tmp_first_split = explode(';', $question['correct'][0]);
  $tmp_second_split = explode('$', $tmp_first_split[11]);
  $i = 0;
  $excluded_no = 0;
  for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
    if (substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no - 2] > 219) {
      if (substr($tmp_exclude,$i,1) == '0') {
        $x = $tmp_second_split[$label_no-2];
        $y = $tmp_second_split[$label_no-1] - 25;
        $correct_labels[$x . 'x' . $y] = substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'));
      } else {
        $excluded_no++;
      }
      $i++;
    }
  }

  return $correct_labels;
}
$numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii', 'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx');

$user_modules = array();
if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
  $tmp_moduleID_in = $_GET['repmodule'];
} else {
  $tmp_moduleID_in = $moduleID_in;
}

$global_users = '';
$calendar_year = $propertyObj->get_calendar_year();
if ($calendar_year == '' or $calendar_year == '2006/07' or $calendar_year == '2007/08') {  // HACK: we don't have students by year before 2008/09
  $mod_query = $mysqli->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in)");
} else {
  $mod_query = $mysqli->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in) AND calendar_year = ?");
  $mod_query->bind_param('s', $calendar_year);
}
$mod_query->execute();
$mod_query->bind_result($idMod, $tmp_userID, $tmp_moduleid);
$mod_query->store_result();
while ($mod_query->fetch()) {
  $user_modules[$tmp_userID]['idMod'] = $idMod;
  if ($global_users == '') {
    $global_users = $tmp_userID;
  } else {
    $global_users .= ',' . $tmp_userID;
  }
}
$mod_query->close();

//******************** got from assessment_data
$paper_type = $propertyObj->get_paper_type();
if (!isset($paper_type)) $paper_type = '0';
$exclude = '';
// Get order of the class.

$student_list = '';
if ($paper_type == '0') {
  $result = $mysqli->prepare("(SELECT log_metadata.userID, SUM(mark) AS total_mark FROM log0, log_metadata WHERE log0.metadataID = log_metadata.id AND paperID = ? AND started >= ? AND started <= ? AND userID IN ($global_users) GROUP BY log_metadata.userID, paperID, started) UNION ALL (SELECT log_metadata.userID, sum(mark) AS total_mark FROM log1, log_metadata WHERE log1.metadataID = log_metadata.id AND paperID = ? AND started >= ? AND started <= ? AND userID IN ($global_users) GROUP BY log_metadata.userID, paperID, started) ORDER BY total_mark");
  $result->bind_param('ississ', $paperID, $startdate, $enddate, $paperID, $startdate, $enddate);
} else {
  $result = $mysqli->prepare("SELECT log_metadata.userID, SUM(mark) AS total_mark FROM log$paper_type, log_metadata WHERE log$paper_type.metadataID = log_metadata.id AND paperID = ? AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? AND userID IN ($global_users) GROUP BY log_metadata.userID, paperID, started ORDER BY total_mark");
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

$csv = '';

if ($student_no > 0) {
  $log_array = array();
  $hits = 0;
  $rowID = 0;
  // Capture the log data.
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT DISTINCT sid.student_id, username, log_metadata.userID, title, surname, first_names, grade, gender, year, started, log0.q_id, user_answer, q_type, screen FROM (log0, log_metadata, questions, users) LEFT JOIN sid ON users.id = sid.userID WHERE log0.metadataID = log_metadata.id AND log0.q_id = questions.q_id AND log_metadata.userID IN ($student_list) AND paperID = ? AND users.id = log_metadata.userID AND (users.roles='Student' OR users.roles='graduate')$exclude AND grade LIKE ? AND started >= ? AND started <= ?) UNION ALL (SELECT DISTINCT sid.student_id, username, log_metadata.userID, title, surname, first_names, grade, gender, year, started, log1.q_id, user_answer, q_type, screen FROM (log1, log_metadata, questions, users) LEFT JOIN sid ON users.id = sid.userID WHERE log1.metadataID = log_metadata.id AND log1.q_id = questions.q_id AND log_metadata.userID IN ($student_list) AND paperID = ? AND users.id = log_metadata.userID AND (users.roles='Student' OR users.roles='graduate')$exclude AND grade LIKE ? AND started >= ? AND started <= ?) ORDER BY surname, first_names, started, userID");
    $result->bind_param('isssisss', $paperID, $_GET['repcourse'], $startdate, $enddate, $paperID, $_GET['repcourse'], $startdate, $enddate);
  } else {
    $result = $mysqli->prepare("SELECT DISTINCT sid.student_id, username, log_metadata.userID, title, surname, first_names, grade, gender, year, started, log$paper_type.q_id, user_answer, q_type, screen FROM (log$paper_type, log_metadata, questions, users) LEFT JOIN sid ON users.id = sid.userID WHERE log$paper_type.metadataID = log_metadata.id AND log$paper_type.q_id = questions.q_id AND log_metadata.userID IN ($student_list) AND paperID = ? AND users.id = log_metadata.userID AND (users.roles='Student' OR users.roles='graduate')$exclude AND grade LIKE ? AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY surname, first_names, started, userID");
    $result->bind_param('isss', $paperID, $_GET['repcourse'], $startdate, $enddate);
  }

  $result->execute();
  $result->bind_result($student_id, $username, $uID, $title, $surname, $first_names, $grade, $gender, $year, $started, $question_ID, $user_answer, $q_type, $screen);
  $old_username = '';
  while ($result->fetch()) {
    if ($old_username != $username or $old_started != $started) {
      $rowID++;
    }
    $log_array[$rowID][$screen][$question_ID] = $user_answer;
    $log_array[$rowID]['student_id'] = demo_replace_number($student_id, $demo);
    $log_array[$rowID]['userID'] = $uID;
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


//********************************


$row_written = 0;
foreach ($user_results as $individual) {
  $tmp_user_ID = $individual['username'];
  // Write out the headings.
  if ($row_written == 0) {
    // Only output personal data if assessment, do not show if survey.
    if ($paper_type < 3) {
      $csv .= '"' . $string['gender'] . '","' . $string['title'] . '","' . $string['surname'] . '","' . $string['firstnames'] . '","' . $string['studentid'] . '","' . $string['course'] . '","' . $string['year'] . '","' . $string['submitted'] . '"';
    } else {
      $csv .= '"' . $string['gender'] . '","' . $string['course'] . '","' . $string['year'] . '","' . $string['submitted'] . '"';
    }
    $q_no = 1;
    foreach ($paper_buffer as $q_id => $question) {
      if (array_key_exists($q_id,$excluded)) {
        $tmp_exclude = $excluded[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      // If a random question, get the first on the associated questions from the block. If none exist, output nothing
      $skip_random = false;
      if ($question['q_type'] == 'random') {
        if (isset($paper_buffer[$q_id]['random_questions']) and count($paper_buffer[$q_id]['random_questions']) > 0) {
          $question = reset($paper_buffer[$q_id]['random_questions']);

          if ($question['q_type'] == 'blank') {
            $tmp_q_id = key($paper_buffer[$q_id]['random_questions']);
            $question['correct'] = extract_blank_correct($question['option_text'][0], $question['display_method'], $paper_buffer, $tmp_q_id);
          }
          if ($question['q_type'] == 'labelling') {
            $question['correct_labels'] = get_correct_labels($question, $tmp_exclude);
          }
        } else {
          $skip_random = true;
        }
      }
      
      if (!$skip_random) {
        if ($question['q_type'] == 'extmatch' and $question['score_method'] == 'Mark per Option') {
          $sub_parts = 0;
          $paper_answers = explode('|', $question['correct'][0]);
          for ($a=0; $a<count($paper_answers); $a++) {
            $sub_parts += substr_count($paper_answers[$a], '$');
            if ($paper_answers[$a] != '' and substr($tmp_exclude, $a+$sub_parts, 1) == '0') {
              $num_ix = 0;
              $correct_subparts = explode('$', $paper_answers[$a]);
              foreach ($correct_subparts as $subpart) {
                $csv .= ',Q' . $q_no . $numerals[$a]. chr($num_ix + 65);
                $num_ix++;
              }
            }
          }
        } elseif ($question['q_type'] == 'matrix' and $question['score_method'] == 'Mark per Option') {
          $sub_parts = 0;
          $paper_answers = explode('|', $question['correct'][0]);
          for ($a=0; $a<count($paper_answers); $a++) {
            $sub_parts += substr_count($paper_answers[$a], '$');

            if ($paper_answers[$a] != '' and substr($tmp_exclude, $a+$sub_parts, 1) == '0') $csv .= ',Q' . $q_no . chr($a+65);
          }
        } elseif (($question['q_type'] == 'dichotomous' or $question['q_type'] == 'blank' or $question['q_type']=='rank') and $question['score_method'] != 'Mark per Question') {
          if (!array_key_exists($q_id, $excluded) || $question['q_type']!='rank') {
            for ($a=0; $a<count($question['correct']); $a++) {            
              if (substr($tmp_exclude, $a, 1) == '0' && ($question['q_type']!='rank' || $question['correct'][$a]>0)) $csv .= ',Q' . $q_no . chr($a+65);
            }
            if ($question['score_method'] == 'Bonus Mark') $csv .= ',Q' . $q_no.'_'.$string['bonus'];
          }
        } elseif ($question['q_type']=='mrq' and $question['score_method'] == 'Mark per Option') {
          if (!array_key_exists($q_id, $excluded)) {
            for ($a=0; $a<count($question['correct']); $a++) {
              if (substr($tmp_exclude, $a, 1) == '0' and $question['correct'][$a] == 'y') $csv .= ',Q' . $q_no . chr($a+65);
            }
          }
        } elseif ($question['q_type'] == 'labelling' and $question['score_method'] == 'Mark per Option') {
          for ($a=0; $a <(count($question['correct_labels']) + substr_count($tmp_exclude, '1')); $a++) {
            if (substr($tmp_exclude, $a, 1) == '0') $csv .= ',Q' . $q_no . chr($a+65);
          }
        } elseif ($question['q_type'] == 'hotspot' and $question['score_method'] == 'Mark per Option') {
          $paper_answers = explode('|', $question['correct'][0]);
          for ($a=0; $a<count($paper_answers); $a++) {
            if (substr($tmp_exclude, $a, 1) == '0') $csv .= ',Q' . $q_no . chr($a+65);
          }
        } else {
          if (!array_key_exists($q_id, $excluded)) $csv .= ',Q' . $q_no;
        }
        $q_no++;
      }
    }
    $csv .= "\n";
  }

  // Write out the raw data.
  if ($individual['visible'] == 1) {
    if ($paper_type < 3) {
      $csv .= '"' . $individual['gender'] . '","' . $individual['title'] . '","' . $individual['surname'] . '","' . $individual['first_names'] . '","' . $individual['student_id'] . '","' .$individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
    } else {
      $csv .= '"' . $individual['gender'] . '","' . $individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
    }
    
    foreach ($paper_buffer as $q_id => $question) {

      if (array_key_exists($q_id, $excluded)) {
        $tmp_exclude = $excluded[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      // If a random question, get the one that the user answered, otherwise just get the first and skip if none exist
      $skip_random = false;
      if ($question['q_type'] == 'random') {
        if (isset($paper_buffer[$q_id]['random_questions']) and count($paper_buffer[$q_id]['random_questions']) > 0) {
          $rnd_found = false;
          foreach ($paper_buffer[$q_id]['random_questions'] as $tmp_q_id => $tmp_q) {
            if (isset($individual['mark_array'][$tmp_q_id])) {
              $q_id = $tmp_q_id;
              $question = $tmp_q;
              $rnd_found = true;
              break;
            }
          }
          if (!$rnd_found) {
            $question = reset($paper_buffer[$q_id]['random_questions']);
            $q_id = key($paper_buffer[$q_id]['random_questions']);
          }
        } else {
          $skip_random = true;
        }
      }

      if (!$skip_random) {
        if (isset($individual['mark_array'][$q_id])) {
          if (is_array($individual['mark_array'][$q_id])) {
                         
            foreach ($individual['mark_array'][$q_id] as $mi => $tmp_mark) {
            
                // ----- parts (extmatch)-----
                
                $parts_test_fail = true;           
                if ($question['q_type'] == 'extmatch' and isset($log_array[$row_written])) {
                  $extmatch_parts = explode('|', $question['correct'][0]);
                  if (strpos($extmatch_parts[$mi], '$')!==false) {
                    $parts_test_fail = false;
                    
                    $answer = '';
                    foreach ($log_array[$row_written] as $kb => $vb) {
                      if (is_array($vb)) {
                        foreach ($vb as $kc => $vc) {
                          if ($q_id==$kc) $answer = preg_replace('/,/', '', $vc);
                        }
                      }
                    }        
                    $answer_parts = explode('|', $answer);
                    
                    $extmatch_parts_correct = explode('$', $extmatch_parts[$mi]);
                    $answer_subparts = explode('$', $answer_parts[$mi]);
                    
                    foreach ($extmatch_parts_correct as $qi => $question_part) {
                      if (in_array($question_part,$answer_subparts)) {
                        $csv .= ',1';
                      } else {
                        $csv .= ',0';
                      }
                    }                      
                  }
                }           
              if ($question['q_type'] != 'labelling' or substr($tmp_exclude,$mi,1) == '0') {
                if ($parts_test_fail) $csv .= ',' . (($tmp_mark > 0) ? 1:0);  
              }
            }
          } else {
            if (($question['q_type'] == 'mrq' or $question['q_type'] == 'rank') and $question['score_method'] != 'Mark per Question' and isset($log_array[$row_written])) {
              $answer = '';
              foreach ($log_array[$row_written] as $kb => $vb) {
                if (is_array($vb)) {
                  foreach ($vb as $kc => $vc) {
                    if ($q_id == $kc) $answer = preg_replace('/,/', '', $vc);
                  }
                }
              }
              $answer .= '                                   ';                  
              $bonus_q = $bonus_a = Array();
              foreach ($question['correct'] as $qi => $question_part) {
                if ($question_part != 'n' && ($question['q_type']!='rank' || $question_part>0)) {
                  if ($question['score_method'] == 'Bonus Mark') {
                    if ($question_part != '') {
                      array_push($bonus_q,$question_part);
                      array_push($bonus_a,$answer[$qi]);                           
                    }
                  } else {
                    $csv .= ',' . (($question_part == $answer[$qi]) ? 1:0);
                  }
                }
              }            
              if ($question['score_method'] == 'Bonus Mark') {
                $bonus_test = count($bonus_a);
                foreach ($bonus_a as $part_nr => $answer_part) {
                  $csv .= ',' . (in_array($answer_part,$bonus_q) ? 1:0);
                  $bonus_test -= (($answer_part==$bonus_q[$part_nr]) ? 1:0);
                }
                $csv .= ',' . (($bonus_test == 0) ? 1:0);
              }
            } else {
              $csv .= ',' . (($individual['mark_array'][$q_id] > 0) ? 1:0);
            }
          }
        } else {
          if (($question['q_type'] == 'extmatch' or $question['q_type'] == 'matrix') and $question['score_method'] == 'Mark per Option') {
            $sub_parts = 0;

            $paper_answers = explode("|", $question['correct'][0]);
            for ($a=0; $a<count($paper_answers); $a++) {
              if ($paper_answers[$a] != '' and substr($tmp_exclude,$a+$sub_parts,1) == '0') $csv .= '0'; 
            }
          } elseif (($question['q_type'] == 'dichotomous' or $question['q_type'] == 'blank') and $question['score_method'] == 'Mark per Option') {
            for ($a=0; $a<count($question['correct']); $a++) {
              if (substr($tmp_exclude,$a,1) == '0') $csv .= ',0';
            }
          } elseif ($question['q_type'] == 'labelling' and $question['score_method'] == 'Mark per Option') {
            for ($a=0; $a < count($question['correct_labels']); $a++) {
              if (substr($tmp_exclude, $a, 1) == '0') $csv .= ',0';
            }
          } elseif ($question['q_type'] == 'hotspot' and $question['score_method'] == 'Mark per Option') {
            $paper_answers = explode("|",$question['correct'][0]);
            for ($a=0; $a<count($paper_answers); $a++) {
              if (substr($tmp_exclude,$a,1) == '0') $csv .= ',0';
            }
          } else {
            if (!array_key_exists($q_id,$excluded)) $csv .= ',0';
          }
        }
      }
    }
    $csv .= "\n";
  }
  $row_written++;
}
} else {
  $csv .= $string['nodata'];
}
echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");

$mysqli->close();
?>