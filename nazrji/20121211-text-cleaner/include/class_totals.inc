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
* Class total reports, included functions used in:
*    
*    class_totals.php
*    class_totals_csv.php
*    class_totals_xml.php
*    assessment_marks.php
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

if ($userObject->has_role('Demo')) {
  $demo = true;
} else {
  $demo = false;
}
require_once '../include/calculate_marks.inc';
require_once '../include/demo_replace.inc';
require_once '../include/sort.inc';
require_once '../classes/paperutils.class.php';

function getRandomDetails($questionID, &$random_q_ids) {
  global  $configObject, $paper_buffer, $mysqli;

  $random_q_ids[] = $questionID;

  $question_no = 0;
  $random_questions = array();
  $old_q_id = '';
  $old_score_method = '';
  $old_q_media_width = '';
  $old_q_media_height = '';
  $old_correct = array();
  $old_option_text = array();
  
  $result = $mysqli->prepare("SELECT theme, options1.option_text, leadin, scenario, q_media_width, q_media_height, options2.correct, options2.marks_correct, options2.marks_incorrect, options2.option_text, q_type, display_method, score_method, last_edited, status FROM options AS options1, questions, options AS options2 WHERE options1.option_text=questions.q_id AND questions.q_id=options2.o_id AND options1.o_id=?");
  $result->bind_param('i', $questionID);
  $result->execute();
  $result->store_result();
  if ($result->num_rows > 0) {
    $result->bind_result($theme, $q_id, $leadin, $scenario, $q_media_width, $q_media_height, $correct, $marks_correct, $marks_incorrect, $option_text, $q_type, $display_method, $score_method, $display_last_edited, $status);
    while ($result->fetch()) {
      if ($old_q_id != $q_id and $old_q_id != '') {
        $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
        if (strlen($old_leadin) > 160) $old_leadin = substr($old_leadin,0,160) . '...';
        $random_questions[$old_q_id]['theme'] = $old_theme;
        $random_questions[$old_q_id]['q_id'] = $old_q_id;
        $random_questions[$old_q_id]['q_type'] = $old_q_type;
        $random_questions[$old_q_id]['leadin'] = $old_leadin;
        $random_questions[$old_q_id]['scenario'] = $old_scenario;
        $random_questions[$old_q_id]['correct'] = $old_correct;
        $random_questions[$old_q_id]['display_method'] = $old_display_method;
        $random_questions[$old_q_id]['score_method'] = $old_score_method;
        $random_questions[$old_q_id]['status'] = $old_status;
        $random_questions[$old_q_id]['display_last_edited'] = $display_last_edited;
        $random_questions[$old_q_id]['marks_correct'] = $old_marks_correct;
        $random_questions[$old_q_id]['marks_incorrect'] = $old_marks_incorrect;
        $random_questions[$old_q_id]['random_mark'] = qRandomMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
        $random_questions[$old_q_id]['option_text'] = $old_option_text;
        $old_correct = array();
        $old_option_text = array();
        $question_no++;
      }
      $old_theme = $theme;
      $old_q_id = $q_id;
      $old_q_type = $q_type;
      $old_leadin = $leadin;
      $old_scenario = $scenario;
      $old_status = $status;
      $old_marks_correct = $marks_correct;
      $old_marks_incorrect = $marks_incorrect;
      $old_correct[] = $correct;
      $old_option_text[] = $option_text;
      $old_display_method = $display_method;
      $old_score_method = $score_method;
      $old_q_media_width = $q_media_width;
      $old_q_media_height = $q_media_height;
    }

    // Write out the last question.
    $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
    if (strlen($old_leadin) > 160) $old_leadin = substr($old_leadin,0,160) . '...';
    $random_questions[$old_q_id]['theme'] = $old_theme;
    $random_questions[$old_q_id]['q_id'] = $old_q_id;
    $random_questions[$old_q_id]['q_type'] = $old_q_type;
    $random_questions[$old_q_id]['leadin'] = $old_leadin;
    $random_questions[$old_q_id]['scenario'] = $old_scenario;
    $random_questions[$old_q_id]['correct'] = $old_correct;
    $random_questions[$old_q_id]['display_method'] = $old_display_method;
    $random_questions[$old_q_id]['score_method'] = $old_score_method;
    $random_questions[$old_q_id]['status'] = $old_status;
    $random_questions[$old_q_id]['display_last_edited'] = $display_last_edited;
    $random_questions[$old_q_id]['marks_correct'] = $old_marks_correct;
    $random_questions[$old_q_id]['marks_incorrect'] = $old_marks_incorrect;
    $random_questions[$old_q_id]['random_mark'] = qRandomMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
    $random_questions[$old_q_id]['option_text'] = $old_option_text;
  }
  $result->close();

  $paper_buffer[$questionID]['random_questions'] = $random_questions;

  return $old_q_id;
}

function random_qMarks($random_questions) {
  $min = 999;
  $max = 0;

  foreach ($random_questions as $individual_question) {
    if ($individual_question['marks'] > $max) $max = $individual_question['marks'];
    if ($individual_question['marks'] < $min) $min = $individual_question['marks'];
  }

  if ($min == $max) {
    return $min;
  } else {
    return 'ERR';
  }
}

function checkDisplayExcluded(&$html, $q_id, $exclude, $q_no) {
 if (isset($exclude[$q_id])) {
   $subpart = '';
   if (strlen($exclude[$q_id]) > 1) {
     for ($i=0; $i<strlen($exclude[$q_id]); $i++) {
       if (substr($exclude[$q_id],$i,1) == '1') {
         if ($subpart == '') {
           $subpart = chr($i+97);
         } else {
           $subpart .= ', Q' . $q_no . chr($i+97);
         }
       }
     }
   }
   if ($html == '') {
     $html = 'Q' . $q_no . $subpart;
   } else {
     $html .= ', Q' . $q_no . $subpart;
   }
 }
}

function displayExperimental(&$html, $q_no) {
 if ($html == '') {
   $html = 'Q' . $q_no;
 } else {
   $html .= ', Q' . $q_no;
 }
}

function get_ss_pass(&$pass_score, &$distinction_score) {
  global $marking, $mysqli, $excluded, $total_marks;
  $mark_parts = explode(',',$marking);

  $ebel_marks = array();
  $ebel_percents = array();
  
  $std_types = array('EE', 'EI', 'EN', 'ME', 'MI', 'MN', 'HE', 'HI', 'HN', 'EE2', 'EI2', 'EN2', 'ME2', 'MI2', 'MN2', 'HE2', 'HI2', 'HN2');
  foreach ($std_types as $std_type) {
    $ebel_marks[$std_type] = 0;
    $ebel_percents[$std_type] = 0;
  }
  
  $std_total = 0;
  $question_no = 0;

  // Get question ratings.
  $result = $mysqli->prepare("SELECT questionID, rating, method, status, q_type, score_method FROM standards_setting, questions WHERE standards_setting.questionID=questions.q_id AND paperID=? AND setterID=? AND std_set=?");
  $result->bind_param('iis', $_GET['paperID'], $mark_parts[1], $mark_parts[2]);
  $result->execute();
  $result->bind_result($questionID, $rating, $method, $status, $q_type, $score_method);
  while ($result->fetch()) {
    if ($status != 'Experimental') {
      $individual_ratings = explode(',',$rating);
      $count_individual_ratings = count($individual_ratings);
      for ($i=0; $i<$count_individual_ratings; $i++) {
        $bad_bonus_mark = ($q_type == 'rank' and $score_method == 'Bonus Mark' and isset($excluded[$questionID]) and strpos($excluded[$questionID], '1') !== false);
        if ((!isset($excluded[$questionID]) or substr($excluded[$questionID],$i,1) == 0) and !$bad_bonus_mark) {
          if (in_array($individual_ratings[$i],$std_types)) $ebel_marks[$individual_ratings[$i]]++;
        }
        $std_total += $individual_ratings[$i];
        $question_no++;
      }
    }
  }
  $result->close();

  // Get the Ebel grid.
  if ($method == 'Ebel') {
    $result = $mysqli->prepare("SELECT category, percentage FROM ebel WHERE setterID=? AND date_set=?");
    $result->bind_param('is', $mark_parts[1], $mark_parts[2]);
    $result->execute();
    $result->bind_result($category, $percentage);
    while ($result->fetch()) {
      $ebel_percents[$category] = round($percentage, 2);
    }
    $result->close();

    $cut_marks = 0.0;
    $cut_marks += $ebel_marks['EE'] * $ebel_percents['EE'] * 100;
    $cut_marks += $ebel_marks['EI'] * $ebel_percents['EI'] * 100;
    $cut_marks += $ebel_marks['EN'] * $ebel_percents['EN'] * 100;
    $cut_marks += $ebel_marks['ME'] * $ebel_percents['ME'] * 100;
    $cut_marks += $ebel_marks['MI'] * $ebel_percents['MI'] * 100;
    $cut_marks += $ebel_marks['MN'] * $ebel_percents['MN'] * 100;
    $cut_marks += $ebel_marks['HE'] * $ebel_percents['HE'] * 100;
    $cut_marks += $ebel_marks['HI'] * $ebel_percents['HI'] * 100;
    $cut_marks += $ebel_marks['HN'] * $ebel_percents['HN'] * 100;
    $pass_score = ($cut_marks / ($total_marks  * 100)) * 100;
    $pass_score = round($pass_score,1);
    
    if ($ebel_percents['EE2'] === NULL) {
      $distinction_score = -1;
    } else {
      $cut_marks2 = 0.0;
      $cut_marks2 += $ebel_marks['EE'] * $ebel_percents['EE2'] * 100;
      $cut_marks2 += $ebel_marks['EI'] * $ebel_percents['EI2'] * 100;
      $cut_marks2 += $ebel_marks['EN'] * $ebel_percents['EN2'] * 100;
      $cut_marks2 += $ebel_marks['ME'] * $ebel_percents['ME2'] * 100;
      $cut_marks2 += $ebel_marks['MI'] * $ebel_percents['MI2'] * 100;
      $cut_marks2 += $ebel_marks['MN'] * $ebel_percents['MN2'] * 100;
      $cut_marks2 += $ebel_marks['HE'] * $ebel_percents['HE2'] * 100;
      $cut_marks2 += $ebel_marks['HI'] * $ebel_percents['HI2'] * 100;
      $cut_marks2 += $ebel_marks['HN'] * $ebel_percents['HN2'] * 100;
      $distinction_score = (($cut_marks2 / ($total_marks * 100)) * 100);
      $distinction_score = round($distinction_score,1);
    }
  } else {
    // Modified Angoff method.
    if ($question_no > 0) {
      $pass_score = round($std_total/$question_no,1);
    } else {
      $pass_score = 0;
    }
  }

  if ($distinction_score == 0) {
    $distinction_score = get_ss_hon();
  }
}

function get_ss_hon() {
  global $user_results, $user_no;

  $marks_data = array();
  for ($i=0; $i<$user_no; $i++) {
    if (isset($user_results[$i]['percent'])) $marks_data[] = $user_results[$i]['percent'];
  }

  return mypercentile($marks_data,0.2);
}

function mypercentile($data,$percentile) {
  $count = count($data);
  if ($count == 0) {
    return '';
  }
  if (0 < $percentile and $percentile < 1 ) {
    $p = $percentile;
  } elseif( 1 < $percentile and $percentile <= 100 ) {
    $p = $percentile * .01;
  } else {
    return '';
  }
  $allindex = ($count-1) * $p;
  $intvalindex = intval($allindex);
  $floatval = $allindex - $intvalindex;
  rsort($data);
  if (!is_float($floatval)){
    $result = $data[$intvalindex];
  } else {
    if ($count > $intvalindex+1) {
      $result = $floatval*($data[$intvalindex+1] - $data[$intvalindex]) + $data[$intvalindex];
    } else {
      $result = $data[$intvalindex];
    }
  }
  return $result;
}

function crankMark($tmp_percent, $ss_pass, $ss_hon, $pass_mark, $distinction_mark) {
  if ($ss_hon > 0 and $ss_hon < 100) {
    if ($tmp_percent < $ss_pass) {
      $cranked = $pass_mark  - ($pass_mark - 0) * (($ss_pass - $tmp_percent) / ($ss_pass - 0));
    } elseif ($tmp_percent >= $ss_pass and $tmp_percent < $ss_hon) {
      $cranked = $distinction_mark  - ($distinction_mark - $pass_mark) * (($ss_hon - $tmp_percent) / ($ss_hon - $ss_pass));
    } else {
      $cranked = 100 - (100 - $distinction_mark) * ((100 - $tmp_percent) / (100 - $ss_hon));
    }
  } else {
    if ($tmp_percent < $ss_pass) {
      $cranked = $pass_mark  - ($pass_mark - 0) * (($ss_pass - $tmp_percent) / ($ss_pass - 0));
    } else {
      $cranked = 100 - (100 - $pass_mark) * ((100 - $tmp_percent) / (100 - $ss_pass));
    }
  }
  return round($cranked);
}

function writeUserResults($tmp, &$master_array, &$user_number, $tmp_user_mark, $tmp_user_mark_array, $tmp_user_duration, $marking_comp, $met_array, $student_met_array) {
  global $student_cohort, $cohort_size, $total_random_mark, $total_marks, $marking, $ss_pass, $ss_hon, $pass_mark, $demo, $display_no, $meta_col_count;
  $master_array[$user_number]['name'] = $tmp['name'];
  $master_array[$user_number]['mark'] = round($tmp_user_mark,1);
  $master_array[$user_number]['mark_array'] = $tmp_user_mark_array;
  if ($total_marks == 0) {
    $master_array[$user_number]['percent'] = 0;
    $master_array[$user_number]['adj_percent'] = 0;
  } else {
    if ($marking == '1') {                                   // Monkey mark
      $master_array[$user_number]['percent'] = round(($tmp_user_mark/$total_marks)*100);
      $master_array[$user_number]['adj_percent'] = round((($tmp_user_mark-$total_random_mark)/($total_marks-$total_random_mark))*100);
    } elseif ($marking{0} == '2') {                 // Standards Setting
      $master_array[$user_number]['percent'] = ($tmp_user_mark/$total_marks)*100;
      $master_array[$user_number]['adj_percent'] = ($tmp_user_mark/$total_marks)*100;
    } else {                                                 // Unadjusted mark
      $master_array[$user_number]['percent'] = round(($tmp_user_mark/$total_marks)*100);
      $master_array[$user_number]['adj_percent'] = round(($tmp_user_mark/$total_marks)*100);
    }
  }
  
  $master_array[$user_number]['started'] = $tmp['started'];
  $master_array[$user_number]['username'] = $tmp['username'];
  $master_array[$user_number]['tmp_userID'] = $tmp['tmp_userID'];
  $master_array[$user_number]['student_grade'] = $tmp['grade'];
  $master_array[$user_number]['roles'] = $tmp['roles'];
  $master_array[$user_number]['module'] = $tmp['module'];
  $master_array[$user_number]['attempt'] = $tmp['attempt'];
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
  $master_array[$user_number]['room'] = $tmp['room'];
  $master_array[$user_number]['visible'] = 1;    // Default to visible unless switched off below.
  
  // Add metadata
  if (!empty($met_array)) {
    foreach ($met_array as $type) {
      if (isset($student_met_array[$tmp['tmp_userID']][$type]) ) {
        $master_array[$user_number]['meta_' . $type] = $student_met_array[$tmp['tmp_userID']][$type];
      } else {
        $master_array[$user_number]['meta_' . $type] = '';
      }
    }
  }
  
  // Check metadata exclusions
  $i = 1;
  while (isset($_GET["meta$i"])) {
    $meta_parts = explode('=', $_GET["meta$i"]);
    if ($meta_parts[1] != '%') {
      if ($master_array[$user_number][$meta_parts[0]] != $meta_parts[1]) {
        $master_array[$user_number]['visible'] = 0;
      }
    }
    $i++;
  }
  if ($master_array[$user_number]['visible'] == 1) {
    $display_no++;
  }
  
  for ($i=0; $i<$cohort_size; $i++) {
    if ($student_cohort[$i]['username'] == $tmp['username']) {
      $student_cohort[$i]['taken'] = 1;
    }
  }
  $master_array[$user_number]['marking_complete'] = $marking_comp;

  $user_number++;
}

function find_random_question($paper_buffer, $q_id, $random_q_ids, &$tmp_q) {
  $tmp_q_id = -1;

  foreach ($random_q_ids as $rnd_id) {
    if (isset($paper_buffer[$rnd_id]) and isset($paper_buffer[$rnd_id]['random_questions']) and count($paper_buffer[$rnd_id]['random_questions']) > 0) {
      if (in_array($q_id, array_keys($paper_buffer[$rnd_id]['random_questions']))) {
        $tmp_q_id = $q_id;
        $tmp_q = $paper_buffer[$rnd_id]['random_questions'][$q_id];
      }
    }
  }

  return $tmp_q_id;
}

function extract_blank_correct($option_text, $display_method, $paper_buffer, $question_id) {
  $correct = array();

  $not_used = preg_match('/mark="([0-9]{1,3})"/', $option_text, $results);
  $blank_details = explode("[blank",$option_text);
  $no_answers = count($blank_details) - 1;
  for ($i=1; $i<=$no_answers; $i++) {
    $blank_details[$i] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$blank_details[$i]);
    $blank_details[$i] = preg_replace("| size=\"([0-9]{1,3})\"|","",$blank_details[$i]);
    $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
    $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
    $answer_list = explode(',',$blank_details[$i]);
    $answer_list[0] = str_replace("[/blank]",'',$answer_list[0]);
    if ($display_method == 'textboxes') {
      foreach ($answer_list as $individual_answer) {
        $correct[$i-1][] = html_entity_decode(trim($individual_answer));
      }
    } else {
      foreach ($answer_list as $tmp_answer) {
        $correct[$i-1][] = html_entity_decode(trim($tmp_answer));
      }
    }
  }

  return $correct;
}

$multi_part_qns = array('extmatch'=>1, 'matrix'=>1, 'blank'=>1, 'dichotomous'=>1, 'labelling'=>1, 'hotspot'=>1);
function getUserMark($q_id, $tmp_user_answer, $tmp_user_mark, $exclude, $status, &$tmp_mark, &$tmp_user_mark_array, $random_q_ids, $tmp_userID) {
  global $paper_buffer, $user_results, $tmp_array, $multi_part_qns, $explodeCache;
    
  if ($status == 'Experimental') $exclude[$q_id] = '1111111111111111111111111111111111111111';

  $skip_random = false;
  if (!isset($paper_buffer[$q_id])) {
    $tmp_q = array();
    $tmp_q_id = find_random_question($paper_buffer, $q_id, $random_q_ids, $tmp_q);
    if ($tmp_q_id == -1) {
      $skip_random = true;
    } else {
      $question = $tmp_q;

      if ($question['q_type'] == 'blank') {
        $question['correct'] = extract_blank_correct($question['option_text'][0], $question['display_method'], $paper_buffer, $tmp_q_id);
      }
    }
  } else {
    $question = &$paper_buffer[$q_id];
  }
  
  if (!$skip_random and $question['score_method'] != 'Mark per Question' and isset($multi_part_qns[$question['q_type']])) {
    if ($question['q_type'] == 'extmatch' or $question['q_type'] == 'matrix') {
      if (!isset($tmp_user_mark_array[$q_id])) $tmp_user_mark_array[$q_id] = array();
      $paper_answers = explode('|', $question['correct'][0]);
      $user_answers = explode('|', $tmp_user_answer);
      if (isset($exclude[$q_id])) {
        $tmp_exclude = $exclude[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      $section = 0;
      $count_paper_answers = count($paper_answers);
      for ($a=0; $a<$count_paper_answers; $a++) {
        if ($paper_answers[$a] != '') {
          $answers_correct = 0;
          $answers_incorrect = 0;          
          if ($question['q_type'] == 'extmatch') {
            $sub_paper_answers = explode('$', $paper_answers[$a]);
            if (isset($user_answers[$a])) {
              $sub_user_answers = explode('$', $user_answers[$a]);
            } else {
              $sub_user_answers = array();
            }
            $exclude_on = true;
            $count_sub_paper_answers = count($sub_paper_answers);
            for ($c=0; $c<$count_sub_paper_answers; $c++) {
              if ($tmp_exclude{$section} == '0') {
                if (!isset($sub_user_answers[$c]) or $sub_user_answers[$c] == '' or $sub_user_answers[$c] == 'u') {
                  // Do nothing
                } elseif (in_array($sub_user_answers[$c], $sub_paper_answers)) {
                  $answers_correct++;
                } else {
                  $answers_incorrect++;
                }
                $exclude_on = false;
              }
              $section++;
            }
            
            $section_mark = ($answers_correct * $question['marks_correct']) + ($answers_incorrect * $question['marks_incorrect']);
            $tmp_mark += $section_mark;
            // This is excluding whole question parts (not answers/marks).
            if ($exclude_on == false) $tmp_user_mark_array[$q_id][] = $section_mark;
          } else {   // Matrix
            if ($tmp_exclude{$a} == '0') {
              if ($paper_answers[$a] == $user_answers[$a]) {
                $tmp_mark += $question['marks_correct'];
                $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
              } elseif ($user_answers[$a] != 'u' and $user_answers[$a] != '') {
                $tmp_mark += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
              } else {
                $tmp_user_mark_array[$q_id][] = 0;
              }
            }
          }
        }
      }
    } elseif ($question['q_type'] == 'blank') {
      $user_answers = explode('|', $tmp_user_answer);
      if (isset($exclude[$q_id])) {
        $tmp_exclude = $exclude[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      $count_user_awnser = count($user_answers)-1;
      for ($a=0; $a<$count_user_awnser; $a++) {
        if ($tmp_exclude{$a} == '0') {
          if ($question['display_method'] == 'dropdown') {
            if (str_replace('&nbsp;',' ',$question['correct'][$a][0]) == html_entity_decode(str_replace('&nbsp;',' ',$user_answers[$a+1]))) {
              $tmp_mark += $question['marks_correct'];
              $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
            } elseif ($user_answers[$a+1] != 'u' and $user_answers[$a+1] != '') {
              $tmp_mark += $question['marks_incorrect'];
              $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
            } else {
              $tmp_user_mark_array[$q_id][] = 0;
            }
          } else {
            $match = false;
            if (isset($question['correct'][$a])) {
              foreach ($question['correct'][$a] as $correct_alternative) {
                if (strtolower(trim($user_answers[$a+1])) == strtolower(trim($correct_alternative))) {
                  $match = true;
                }
              }
            }
            
            if ($match == true) {
              $tmp_mark += $question['marks_correct'];
              $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
            } elseif ($user_answers[$a+1] != 'u' and $user_answers[$a+1] != '') {
              $tmp_mark += $question['marks_incorrect'];
              $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
            } else {
              $tmp_user_mark_array[$q_id][] = 0;
            }
          }
        }
      }
    } elseif ($question['q_type'] == 'dichotomous') {
      $count_question_correct = count($question['correct']);
      for ($a=0; $a<$count_question_correct; $a++) {
        if (isset($exclude[$q_id])) {
          $tmp_exclude = $exclude[$q_id];
        } else {
          $tmp_exclude = '0000000000000000000000000000000000000000';
        }
        if ($tmp_exclude{$a} == '0') {
          if ($question['correct'][$a] == substr($tmp_user_answer,$a,1)) {
            $tmp_mark += $question['marks_correct'];
            $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
          } else {
            if (substr($tmp_user_answer, $a, 1) == 'a' or substr($tmp_user_answer,$a,1) == 'u') {
              $tmp_user_mark_array[$q_id][] = 0;
            } else {
              $tmp_mark += $question['marks_incorrect'];
              $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
            }
          }
        }
      }
    } elseif ($question['q_type'] == 'hotspot') {
      if (isset($exclude[$q_id])) {
        $tmp_exclude = $exclude[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      $question_parts = explode('|', $question['correct'][0]);
      $user_answers = explode('|', $tmp_user_answer);
      $count_question_parts = count($question_parts);
      for ($i=0; $i<$count_question_parts; $i++) {
        if ($tmp_exclude{$i} == '0') {
          if (isset($user_answers[$i]) and substr($user_answers[$i],0,1) == '1') {
            $tmp_mark += $question['marks_correct'];
            $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
          } elseif (isset($user_answers[$i]) and substr($user_answers[$i],0,1) == '0') {
            $tmp_mark += $question['marks_incorrect'];
            $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
          } else {
            $tmp_user_mark_array[$q_id][] = 0;
          }
        }
      }
    } elseif ($question['q_type'] == 'labelling') {
      if (isset($exclude[$q_id])) {
        $tmp_exclude = $exclude[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
          
      $correct_labels = array();
      
      $tmp_first_split = explode(';', $question['correct'][0]);
      $tmp_second_split = explode('$', $tmp_first_split[11]);

      $label_count = 0;
      $placeholders = 0;
      $i = 0;
      $excluded_no = 0;
      $count_tmp_second_split = count($tmp_second_split);
      for ($label_no = 4; $label_no <= $count_tmp_second_split; $label_no += 4) {
        if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
        if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
          if ($tmp_exclude{$i} == '0') {
            $x = $tmp_second_split[$label_no-2];
            $y = $tmp_second_split[$label_no-1] - 25;
            $correct_labels[$x . 'x' . $y] = substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'));
            $placeholders++;
          } else {
            $excluded_no++;
          }
          $i++;
        }
      }

      // Strip out width and height for graphical labels.
      foreach ($correct_labels as $key=>$value) {
        $tmp_parts = explode('~', $value);
        $correct_labels[$key] = $tmp_parts[0];
      }
   
      $user_split1 = explode(';', $tmp_user_answer);
      $user_split2 = explode('$', $user_split1[1]);

      $i = 0;
      $correct = 0;
      $count_user_split2 = count($user_split2)-3;
      for ($a=0; $a<$count_user_split2; $a+=4) {
        $x = $user_split2[$a];
        $y = $user_split2[$a+1];

        if (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] == $user_split2[$a+2]) {
          $tmp_mark += $question['marks_correct'];
          $correct += $question['marks_correct'];
          $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
        } elseif (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] != $user_split2[$a+2]) {
          $tmp_mark += $question['marks_incorrect'];
          $correct += $question['marks_incorrect'];
          $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
        } else {
          $tmp_user_mark_array[$q_id][] = 0;
        }
        $i++;
      }
      
      // If the user has not answered the question pad out with zero marks.
      if (isset($tmp_user_mark_array[$q_id])) {
        $user_answer_no = count($tmp_user_mark_array[$q_id]);
      } else {
        $user_answer_no = 0;
      }
      if ($user_answer_no < count($correct_labels)) {
        for ($i=$user_answer_no; $i<count($correct_labels); $i++) {
          $tmp_user_mark_array[$q_id][] = 0;
        }
      }
      
      $question['correct_labels'] = $correct_labels;
    }
  } else {
    // Marking per Question, or all other question types, simply return the original mark.
    if (!isset($exclude[$q_id])) {
      $round_tmp_user_mark = round($tmp_user_mark, 2);
      $tmp_mark += $round_tmp_user_mark;
      $tmp_user_mark_array[$q_id] = $round_tmp_user_mark;
    }
  }
}

function formatsec($seconds) {
  $diff_hour = ($seconds / 60) / 60;
  $tmp_position = strpos($diff_hour, '.');
  if ($tmp_position > 0) $diff_hour = substr($diff_hour, 0, $tmp_position);
  if ($diff_hour > 0) $seconds -= ($diff_hour * 60) * 60;
  $diff_min = $seconds / 60;
  $tmp_position = strpos($diff_min, '.');
  if ($tmp_position > 0) $diff_min = substr($diff_min, 0, $tmp_position);
  if ($diff_min > 0) $seconds -= $diff_min * 60;
  $diff_sec = $seconds;
  $timestring = '';
  if ($diff_hour < 10) $timestring = '0';
  $timestring .= "$diff_hour:";
  if ($diff_min < 10) $timestring .= '0';
  $timestring .= "$diff_min:";
  if ($diff_sec < 10) $timestring .= '0';
  $timestring .= $diff_sec;
  return $timestring;
}

set_time_limit(0);

$paperID    = $_GET['paperID'];
$startdate  = $_GET['startdate'];
$enddate    = $_GET['enddate'];

$row_count      = 0;
$old_q_id       = -1;
$old_screen     = 0;
$old_userID     = '';
$old_title      = '';
$old_surname    = '';
$old_started    = '';
$old_display_started = '';
$old_duration   = 0;
$old_ipaddress  = '';
$temp_mark      = 0;
$temp_time      = '';
$total_mark     = 0;
$total_adjusted_mark = 0;
$total_time     = 0;
$completed_no   = 0;
$max_mark       = 0;
$min_mark       = 9999;
$user_duration  = 0;

$old_theme = '';
$old_scenario = '';
$old_leadin = '';
$old_media = '';
$old_type = '';
$old_score_method = '';

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

// Load the correct answers into 'paper_buffer' array.
$result = $mysqli->prepare("SELECT q_id, marks_correct, marks_incorrect, display_method, score_method, q_media_height, q_media_width, paper_title, marking, pass_mark, distinction_mark, paper_ownerID, q_type, paper_type, correct, score_method, option_text, calendar_year, status, display_pos, crypt_name FROM (papers, questions, options, properties) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND papers.paper=? AND questions.q_id=options.o_id AND q_type!='info' ORDER BY screen, display_pos, id_num");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->store_result();
$result->bind_result($q_id, $marks_correct, $marks_incorrect, $display_method, $score_method, $q_media_height, $q_media_width, $paper_title, $marking, $pass_mark, $distinction_mark, $tmp_ownerID, $q_type, $paper_type, $correct, $score_method, $option_text, $calendar_year, $status, $display_pos, $crypt_name);
$old_q_id = '';
$question_no = 0;
$total_marks = 0;
$orig_total_marks = 0;
$total_random_mark = 0;
$display_excluded = '';
$display_experimental = '';
$old_correct = array();
$old_option_text = array();
$stems = 0;
$random_q_ids = array();

while ($result->fetch()) {
  if ($q_id != $old_q_id or $old_display_pos != $display_pos) {
    if ($old_q_id != 0) {
      if ($old_status != 'Experimental') {
        if (isset($excluded[$old_q_id])) {
          $tmp_exclude = $excluded[$old_q_id];
        } else {
          $tmp_exclude = '';
        }
        if ($old_q_type == 'random') {
          $tmp_id = getRandomDetails($old_q_id, $random_q_ids);

          $last_random = $paper_buffer[$old_q_id]['random_questions'][$tmp_id];

          $old_q_type = $last_random['q_type'];
          $old_marks_correct = $last_random['marks_correct'];
          $old_option_text = $last_random['option_text'];
          $old_correct = $last_random['correct'];
          $old_display_method = $last_random['display_method'];
          $old_score_method = $last_random['score_method'];
          $total_marks += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
          $orig_total_marks += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        } else {
          $total_marks += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
          $orig_total_marks += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
          $total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
          checkDisplayExcluded($display_excluded, $old_q_id, $excluded, $question_no);
        }
      } else {
      	displayExperimental($display_experimental, $question_no);
      }
      $stems = 0;
      $old_marks_correct = 0;
    }
    $question_no++;
    $option_no = 0;
    $old_correct = array();
    $old_option_text = array();
    $question_id = $q_id;
    $paper_buffer[$question_id]['q_type'] = $q_type;
    $paper_buffer[$question_id]['score_method'] = $score_method;
    $paper_buffer[$question_id]['display_method'] = $display_method;
    $paper_buffer[$question_id]['marks_correct'] = $marks_correct;
    $paper_buffer[$question_id]['marks_incorrect'] = $marks_incorrect;

    if ($q_type == 'blank') {
      $paper_buffer[$question_id]['correct'] = extract_blank_correct($option_text, $score_method, $paper_buffer, $question_id);
    } else {
      $paper_buffer[$question_id]['correct'][$option_no] = $correct;
    }
    $paper = $paper_title;
  } else {
    $paper_buffer[$question_id]['correct'][$option_no] = $correct;
  }
  $option_no++;
  $old_q_id = $q_id;
  $old_display_pos = $display_pos;
  $old_q_type = $q_type;
  $old_display_method = $display_method;
  $old_score_method = $score_method;
  $old_correct[] = $correct;
  $old_q_media_width = $q_media_width;
  $old_q_media_height = $q_media_height;
  $old_option_text[] = $option_text;
  $old_marks_correct = $marks_correct;
  $old_marks_incorrect = $marks_incorrect;
  $old_status = $status;
  $stems++;
}
$result->close();
if ($old_status != 'Experimental') {
  if (isset($excluded[$old_q_id])) {
    $tmp_exclude = $excluded[$old_q_id];
  } else {
    $tmp_exclude = '';
  }
  if ($old_q_type == 'random') {
    $tmp_id = getRandomDetails($old_q_id, $random_q_ids);

    $last_random = $paper_buffer[$old_q_id]['random_questions'][$tmp_id];

    $old_q_type = $last_random['q_type'];
    $old_marks_correct = $last_random['marks_correct'];
    $old_option_text = $last_random['option_text'];
    $old_correct = $last_random['correct'];
    $old_display_method = $last_random['display_method'];
    $old_score_method = $last_random['score_method'];
    $total_marks += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
    $orig_total_marks += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
  } else {
    $total_marks += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
    $orig_total_marks += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
    $total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
    checkDisplayExcluded($display_excluded, $old_q_id, $excluded, $question_no);
  }
} else {
	displayExperimental($display_experimental, $question_no);
}

$cohort_size = 0;
$out_of_range = 0;
if ($_GET['absent'] == 1) {
  // Get students in the cohort.
  $student_cohort = array();
  $result = $mysqli->prepare("SELECT DISTINCT users.id, users.username, title, surname, first_names, initials, student_id, grade, moduleid, gender FROM (student_modules, users, sid) WHERE student_modules.userID=sid.userID AND student_modules.userID=users.id AND users.roles LIKE '%Student%' AND moduleid IN ('" . str_replace(",","','",$moduleID) . "') AND calendar_year=?");
  $result->bind_param('s', $calendar_year);
  $result->execute();
  $result->bind_result($tmp_userID, $tmp_username, $title, $surname, $first_names, $initials, $student_id, $grade, $moduleid, $gender);
  while ($result->fetch()) {
    $student_cohort[$cohort_size]['username'] = $tmp_username;
    $student_cohort[$cohort_size]['tmp_userID'] = $tmp_userID;
    $student_cohort[$cohort_size]['name'] = str_replace("'","",$surname) . ',' . $first_names;
    $student_cohort[$cohort_size]['title'] = $title;
    $student_cohort[$cohort_size]['surname'] = demo_replace($surname, $demo);
    $student_cohort[$cohort_size]['first_names'] = demo_replace($first_names, $demo);
    $student_cohort[$cohort_size]['initials'] = demo_replace($initials, $demo);
    $student_cohort[$cohort_size]['student_id'] = demo_replace_number($student_id, $demo);
    $student_cohort[$cohort_size]['grade'] = $grade;
    $student_cohort[$cohort_size]['module'] = $moduleid;
    $student_cohort[$cohort_size]['gender'] = $gender;
    $student_cohort[$cohort_size]['taken'] = 0;
    $cohort_size++;
  }
  $result->close();
}

if ($paper_type == '2') {
  // Get ipaddresses.
  $ipaddresses = array();
  $result = $mysqli->prepare("SELECT name, address FROM (ip_addresses, labs) WHERE ip_addresses.lab=labs.id");
  $result->execute();
  $result->bind_result($lab_name, $lab_ip_address);
  while ($result->fetch()) {
    $ipaddresses[$lab_ip_address] = $lab_name;
  }
  $result->close();
}

// Get all the users on the module(s) the paper is on.

$user_modules = array();
$moduleID = Paper_utils::get_modules($_GET['paperID'],$mysqli);
$moduleID_in =  "'" . implode("','",array_keys($moduleID)) . "'";
$mod_query = $mysqli->prepare("SELECT userID, moduleID, MAX(attempt) FROM modules_student,modules WHERE modules_student.idMod = modules.id AND idMod IN ($moduleID_in) and calendar_year = ? GROUP BY userID,moduleID");
$mod_query->bind_param('s',$calendar_year);
$mod_query->execute();
$mod_query->bind_result($tmp_userID, $tmp_moduleid, $attempt);
$mod_query->store_result();
while ($mod_query->fetch()) {
  $user_modules[$tmp_userID]['moduleid'] = $tmp_moduleid;
}
$mod_query->close();

// Query for metadata
$metadata_array = array();
$student_metadata_array = array();
$moduleID_in =  "'" . implode("','",array_keys($moduleID)) . "'";
$stmt = $mysqli->prepare("SELECT userID, type, value FROM users_metadata, modules WHERE users_metadata.idMod=modules.id AND modules.id IN ($moduleID_in)");
$stmt->execute();
$stmt->bind_result($student_userID, $type, $value);
while ($stmt->fetch()) {
  $student_metadata_array[$student_userID][$type] = $value;
  $metadata_array[$type] = $type;
}
$stmt->close();

if (isset($_GET['studentsonly']) and $_GET['studentsonly'] == 0) {
  $roles_sql = '';
} else {
  $roles_sql = " AND (users.roles='Student' OR users.roles='graduate')";
}

// Get log data.
if ($paper_type == '0') {
  $log_query = $mysqli->prepare("(SELECT DISTINCT log0.q_id, 0 AS paper_type, grade, roles, screen, duration, log0.started AS order_started, user_answer, DATE_FORMAT(log0.started, '{$configObject->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, gender, ipaddress, username, users.id, student_id, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, log0.userID, mark, status, attempt FROM (log0, log_metadata, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log0.q_paper=log_metadata.paperID AND log0.userID=log_metadata.userID AND log0.started=log_metadata.started AND log0.q_id=questions.q_id AND q_paper=? AND users.id=log0.userID AND grade LIKE ? $roles_sql AND log0.started>=? AND log0.started<=?) UNION ALL (SELECT log1.q_id, 1 AS paper_type, grade, roles, screen, duration, log1.started AS order_started, user_answer, DATE_FORMAT(log1.started, '{$configObject->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, gender, ipaddress, username, users.id, student_id, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, log1.userID, mark, status, attempt FROM (log1, log_metadata, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log1.q_paper=log_metadata.paperID AND log1.userID=log_metadata.userID AND log1.started=log_metadata.started AND log1.q_id=questions.q_id AND q_paper=? AND users.id=log1.userID AND grade LIKE ? $roles_sql AND log1.started>=? AND log1.started<=?) ORDER BY userID, order_started, screen");
  $log_query->bind_param('isssisss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate'], $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
} elseif ($paper_type == '2') {
  $log_query = $mysqli->prepare("SELECT DISTINCT log2.q_id, 2 AS paper_type, grade, roles, screen, duration, log2.started, user_answer, DATE_FORMAT(log2.started, '{$configObject->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, gender, ipaddress, username, users.id, student_id, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, log2.userID, mark, status, attempt FROM (log2, log_metadata, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log2.q_paper=log_metadata.paperID AND log2.userID=log_metadata.userID AND log2.started=log_metadata.started AND log2.q_id=questions.q_id AND q_paper=? AND users.id=log2.userID AND grade LIKE ? $roles_sql AND DATE_ADD(log2.started, INTERVAL 2 MINUTE)>=? AND log2.started<=? ORDER BY userID, log2.started, screen");
  $log_query->bind_param('isss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
} elseif ($paper_type == '5') {
  $log_query =   $mysqli->prepare("SELECT DISTINCT log5.q_id, 5 AS paper_type, grade, roles, 1 AS screen, 0 AS duration, log5.started, NULL AS user_answer, DATE_FORMAT(log5.started, '{$configObject->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, gender, NULL AS ipaddress, username, users.id, student_id, NULL AS user_answer, q_type, log5.userID, mark, status, 1 AS attempt FROM (log5, log_metadata, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log5.q_paper=log_metadata.paperID AND log5.userID=log_metadata.userID AND log5.started=log_metadata.started AND log5.q_id=questions.q_id AND q_paper=? AND users.id=log5.userID AND grade LIKE ? $roles_sql AND DATE_ADD(log5.started, INTERVAL 2 MINUTE)>=? AND log5.started<=? ORDER BY userID, log5.started, screen");
  $log_query->bind_param('isss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
} else {
  $log_query = $mysqli->prepare("SELECT DISTINCT log$paper_type.q_id, $paper_type AS paper_type, grade, roles, screen, duration, log$paper_type.started, user_answer, DATE_FORMAT(log$paper_type.started, '{$configObject->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, gender, ipaddress, username, users.id, student_id, REPLACE(user_answer,'\"',\"'\") AS user_answer, q_type, log$paper_type.userID, mark, status, attempt FROM (log$paper_type, log_metadata, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log$paper_type.q_paper=log_metadata.paperID AND log$paper_type.userID=log_metadata.userID AND log$paper_type.started=log_metadata.started AND log$paper_type.q_id=questions.q_id AND q_paper=? AND users.id=log$paper_type.userID AND grade LIKE ? $roles_sql AND log$paper_type.started>=? AND log$paper_type.started<=? ORDER BY userID, started, screen");
  $log_query->bind_param('isss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
}
$log_query->execute();
$log_query->bind_result($q_id, $paper_type, $grade, $tmp_roles, $screen, $duration, $started, $user_answer, $display_started, $year, $title, $surname, $initials, $first_names, $gender, $ipaddress, $username, $tmp_userID, $student_id, $user_answer, $q_type, $tmp_userID, $mark, $status, $attempt);

$user_no    = 0;
$display_no = 0;
$tmp_mark   = 0;
$tmp_user_mark_array = array();
$user_duration    = 0;
$marking_complete = 1;
$old_screen   = 0;
$total_adjusted_mark = 0;
$tmp_array    = array();
$user_results = array();
$median       = array();
$sortby   = 'name';
$ordering = 'asc';
if (isset($_GET['percent'])) {
  $percent = $_GET['percent'];
} else {
  $percent = 100;
}
if (isset($_GET['direction'])) {
  $direction = $_GET['direction'];
} else {
  $direction = 100;
}
if (isset($_GET['absent'])) {
  $absent = $_GET['absent'];
} else {
  $absent = 0;
}
    
$warnings = array('deleted_qns' => array());
while ($log_query->fetch() !== null) {
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '' and $user_modules[$tmp_userID]['moduleid'] != $_GET['repmodule']) {
    continue; //this user is not on the module set in repmodule so dont put them in the array
  }
  if ($old_screen != $screen or $old_userID != $tmp_userID or $old_started != $started) {
    $user_duration += $old_duration;
  }
  if (($old_userID != $tmp_userID) or ($old_started != $started)) {
    if ($old_userID != '') {
      writeUserResults($tmp_array, $user_results, $user_no, $tmp_mark, $tmp_user_mark_array, $user_duration, $marking_complete, $metadata_array, $student_metadata_array);
      $tmp_array['questions'] = 0;
      $tmp_mark = 0;
      $tmp_user_mark_array = array();
      $user_duration = 0;
      $marking_complete = 1;
    }
    $tmp_array = array();
    $tmp_array['started'] = $started;
    $tmp_array['username'] = $username;
    $tmp_array['tmp_userID'] = $tmp_userID;
    if (isset($user_modules[$tmp_userID]['moduleid'])) {
      $tmp_array['module'] = $user_modules[$tmp_userID]['moduleid'];
    } else {
      $tmp_array['module'] = '';
    }
    $tmp_array['attempt'] = $attempt;
    $tmp_array['grade'] = $grade;
    $tmp_array['roles'] = $tmp_roles;
    $tmp_array['year'] = $year;
    $tmp_array['display_started'] = $display_started;
    $tmp_array['title'] = $title;
    $tmp_array['name'] = str_replace("'","",$surname) . ',' . $first_names;
    $tmp_array['surname'] = $surname;
    $tmp_array['initials'] = $initials;
    $tmp_array['first_names'] = $first_names;
    $tmp_array['student_id'] = $student_id;
    $tmp_array['gender'] = $gender;
    $tmp_array['ipaddress'] = $ipaddress;
    if (isset($ipaddresses[$ipaddress])) {
      $tmp_array['room'] = $ipaddresses[$ipaddress];
    } else {
      $tmp_array['room'] = '<span style="color:#808080">&lt;unknown&gt;</span>';
    }
    $tmp_array['xmean'] = 0;
    $tmp_array['xmean_adjusted'] = 0;
    $tmp_array['duration'] = $user_duration;
    $tmp_array['paper_type'] = $paper_type;
  }
  getUserMark($q_id, $user_answer, $mark, $excluded, $status, $tmp_mark, $tmp_user_mark_array, $random_q_ids, $tmp_userID);

  if ($q_type == 'textbox' and !is_numeric($mark)) $marking_complete = 0;
  if (isset($tmp_array['questions'])) {
    $tmp_array['questions']++;
  } else {
    $tmp_array['questions'] = 1;
  }
  $old_userID   = $tmp_userID;
  $old_started  = $started;
  $old_duration = $duration;
  $old_screen   = $screen;
  $old_status   = $status;
}


if (!empty($tmp_array)) {
  if (!isset($_GET['repmodule']) or $_GET['repmodule'] == '' or $user_modules[$tmp_userID]['moduleid'] == $_GET['repmodule']) {
    $user_duration += $old_duration;
    $log_query->free_result();
    $log_query->close();
    writeUserResults($tmp_array, $user_results, $user_no, $tmp_mark, $tmp_user_mark_array, $user_duration, $marking_complete, $metadata_array, $student_metadata_array);
  
    if ($percent < 100) {
      // Sort by mark order.
      $sortby = 'mark';
      $ordering = $direction;
      $user_results = array_csort($user_results, $sortby, $ordering);
      $cohort_size = round(($user_no/100)*$percent);
      // Set visible/invisible flag where necessary.
      for ($i=0; $i<$user_no; $i++) {
        if ($i >= $cohort_size) {
          $user_results[$i]['visible'] = 0;
          $out_of_range++;
        } else {
          $total_mark += $user_results[$i]['mark'];
          $total_adjusted_mark += ($user_results[$i]['mark'] - $total_random_mark);
        }
      }
    } else {
      if ($absent == 0) $cohort_size = $user_no;
      for ($i=0; $i<$user_no; $i++) {
        if (isset($user_results[$i]['mark'])) {
          $total_mark += $user_results[$i]['mark'];
          $total_adjusted_mark += ($user_results[$i]['mark'] - $total_random_mark);
        }
      }
    }
  
    if ($absent == 1) {
      // Add in students in cohort who haven't taken the exam.
      $count_student_cohort = count($student_cohort);
      for ($i=0; $i<$count_student_cohort; $i++) {
        if ($student_cohort[$i]['taken'] == 0) {
          $user_results[$user_no]['name'] = $student_cohort[$i]['name'];
          $user_results[$user_no]['mark'] = 0;
          $user_results[$user_no]['adj_percent'] = 0;
          $user_results[$user_no]['percent'] = 0;
          $user_results[$user_no]['started'] = '';
          $user_results[$user_no]['username'] = $student_cohort[$i]['username'];
          $user_results[$user_no]['tmp_userID'] = $student_cohort[$i]['tmp_userID'];
          $user_results[$user_no]['student_grade'] = $student_cohort[$i]['grade'];
          $user_results[$user_no]['module'] = $student_cohort[$i]['module'];
          $user_results[$user_no]['year'] = '';
          $user_results[$user_no]['display_started'] = '';
          $user_results[$user_no]['title'] = $student_cohort[$i]['title'];
          $user_results[$user_no]['surname'] = $student_cohort[$i]['surname'];
          $user_results[$user_no]['initials'] = $student_cohort[$i]['initials'];
          $user_results[$user_no]['first_names'] = $student_cohort[$i]['first_names'];
          $user_results[$user_no]['student_id'] = $student_cohort[$i]['student_id'];
          $user_results[$user_no]['gender'] = $student_cohort[$i]['gender'];
          $user_results[$user_no]['ipaddress'] = '';
          $user_results[$user_no]['duration'] = 0;
          $user_results[$user_no]['questions'] = '';
          $user_results[$user_no]['paper_type'] = '';
          $user_results[$user_no]['room'] = '';
          $user_results[$user_no]['visible'] = 1;    // Default to visible unless switched off below.
          $user_results[$user_no]['roles'] = 'Student';
          $user_no++;
        }
      }
    }
    
    /*
    // Remove additional users from array who do not match passed metadata settings.
    $meta_parts = explode('=',$_GET['meta1']);
    for ($i=0; $i<$user_no; $i++) {
      $type = $meta_parts[0];
      
      if (isset($user_results[$i]) and $user_results[$i]['metadata'][$type] != $meta_parts[1]) {
        $total_mark -= $user_results[$i]['mark'];
        $total_adjusted_mark -= ($user_results[$i]['mark']-$total_random_mark);
        unset($user_results[$i]);
      }
    }
    */
    
    // Sort the arrays
    sort($median);
    if (isset($_GET['sortby'])) {
      $sortby = $_GET['sortby'];
    } else {
      $sortby = 'name';
    }
    if (isset($_GET['ordering'])) {
      $ordering = $_GET['ordering'];
    } else {
      $ordering = 'asc';
    }
    if (isset($_GET['percent'])) {
      $percent = $_GET['percent'];
    } else {
      $percent = 100;
    }
    if (isset($_GET['direction'])) {
      $direction = $_GET['direction'];
    } else {
      $direction = 'asc';
    }
    if (isset($_GET['absent'])) {
      $absent = $_GET['absent'];
    } else {
      $absent = 0;
    }
    
    $tmp_sort = ($sortby != 'result' and $sortby != 'percent') ? $sortby : 'adj_percent';
    if ($tmp_sort == 'adj_percent') $tmp_sort = 'mark';
    $user_results = array_csort($user_results, $tmp_sort, $ordering);
  }
}

if (substr($marking,0,1) == '2') {
  get_ss_pass($ss_pass, $ss_hon);

  for ($i=0; $i<$user_no; $i++) {
    if (isset($user_results[$i]['adj_percent'])) {
      $user_results[$i]['adj_percent'] = crankMark($user_results[$i]['percent'], $ss_pass, $ss_hon, $pass_mark, $distinction_mark);
    }
  }
}

function isEven($intNumber) {
  if ($intNumber % 2 == 0 ) {
    return 'y';
  } else {
    return 'n';
  }
}

// Generate summary statistics.
$failures       = 0;
$passes         = 0;
$honours        = 0;
$mark_total     = 0;
$percent_total  = 0;
$max_mark       = 0;
$min_mark       = 9999;
$max_percent    = 0;    // adjusted percent.
$min_percent    = 100;  // adjusted percent.
$median_mark_array    = array();
$median_percent_array = array();
$marks_data           = array();

for ($i=0; $i<$user_no; $i++) {
  if (isset($user_results[$i]['adj_percent']) and $user_results[$i]['questions'] >= $question_no and $user_results[$i]['visible'] == 1) {
    $completed_no++;
    $median_mark_array[] = $user_results[$i]['mark'];
    $median_percent_array[] = $user_results[$i]['adj_percent'];

    $mark_total += $user_results[$i]['mark'];
    $percent_total += $user_results[$i]['adj_percent'];
  }
  if (isset($user_results[$i]['mark']) and $user_results[$i]['visible'] == 1) {
    if (isset($user_results[$i]['adj_percent'])) {
      $marks_data[] = $user_results[$i]['adj_percent'];
      if ($user_results[$i]['adj_percent'] < $pass_mark) $failures++;
      if ($user_results[$i]['adj_percent'] < $min_percent) $min_percent = $user_results[$i]['adj_percent'];
      if ($user_results[$i]['adj_percent'] > $max_percent) $max_percent = $user_results[$i]['adj_percent'];
    }
    if ($user_results[$i]['adj_percent'] >= $pass_mark and $user_results[$i]['adj_percent'] < $distinction_mark) $passes++;
    if ($user_results[$i]['adj_percent'] >= $distinction_mark) $honours++;
    if ($user_results[$i]['mark'] < $min_mark) $min_mark = $user_results[$i]['mark'];
    if ($user_results[$i]['mark'] > $max_mark) $max_mark = $user_results[$i]['mark'];
  }
}

// Calculate StdDev.
$xmean_total = 0;
$xmean_percent_total = 0;
for ($i=0; $i<$user_no; $i++) {
  if (isset($user_results[$i]['questions']) and $user_results[$i]['questions'] >= $question_no and $user_results[$i]['visible'] == 1 and $completed_no > 0) {
    $xmean_total += (($user_results[$i]['mark'] - ($mark_total / $completed_no)) * ($user_results[$i]['mark'] - ($mark_total / $completed_no)));
    $xmean_percent_total += (($user_results[$i]['adj_percent'] - ($percent_total / $completed_no)) * ($user_results[$i]['adj_percent'] - ($percent_total / $completed_no)));
  }
}
if ($completed_no > 1) {
  $stddev_mark = sqrt($xmean_total/($completed_no-1));
  $stddev_percent = sqrt($xmean_percent_total / ($completed_no - 1));
} else {
  $stddev_mark    = 0;
  $stddev_percent = 0;
}

if ($completed_no > 0) {
  $mean_mark = round($mark_total / $completed_no,1);
  $mean_percent = round($percent_total / $completed_no,1);
  sort($median_mark_array);
  sort($median_percent_array);
  if (isEven($completed_no) == 'y') {
    $median_mark = ($median_mark_array[($completed_no/2)-1] + $median_mark_array[($completed_no/2)-1]) / 2;
    $median_percent = ($median_percent_array[($completed_no/2)-1] + $median_percent_array[($completed_no/2)-1]) / 2;
  } else {
    $median_mark = $median_mark_array[($completed_no/2)-1];
    $median_percent = $median_percent_array[($completed_no/2)-1];
  }
} else {
  $mean_mark      = 0;
  $mean_percent   = 0;
  $median_mark    = 0;
  $median_percent = 0;
}

$top_10     = mypercentile($marks_data, 0.1);
$top_15     = mypercentile($marks_data, 0.15);
$top_20     = mypercentile($marks_data, 0.2);
$top_25     = mypercentile($marks_data, 0.25);
$bottom_10  = mypercentile($marks_data, 0.90);
?>