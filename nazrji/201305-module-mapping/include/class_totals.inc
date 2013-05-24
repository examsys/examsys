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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../include/calculate_marks.inc';
require_once '../include/demo_replace.inc';
require_once '../include/sort.inc';
require_once '../classes/paperproperties.class.php';
require_once '../classes/exclusion.class.php';
require_once '../classes/mathsutils.class.php';
require_once '../classes/results_cache.class.php';

function compile_report($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $paperID, $startdate, $enddate, $mysqli) {
  global $stats, $question_no, $log_late, $exclusions, $cohort_size, $random_q_ids, $paper_buffer, $student_cohort;
  
  $configObject = Config::get_instance();
  
  $demo = is_demo($userObject);
  $q_medians = array();
  $log_late = array();
  $log_late_missing_users = array();

  $moduleID = Paper_utils::get_modules($paperID, $mysqli);
  $moduleID_in = implode(',', array_keys($moduleID));

  $results_cache = new ResultsCache($mysqli);
  $exclusions    = new Exclusion($paperID, $mysqli);

  $marking       = $propertyObj->get_marking();
  $pass_mark     = $propertyObj->get_pass_mark();
  $paper_type    = $propertyObj->get_paper_type();
  $calendar_year = $propertyObj->get_calendar_year();

  $exclusions->load();                                                                                // Get any questions to exclude.

  $paper_buffer = load_answers($exclusions, $question_no, $mysqli);

  get_log_late($startdate, $enddate, $paperID, $configObject, $log_late, $log_late_missing_users, $mysqli);

  $student_cohort = get_absent($moduleID_in, $paperID, $calendar_year, $demo, $mysqli);

  $user_modules = get_users($moduleID_in, $paperID, $calendar_year, $mysqli);                         // Get all the users on the module(s) the paper is on. 

  $metadata_array = get_metadata($moduleID_in, $calendar_year, $mysqli);                              // Query for metadata

  $user_results = load_results($studentsonly, $propertyObj, $demo, $configObject, $metadata_array, $log_late_missing_users, $exclusions, $random_q_ids, $user_modules, $paper_buffer, $q_medians, $mysqli);  // Load the student results

  $late_cohort = load_late_cohort($log_late_missing_users, $demo, $mysqli);
  
  adjust_marks($paper_buffer, $marking, $exclusions, $ss_pass, $ss_hon, $propertyObj, $user_results, $mysqli);

  add_rank($user_results);                                                                            // Add in rank data.

  convert_moduleIDs($user_results, $mysqli);                                                          // Convert Module IDs into codes

  $cohort_size  = flag_subpart($user_results, $percent, $ordering, $absent);

  add_late_students($user_results, $late_cohort, $paper_type);                                        // Add in late users into main dataset

  add_absent_students($absent, $user_results, $student_cohort);                                       // Add any absent students into main dataset

  $stats = get_stats($user_results, $question_no, $propertyObj);                                      // Generate the main statistics

  writeStudentDeciles($user_results, $stats);                                                         // Add in deciles per student

  sort_results($percent, $ordering, $sortby, $user_results);                                          // Sort the whole array by the right column

  if ($results_cache->should_cache($propertyObj, $percent, $absent, $percent, $absent, $paperID)) {
    $results_cache->save_paper_cache($propertyObj, $percent, $absent, $stats, $paperID);                // Cache general paper stats
    
    $results_cache->save_student_mark_cache($propertyObj, $percent, $absent, $user_results, $paperID);  // Cache student/paper marks
    
    $results_cache->save_median_question_marks($propertyObj, $percent, $absent, $q_medians, $paperID);  // Cache the question/paper medians
  }
  
  return $user_results;
}

function adjust_marks($paper_buffer, $marking, $exclusions, &$ss_pass, &$ss_hon, $propertyObj, &$user_results, $db) {
  $user_no = count($user_results);

  $pass_mark        = $propertyObj->get_pass_mark();
  $distinction_mark = $propertyObj->get_distinction_mark();
  $paperID          = $propertyObj->get_property_id();

  if ($marking == '1') {                              // Monkey mark
    for ($i=0; $i<$user_no; $i++) {
      if (isset($user_results[$i]['adj_percent'])) {
        $user_results[$i]['adj_percent'] = (($user_results[$i]['mark'] - $paper_buffer['total_random_mark']) / ($paper_buffer['total_marks'] - $paper_buffer['total_random_mark'])) * 100;
      }
    }
  } elseif ($marking{0} == '2') {                     // Standards Setting
    get_ss_pass($marking, $paperID, $exclusions, $ss_pass, $ss_hon, $paper_buffer, $user_results, $db);

    for ($i=0; $i<$user_no; $i++) {
      if (isset($user_results[$i]['adj_percent'])) {
        $user_results[$i]['adj_percent'] = crankMark($user_results[$i]['percent'], $ss_pass, $ss_hon, $pass_mark, $distinction_mark);
      }
    }
  }  
}

function nicedate($original) {
  return substr($original, 6, 2) . '/' . substr($original, 4, 2) . '/' . substr($original, 0, 4) . ' ' . substr($original, 8, 2) . ':' . substr($original, 10, 2);
}

function getRandomDetails($questionID, &$random_q_ids, $paper_buffer, $mysqli) {
  $configObject = Config::get_instance();

  $random_q_ids[] = $questionID;

  $question_no        = 0;
  $random_questions   = array();
  $old_q_id           = '';
  $old_score_method   = '';
  $old_q_media_width  = '';
  $old_q_media_height = '';
  $old_correct        = array();
  $old_option_text    = array();
  $old_theme        = '';
  $old_scenario     = '';
  $old_leadin       = '';
  $old_media        = '';
  $old_type         = '';
  $old_score_method = '';

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
        $random_questions[$old_q_id]['theme']               = $old_theme;
        $random_questions[$old_q_id]['q_id']                = $old_q_id;
        $random_questions[$old_q_id]['q_type']              = $old_q_type;
        $random_questions[$old_q_id]['leadin']              = $old_leadin;
        $random_questions[$old_q_id]['scenario']            = $old_scenario;
        $random_questions[$old_q_id]['correct']             = $old_correct;
        $random_questions[$old_q_id]['display_method']      = $old_display_method;
        $random_questions[$old_q_id]['score_method']        = $old_score_method;
        $random_questions[$old_q_id]['status']              = $old_status;
        $random_questions[$old_q_id]['display_last_edited'] = $display_last_edited;
        $random_questions[$old_q_id]['marks_correct']       = $old_marks_correct;
        $random_questions[$old_q_id]['marks_incorrect']     = $old_marks_incorrect;
        $random_questions[$old_q_id]['random_mark']         = qRandomMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
        $random_questions[$old_q_id]['option_text']         = $old_option_text;
        $old_correct     = array();
        $old_option_text = array();
        $question_no++;
      }
      $old_theme            = $theme;
      $old_q_id             = $q_id;
      $old_q_type           = $q_type;
      $old_leadin           = $leadin;
      $old_scenario         = $scenario;
      $old_status           = $status;
      $old_marks_correct    = $marks_correct;
      $old_marks_incorrect  = $marks_incorrect;
      $old_correct[]        = $correct;
      $old_option_text[]    = $option_text;
      $old_display_method   = $display_method;
      $old_score_method     = $score_method;
      $old_q_media_width    = $q_media_width;
      $old_q_media_height   = $q_media_height;
    }

    // Write out the last question.
    $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
    if (strlen($old_leadin) > 160) $old_leadin = substr($old_leadin,0,160) . '...';
    $random_questions[$old_q_id]['theme']               = $old_theme;
    $random_questions[$old_q_id]['q_id']                = $old_q_id;
    $random_questions[$old_q_id]['q_type']              = $old_q_type;
    $random_questions[$old_q_id]['leadin']              = $old_leadin;
    $random_questions[$old_q_id]['scenario']            = $old_scenario;
    $random_questions[$old_q_id]['correct']             = $old_correct;
    $random_questions[$old_q_id]['display_method']      = $old_display_method;
    $random_questions[$old_q_id]['score_method']        = $old_score_method;
    $random_questions[$old_q_id]['status']              = $old_status;
    $random_questions[$old_q_id]['display_last_edited'] = $display_last_edited;
    $random_questions[$old_q_id]['marks_correct']       = $old_marks_correct;
    $random_questions[$old_q_id]['marks_incorrect']     = $old_marks_incorrect;
    $random_questions[$old_q_id]['random_mark']         = qRandomMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
    $random_questions[$old_q_id]['option_text']         = $old_option_text;
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

function checkDisplayExcluded(&$html, $exclude, $q_no) {
  $subpart = '';
  
  if (strlen($exclude) > 1) {
    for ($i=0; $i<strlen($exclude); $i++) {
      if (substr($exclude,$i,1) == '1') {
        if ($subpart == '') {
          $subpart = chr($i+97);
        } else {
          $subpart .= ', Q' . $q_no . chr($i+97);
        }
      }
    }
  }
  if (strpos($exclude, '1') !== false) {
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

function get_ss_pass($marking, $paperID, $exclusions, &$pass_score, &$distinction_score, $paper_buffer, $user_results, $db) {
  $mark_parts = explode(',', $marking);
  
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
  $result = $db->prepare("SELECT questionID, rating, method, status, q_type, score_method FROM standards_setting, questions WHERE standards_setting.questionID = questions.q_id AND paperID = ? AND setterID = ? AND std_set = ?");
  $result->bind_param('iis', $paperID, $mark_parts[1], $mark_parts[2]);
  $result->execute();
  $result->bind_result($questionID, $rating, $method, $status, $q_type, $score_method);
  while ($result->fetch()) {
    if ($status != 'Experimental') {
      $individual_ratings = explode(',', $rating);
      $count_individual_ratings = count($individual_ratings);
      for ($i=0; $i<$count_individual_ratings; $i++) {
        $excluded = $exclusions->get_exclusions_by_qid($questionID);

        $bad_bonus_mark = ($q_type == 'rank' and $score_method == 'Bonus Mark' and strpos($excluded, '1') !== false);
        if ((substr($excluded,$i,1) == 0) and !$bad_bonus_mark) {
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
    $result = $db->prepare("SELECT category, percentage FROM ebel WHERE setterID = ? AND date_set = ?");
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
    $pass_score = ($cut_marks / ($paper_buffer['total_marks']  * 100)) * 100;
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
      $distinction_score = (($cut_marks2 / ($paper_buffer['total_marks'] * 100)) * 100);
      $distinction_score = round($distinction_score,1);
    }
  } else {
    // Modified Angoff method.
    if ($question_no > 0) {
      $pass_score = round($std_total / $question_no, 1);
    } else {
      $pass_score = 0;
    }
  }

  if ($distinction_score == 0) {
    $distinction_score = get_ss_hon($user_results);
  }
}

function get_ss_hon($user_results) {
  $user_no = count($user_results);

  $marks_data = array();
  for ($i=0; $i<$user_no; $i++) {
    if (isset($user_results[$i]['percent'])) $marks_data[] = $user_results[$i]['percent'];
  }

  return MathsUtils::percentile($marks_data, 0.2);
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

function writeUserResults($tmp, &$master_array, &$user_number, $tmp_user_mark, $tmp_user_mark_array, $tmp_user_duration, $marking_comp, $met_array, $paper_buffer, $demo) {
  global $student_cohort;
  
  $master_array[$user_number]['name'] = $tmp['name'];
  $master_array[$user_number]['mark'] = round($tmp_user_mark,1);
  $master_array[$user_number]['mark_array'] = $tmp_user_mark_array;
  if ($paper_buffer['total_marks'] == 0) {
    $master_array[$user_number]['percent'] = 0;
    $master_array[$user_number]['adj_percent'] = 0;
  } else {
    $master_array[$user_number]['percent'] = ($tmp_user_mark / $paper_buffer['total_marks']) * 100;
    $master_array[$user_number]['adj_percent'] = ($tmp_user_mark / $paper_buffer['total_marks']) * 100;
  }

  $master_array[$user_number]['started']          = $tmp['started'];
  $master_array[$user_number]['username']         = $tmp['username'];
  $master_array[$user_number]['tmp_userID']       = $tmp['tmp_userID'];
  $master_array[$user_number]['student_grade']    = $tmp['grade'];
  $master_array[$user_number]['roles']            = $tmp['roles'];
  $master_array[$user_number]['module']           = $tmp['module'];
  $master_array[$user_number]['attempt']          = $tmp['attempt'];
  $master_array[$user_number]['year']             = $tmp['year'];
  $master_array[$user_number]['display_started']  = $tmp['display_started'];
  $master_array[$user_number]['title']            = $tmp['title'];
  $master_array[$user_number]['surname']          = demo_replace($tmp['surname'], $demo);
  $master_array[$user_number]['initials']         = demo_replace($tmp['initials'], $demo);
  $master_array[$user_number]['first_names']      = demo_replace($tmp['first_names'], $demo);
  $master_array[$user_number]['email']            = demo_replace($tmp['email'], $demo);
  $master_array[$user_number]['student_id']       = demo_replace_number($tmp['student_id'], $demo);
  $master_array[$user_number]['gender']           = $tmp['gender'];
  $master_array[$user_number]['ipaddress']        = $tmp['ipaddress'];
  $master_array[$user_number]['duration']         = $tmp_user_duration;
  $master_array[$user_number]['questions']        = $tmp['questions'];
  $master_array[$user_number]['paper_type']       = $tmp['paper_type'];
  $master_array[$user_number]['room']             = $tmp['room'];
  $master_array[$user_number]['visible']          = 1;    // Default to visible unless switched off below.
  $master_array[$user_number]['decile']           = 10;

  // Add metadata
  if (!empty($met_array['types'])) {
    foreach ($met_array['types'] as $type) {
      if (isset($smet_array['students'][$tmp['tmp_userID']][$type]) ) {
        $master_array[$user_number]['meta_' . $type] = $met_array['students'][$tmp['tmp_userID']][$type];
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
  
  if (isset($student_cohort)) {
    $student_cohort = check_and_clear_cohort($student_cohort, $tmp['username']);
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
  $blank_details = explode("[blank", $option_text);
  $no_answers = count($blank_details) - 1;
  for ($i=1; $i<=$no_answers; $i++) {
    $blank_details[$i] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$blank_details[$i]);
    $blank_details[$i] = preg_replace("| size=\"([0-9]{1,3})\"|","",$blank_details[$i]);
    $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
    $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
    $answer_list = explode(',', $blank_details[$i]);
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

function getUserMark($q_id, $tmp_user_answer, $tmp_user_mark, $exclusions, $status, &$tmp_mark, &$tmp_user_mark_array, $random_q_ids, $tmp_userID, &$q_medians) {
  global $paper_buffer, $user_results, $tmp_array;

  $tmp_exclude = $exclusions->get_exclusions_by_qid($q_id);
  
  $multi_part_qns = array('extmatch'=>1, 'matrix'=>1, 'blank'=>1, 'dichotomous'=>1, 'labelling'=>1, 'hotspot'=>1);
  
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
      $count_user_awnser = count($user_answers)-1;
      for ($a=0; $a<$count_user_awnser; $a++) {
        if ($tmp_exclude{$a} == '0') {
          if ($question['display_method'] == 'dropdown') {
            $student_answer = html_entity_decode(trim(str_replace('&nbsp;', ' ', $question['correct'][$a][0])));
            $correct_answer = html_entity_decode(trim(str_replace('&nbsp;', ' ', $user_answers[$a+1])));

            if ($student_answer == $correct_answer) {
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
          $x = $tmp_second_split[$label_no-2];
          $y = $tmp_second_split[$label_no-1] - 25;
          $correct_labels[$x . 'x' . $y] = substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'));
          if ($tmp_exclude{$i} == '0') {
            $placeholders++;
          } else {
            $excluded_no++;
          }
          $i++;
        }
      }

      // Strip out width and height for graphical labels.
      $i = 0;
      foreach ($correct_labels as $key=>$value) {
        $tmp_parts = explode('~', $value);
        $correct_labels[$key] = $tmp_parts[0];
        $correct_labels_pos[$key] = $i++;
      }

      if ($tmp_user_answer != '') {
        $user_split1 = explode(';', $tmp_user_answer);
        $user_split2 = explode('$', $user_split1[1]);
        
        $i = 0;
        $correct = 0;
        $count_user_split2 = count($user_split2)-3;
        for ($a=0; $a<$count_user_split2; $a+=4) {
          $x = $user_split2[$a];
          $y = $user_split2[$a+1];
          $index = 0;
          if (isset($correct_labels[$x . 'x' . $y])) $index = $correct_labels_pos[$x . 'x' . $y];
          if ($tmp_exclude{$index} == '0') {
            if (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] == $user_split2[$a+2]) {
              $tmp_mark += $question['marks_correct'];
              $correct += $question['marks_correct'];
              $tmp_user_mark_array[$q_id][$index] = $question['marks_correct'];
            } elseif (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] != $user_split2[$a+2]) {
              $tmp_mark += $question['marks_incorrect'];
              $correct += $question['marks_incorrect'];
              $tmp_user_mark_array[$q_id][$index] = $question['marks_incorrect'];
            } else {
              if (isset($index) && $index!='') $tmp_user_mark_array[$q_id][$index] = 0;
            }
            $i++;
          }
        }
      }
      
      if (isset($tmp_user_mark_array[$q_id])) ksort($tmp_user_mark_array[$q_id]);
      //create shortened array
      $i = 0;
      $correct_labels_exc = array();
      foreach ($correct_labels as $cli => $clv) {
        if ($tmp_exclude{$i++} == '0') $correct_labels_exc[$cli] = $clv;
      }
      // If the user has not answered the question pad out with zero marks.
      if (isset($tmp_user_mark_array[$q_id])) {
        $user_answer_no = count($tmp_user_mark_array[$q_id]);
      } else {
        $user_answer_no = 0;
      }
      if ($user_answer_no < count($correct_labels_exc)) {
        for ($i=$user_answer_no; $i<count($correct_labels_exc); $i++) {
          $tmp_user_mark_array[$q_id][] = 0;
        }
      }
      
      $question['correct_labels'] = $correct_labels_exc;
    }
    
    $q_medians[$q_id][] = $tmp_user_mark;
  } else {
    // Marking per Question, or all other question types, simply return the original mark.
    if ($tmp_exclude{0} == '0') {
      $round_tmp_user_mark = round($tmp_user_mark, 2);
      $tmp_mark += $round_tmp_user_mark;
      $tmp_user_mark_array[$q_id] = $round_tmp_user_mark;
    }
    
    $q_medians[$q_id][] = $tmp_user_mark;
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

/**
 * Check if a record exists in the student cohort array created when showing absent candidates. If found, remove the entry
 * @param  array  $student_cohort All (remaining) students in the cohort
 * @param  string $username       Username to look for
 * @return array                  Cohort array with current user removed if found
 */
function check_and_clear_cohort($student_cohort, $username) {
  $tmp_cohort_size = count($student_cohort);
  for ($i=0; $i < $tmp_cohort_size; $i++) {
    if ($student_cohort[$i]['username'] == $username) {
      unset($student_cohort[$i]);
      break;
    }
  }
  return array_values($student_cohort);
}

function load_answers($exclusions, &$question_no, $db) {
  $question_no      = 0;
  $old_q_id         = 0;
  $stems            = 0;
  $old_correct      = array();
  $old_option_text  = array();
  $random_q_ids     = array();
  
  $paper_buffer['total_marks'] = 0;
  $paper_buffer['orig_total_marks'] = 0;
  $paper_buffer['total_random_mark'] = 0;
  $paper_buffer['display_excluded'] = '';
  $paper_buffer['display_experimental'] = '';
  
  // Load the correct answers into 'paper_buffer' array.
  $result = $db->prepare("SELECT q_id, marks_correct, marks_incorrect, display_method, score_method, q_media_height, q_media_width, q_type, correct, score_method, option_text, status, display_pos FROM (papers, questions, options) WHERE papers.question = questions.q_id AND papers.paper = ? AND questions.q_id = options.o_id AND q_type != 'info' ORDER BY screen, display_pos, id_num");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $marks_correct, $marks_incorrect, $display_method, $score_method, $q_media_height, $q_media_width, $q_type, $correct, $score_method, $option_text, $status, $display_pos);
  while ($result->fetch()) {
    if ($q_id != $old_q_id or $old_display_pos != $display_pos) {
      if ($old_q_id != 0) {
        if ($old_status != 'Experimental') {
          $tmp_exclude = $exclusions->get_exclusions_by_qid($old_q_id);
          if ($old_q_type == 'random') {
            $tmp_id = getRandomDetails($old_q_id, $random_q_ids, $paper_buffer, $db);

            $last_random = $paper_buffer[$old_q_id]['random_questions'][$tmp_id];

            $old_q_type = $last_random['q_type'];
            $old_marks_correct = $last_random['marks_correct'];
            $old_option_text = $last_random['option_text'];
            $old_correct = $last_random['correct'];
            $old_display_method = $last_random['display_method'];
            $old_score_method = $last_random['score_method'];
            $paper_buffer['total_marks'] += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
            $paper_buffer['orig_total_marks'] += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
          } else {
            $paper_buffer['total_marks'] += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
            $paper_buffer['orig_total_marks'] += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
            $paper_buffer['total_random_mark'] += qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
            checkDisplayExcluded($paper_buffer['display_excluded'], $tmp_exclude, $question_no);
          }
        } else {
          displayExperimental($paper_buffer['display_experimental'], $question_no);
        }
        $stems = 0;
        $old_marks_correct = 0;
      }
      $question_no++;
      $option_no        = 0;
      $old_correct      = array();
      $old_option_text  = array();
      $question_id      = $q_id;
      $paper_buffer[$question_id]['q_type']           = $q_type;
      $paper_buffer[$question_id]['score_method']     = $score_method;
      $paper_buffer[$question_id]['display_method']   = $display_method;
      $paper_buffer[$question_id]['marks_correct']    = $marks_correct;
      $paper_buffer[$question_id]['marks_incorrect']  = $marks_incorrect;

      if ($q_type == 'blank') {
        $paper_buffer[$question_id]['correct'] = extract_blank_correct($option_text, $score_method, $paper_buffer, $question_id);
      } else {
        $paper_buffer[$question_id]['correct'][$option_no] = $correct;
      }
    } else {
      $paper_buffer[$question_id]['correct'][$option_no] = $correct;
    }
    $option_no++;
    $stems++;

    $old_q_id             = $q_id;
    $old_display_pos      = $display_pos;
    $old_q_type           = $q_type;
    $old_display_method   = $display_method;
    $old_score_method     = $score_method;
    $old_correct[]        = $correct;
    $old_q_media_width    = $q_media_width;
    $old_q_media_height   = $q_media_height;
    $old_option_text[]    = $option_text;
    $old_marks_correct    = $marks_correct;
    $old_marks_incorrect  = $marks_incorrect;
    $old_status           = $status;
  }
  $result->close();

  if ($old_status != 'Experimental') {
    $tmp_exclude = $exclusions->get_exclusions_by_qid($old_q_id);

    if ($old_q_type == 'random') {
      $tmp_id = getRandomDetails($old_q_id, $random_q_ids, $paper_buffer, $db);

      $last_random = $paper_buffer[$old_q_id]['random_questions'][$tmp_id];

      $old_q_type = $last_random['q_type'];
      $old_marks_correct = $last_random['marks_correct'];
      $old_option_text = $last_random['option_text'];
      $old_correct = $last_random['correct'];
      $old_display_method = $last_random['display_method'];
      $old_score_method = $last_random['score_method'];
      $paper_buffer['total_marks'] += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
      $paper_buffer['orig_total_marks'] += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
    } else {
      $paper_buffer['total_marks'] += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
      $paper_buffer['orig_total_marks'] += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
      $paper_buffer['total_random_mark'] += qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
      checkDisplayExcluded($paper_buffer['display_excluded'], $tmp_exclude, $question_no);
    }
  } else {
    displayExperimental($paper_buffer['display_experimental'], $question_no);
  }
  
  return $paper_buffer;
}

function get_log_late($startdate, $enddate, $paperID, $configObj, &$log_late, &$log_late_missing_users, $db) {
  // Check log_late for any records
  $late_ts = strtotime($enddate) + 7200;
  $late_end = date('Y-m-d H:i:s', $late_ts);
  $cfg_long_date_time = $configObj->get('cfg_long_date_time');
  $result = $db->prepare("SELECT DISTINCT userID, title, surname, first_names, DATE_FORMAT(started, '$cfg_long_date_time') AS display_started, started FROM log_late, log_metadata, users WHERE log_late.metadataID = log_metadata.id AND log_metadata.userID = users.id AND paperID = ? AND started >= ? AND started <= ? ORDER BY surname, initials");
  $result->bind_param('iss', $paperID, $startdate, $late_end);
  $result->execute();
  $result->bind_result($tmp_userID, $title, $surname, $first_names, $display_started, $started);
  while ($result->fetch()) {
    $log_late[$tmp_userID] = $title . ' ' .  $surname . ', ' . $first_names;
    $log_late_missing_users[$tmp_userID]['display_started'] = $display_started;
    $log_late_missing_users[$tmp_userID]['started'] = $started;
  }
  $result->close();
}

function get_absent($moduleID_in, $paperID, $calendar_year, $demo, $db) {
  $student_cohort = array();
  $cohort_size = 0;

  if ($_GET['absent'] == 1) {
    // Get students in the cohort.

    $sql = "SELECT DISTINCT
                           users.id
                         , users.username
                         , title
                         , surname
                         , first_names
                         , initials
                         , student_id
                         , grade
                         , moduleid
                         , gender
            FROM
                  (modules_student, modules, users, sid)
            WHERE
                  modules_student.userID = sid.userID
            AND
                  modules_student.userID = users.id
            AND
                  modules_student.idMod = modules.id
            AND
                  users.roles
            LIKE
                  '%Student%'
            AND
                  idMod
            IN
                  ($moduleID_in)
            AND
                 calendar_year = ?";

    $result = $db->prepare( $sql );
    $result->bind_param('s', $calendar_year);
    $result->execute();
    $result->bind_result($tmp_userID, $tmp_username, $title, $surname, $first_names, $initials, $student_id, $grade, $moduleid, $gender);
    while ($result->fetch()) {
      $student_cohort[$cohort_size]['username']      = $tmp_username;
      $student_cohort[$cohort_size]['tmp_userID']    = $tmp_userID;
      $student_cohort[$cohort_size]['name']          = trim(str_replace("'","",$surname) . ',' . $first_names);
      $student_cohort[$cohort_size]['title']         = $title;
      $student_cohort[$cohort_size]['surname']       = demo_replace($surname, $demo);
      $student_cohort[$cohort_size]['first_names']   = demo_replace($first_names, $demo);
      $student_cohort[$cohort_size]['initials']      = demo_replace($initials, $demo);
      $student_cohort[$cohort_size]['student_id']    = demo_replace_number($student_id, $demo);
      $student_cohort[$cohort_size]['grade']         = $grade;
      $student_cohort[$cohort_size]['module']        = $moduleid;
      $student_cohort[$cohort_size]['gender']        = $gender;
      $student_cohort[$cohort_size]['timer_started'] = false;

      $cohort_size++;
    }
    $result->close();
  }
  
  return $student_cohort;
}

function get_users($moduleID_in, $paperID, $calendar_year, $db) {     // Get all the users on the module(s) the paper is on.
  $user_modules = array();
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $tmp_moduleID_in = $_GET['repmodule'];
  } else {
    $tmp_moduleID_in = $moduleID_in;
  }

  if ($calendar_year == '') {
    $mod_query = $db->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in)");
  } else {
    $mod_query = $db->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in) AND calendar_year = ?");
    $mod_query->bind_param('s', $calendar_year);
  }
  $mod_query->execute();
  $mod_query->bind_result($idMod, $tmp_userID, $tmp_moduleid);
  $mod_query->store_result();
  while ($mod_query->fetch()) {
    $user_modules[$tmp_userID]['idMod'] = $idMod;
  }
  $mod_query->close();
  
  return $user_modules;
}

function get_metadata($moduleID_in, $calendar_year, $db) {
  $metadata_array = array();
  $student_metadata_array = array();
  
  if ($calendar_year == '') {
    $stmt = $db->prepare("SELECT userID, type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN ($moduleID_in)");
  } else {
    $stmt = $db->prepare("SELECT userID, type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN ($moduleID_in) AND calendar_year = ?");
    $stmt->bind_param('s', $calendar_year);
  }
  $stmt->execute();
  $stmt->bind_result($student_userID, $type, $value);
  while ($stmt->fetch()) {
    $metadata_array['students'][$student_userID][$type] = $value;
    $metadata_array['types'][$type] = $type;
  }
  $stmt->close();
  
  return $metadata_array;
}

function load_results($studentsonly, $propertyObj, $demo, $configObj, $metadata_array, &$log_late_missing_users, $exclusions, $random_q_ids, $user_modules, $paper_buffer, &$q_medians, $db) {
  //if (isset($_GET['studentsonly']) and $_GET['studentsonly'] == 0) {
  if ($studentsonly == 0) {
    $roles_sql = '';
  } else {
    $roles_sql = " AND (users.roles='Student' OR users.roles='graduate')";
  }
  
  $user_no              = 0;
  $tmp_mark             = 0;
  $user_duration        = 0;
  $marking_complete     = 1;
  $old_screen           = 0;
  $old_duration         = 0;
  $old_userID           = '';
  $old_started          = '';
  $tmp_user_mark_array  = array();
  $tmp_array            = array();
  $user_results         = array();

  // Get log data.
  $paper_type = $propertyObj->get_paper_type();
  if ($paper_type == '0') {
    $sql = '(SELECT log0.q_id
                      , 0 AS paper_type
                      , grade
                      , roles
                      , screen
                      , duration
                      , started AS order_started
                      , user_answer
                      , DATE_FORMAT(started, "' . $configObj->get('cfg_long_date_time') . '") AS display_started
                      , year
                      , title
                      , surname
                      , initials
                      , first_names
                      , email
                      , gender
                      , ipaddress
                      , lab_name
                      , username
                      , users.id
                      , student_id
                      , user_answer
                      , q_type
                      , log_metadata.userID
                      , mark
                      , status
                      , attempt
                    FROM
                        log0, log_metadata, questions, users
                    LEFT JOIN
                        sid
                    ON
                        users.id = sid.userID
                    WHERE
                        log_metadata.userID = users.id
                    AND
                        log0.metadataID = log_metadata.id
                    AND
                        log0.q_id    = questions.q_id
                    AND
                        paperID = ?
                    AND
                        grade LIKE ?
                    ' . $roles_sql . '
                    AND
                        started >= ?
                    AND
                        started <= ? )
              UNION ALL
                  ( SELECT
                        log1.q_id
                      , 1 AS paper_type
                      , grade
                      , roles
                      , screen
                      , duration
                      , started AS order_started
                      , user_answer
                      , DATE_FORMAT(started, "' . $configObj->get('cfg_long_date_time') . '" ) AS display_started
                      , log_metadata.year
                      , title
                      , surname
                      , initials
                      , first_names
                      , email
                      , gender
                      , ipaddress
                      , lab_name
                      , username
                      , users.id
                      , student_id
                      , user_answer
                      , q_type
                      , log_metadata.userID
                      , mark
                      , status
                      , attempt
                    FROM
                      (log1, log_metadata, questions, users)
                    LEFT JOIN
                          sid
                    ON
                          users.id = sid.userID
                    WHERE
                          log_metadata.userID = users.id
                    AND
                          log1.metadataID = log_metadata.id
                    AND
                          log1.q_id    = questions.q_id
                    AND
                          paperID = ?
                    AND
                          grade LIKE ?
                    '. $roles_sql . '
                    AND
                        started >= ?
                    AND
                        started <= ?
                      )
                    ORDER BY
                        userID
                      , order_started
                      , screen';

    $log_query = $db->prepare($sql);
    $log_query->bind_param('isssisss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate'], $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
  } elseif ($paper_type == '2') {
    $log_query = $db->prepare("SELECT log2.q_id, 2 AS paper_type, grade, roles, screen, duration, started, user_answer, DATE_FORMAT(started, '{$configObj->get('cfg_long_date_time')}') AS display_started, year, title, surname, initials, first_names, email, gender, ipaddress, lab_name, username, users.id, student_id, user_answer, q_type, log_metadata.userID, mark, status, attempt FROM (log2, log_metadata, questions, users ) LEFT JOIN sid ON users.id = sid.userID WHERE log_metadata.userID = users.id AND log2.metadataID = log_metadata.id AND log2.q_id = questions.q_id AND paperID = ? AND grade LIKE ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY userID, started, screen");
    $log_query->bind_param('isss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
  } elseif ($paper_type == '5') {
    $log_query = $db->prepare("SELECT log5.q_id, 5 AS paper_type, grade, roles, 1 AS screen, 0 AS duration, started, NULL AS user_answer, DATE_FORMAT(started, '{$configObj->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, email, gender, NULL AS ipaddress, lab_name, username, users.id, student_id, NULL AS user_answer, q_type, log_metadata.userID, mark, status, 1 AS attempt FROM (log5, log_metadata, questions, users ) LEFT JOIN sid ON users.id=sid.userID WHERE log_metadata.userID = users.id AND log5.metadataID = log_metadata.id AND log5.q_id = questions.q_id AND paperID = ? AND grade LIKE ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY userID, started, screen");
    $log_query->bind_param('isss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
  } else {
    $log_query = $db->prepare("SELECT log$paper_type.q_id, $paper_type AS paper_type, grade, roles, screen, duration, started, user_answer, DATE_FORMAT(started, '{$configObj->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, email, gender, ipaddress, lab_name, username, users.id, student_id, user_answer, q_type, log_metadata.userID, mark, status, attempt FROM (log$paper_type, log_metadata, questions, users ) LEFT JOIN sid ON users.id=sid.userID WHERE log_metadata.userID = users.id AND log$paper_type.metadataID = log_metadata.id AND log$paper_type.q_id = questions.q_id AND paperID = ? AND users.id=log_metadata.userID AND grade LIKE ? $roles_sql AND started>=? AND started<=? ORDER BY userID, started, screen");
    $log_query->bind_param('isss', $_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate']);
  }
  $log_query->execute();
  $log_query->bind_result($q_id, $paper_type, $grade, $tmp_roles, $screen, $duration, $started, $user_answer, $display_started, $year, $title, $surname, $initials, $first_names, $email, $gender, $ipaddress, $lab_name, $username, $tmp_userID, $student_id, $user_answer, $q_type, $tmp_userID, $mark, $status, $attempt);

  while ($log_query->fetch() !== null) {
    if (isset($_GET['repmodule']) and $_GET['repmodule'] != '' and !isset($user_modules[$tmp_userID]['idMod'])) {
      continue; //this user is not on the module set in repmodule so dont put them in the array
    }

    // Check if user is in log late so that we can catch any users who ONLY have log_late records
    if (in_array($tmp_userID, array_keys($log_late_missing_users))) {
      unset($log_late_missing_users[$tmp_userID]);
    }

    if ($old_screen != $screen or $old_userID != $tmp_userID or $old_started != $started) {
      $user_duration += $old_duration;
    }
    if (($old_userID != $tmp_userID) or ($old_started != $started)) {
      if ($old_userID != '') {
        writeUserResults($tmp_array, $user_results, $user_no, $tmp_mark, $tmp_user_mark_array, $user_duration, $marking_complete, $metadata_array, $paper_buffer, $demo);
        $tmp_array['questions'] = 0;
        $tmp_mark = 0;
        $tmp_user_mark_array = array();
        $user_duration = 0;
        $marking_complete = 1;
      }
      $tmp_array = array();
      $tmp_array['started']    = $started;
      $tmp_array['username']   = $username;
      $tmp_array['tmp_userID'] = $tmp_userID;

      if (isset($user_modules[$tmp_userID]['idMod'])) {
        $tmp_array['module'] = $user_modules[$tmp_userID]['idMod'];
      } else {
        $tmp_array['module'] = '';
      }

      $tmp_array['attempt']         = $attempt;
      $tmp_array['grade']           = $grade;
      $tmp_array['roles']           = $tmp_roles;
      $tmp_array['year']            = $year;
      $tmp_array['display_started'] = $display_started;
      $tmp_array['title']           = $title;
      $tmp_array['name']            = trim(str_replace("'","",$surname) . ',' . $first_names);
      $tmp_array['surname']         = $surname;
      $tmp_array['initials']        = $initials;
      $tmp_array['first_names']     = $first_names;
      $tmp_array['email']           = $email;
      $tmp_array['student_id']      = $student_id;
      $tmp_array['gender']          = $gender;
      $tmp_array['ipaddress']       = $ipaddress;
      $tmp_array['room']            = $lab_name;

      if ($tmp_array['room'] == '') {
        $tmp_array['room'] = '<span style="color:#808080">&lt;unknown&gt;</span>';
      }

      $tmp_array['xmean']           = 0;
      $tmp_array['xmean_adjusted']  = 0;
      $tmp_array['duration']        = $user_duration;
      $tmp_array['paper_type']      = $paper_type;
    }
    getUserMark($q_id, $user_answer, $mark, $exclusions, $status, $tmp_mark, $tmp_user_mark_array, $random_q_ids, $tmp_userID, $q_medians);

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
    if (!isset($_GET['repmodule']) or $_GET['repmodule'] == '' or (isset($user_modules[$old_userID]['idMod']) and $user_modules[$old_userID]['idMod'] == $_GET['repmodule'])) {

      $user_duration += $old_duration;
      $log_query->free_result();
      $log_query->close();
      writeUserResults($tmp_array, $user_results, $user_no, $tmp_mark, $tmp_user_mark_array, $user_duration, $marking_complete, $metadata_array, $paper_buffer, $demo);
    }
  }
  
  return $user_results;
}

function load_late_cohort($log_late_missing_users, $demo, $db) {  
  $late_cohort = array();
  $late_cohort_size = 0;
  if (count($log_late_missing_users) > 0) {
    // Get students who don't have records in main log
    $late_id_list = implode(',', array_keys($log_late_missing_users));

    $sql = <<< SQL
  SELECT DISTINCT u.id, u.username, u.title, u.surname, u.first_names, u.initials, sid.student_id, u.grade, '', u.gender
  FROM users u INNER JOIN sid ON u.id = sid.userID
  WHERE u.id IN ($late_id_list)
SQL;

    $result = $db->prepare($sql);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_userID, $tmp_username, $title, $surname, $first_names, $initials, $student_id, $grade, $moduleid, $gender);
    while ($result->fetch()) {
      $late_cohort[$late_cohort_size]['username']         = $tmp_username;
      $late_cohort[$late_cohort_size]['tmp_userID']       = $tmp_userID;
      $late_cohort[$late_cohort_size]['name']             = trim(str_replace("'","",$surname) . ',' . $first_names);
      $late_cohort[$late_cohort_size]['title']            = $title;
      $late_cohort[$late_cohort_size]['surname']          = demo_replace($surname, $demo);
      $late_cohort[$late_cohort_size]['first_names']      = demo_replace($first_names, $demo);
      $late_cohort[$late_cohort_size]['initials']         = demo_replace($initials, $demo);
      $late_cohort[$late_cohort_size]['student_id']       = demo_replace_number($student_id, $demo);
      $late_cohort[$late_cohort_size]['grade']            = $grade;
      $late_cohort[$late_cohort_size]['module']           = $moduleid;
      $late_cohort[$late_cohort_size]['gender']           = $gender;
      $late_cohort[$late_cohort_size]['display_started']  = $log_late_missing_users[$tmp_userID]['display_started'];
      $late_cohort[$late_cohort_size]['started']          = $log_late_missing_users[$tmp_userID]['started'];
      $late_cohort[$late_cohort_size]['timer_started']    = false;

      $late_cohort_size++;
    }
    $result->close();
  }
  
  return $late_cohort;
}

function add_rank(&$user_results) {
  // Put the whole array in marks order.
  $sortby = 'mark';
  $ordering = 'desc';
  $user_results = array_csort($user_results, $sortby, $ordering, SORT_NUMERIC);
  
  $display_rank = 1;
  $global_rank  = 1;
  $old_mark     = 0;
  
  $result_no = count($user_results);
  
  for ($i=0; $i<$result_no; $i++) {
    if ($user_results[$i]['mark'] != $old_mark) {
      $display_rank = $global_rank;
    }
    $user_results[$i]['rank'] = $display_rank;
    $old_mark = $user_results[$i]['mark'];
    $global_rank++;
  }
}

function flag_subpart(&$user_results, $percent, $ordering, $absent) {
  $user_no = count($user_results);
  if ($percent < 100) {
    // Sort by mark order.
    $sortby = 'mark';
    $user_results = array_csort($user_results, $sortby, $ordering, SORT_NUMERIC);
    $cohort_size = round(($user_no/100) * $percent);
    
    // Set visible/invisible flag where necessary.
    for ($i=0; $i<$user_no; $i++) {
      if ($i >= $cohort_size) {
        $user_results[$i]['visible'] = 0;
      }
    }
  } else {
    $cohort_size = $user_no;
  }
  
  return $cohort_size;
}

function add_late_students(&$user_results, $late_cohort, $paper_type) {
  $user_no = count($user_results);

  $late_cohort_size = count($late_cohort);
  if ($late_cohort_size > 0) {
    for ($i=0; $i < $late_cohort_size; $i++) {
      $late_user = $late_cohort[$i];
      if ($absent == 1) {
        $student_cohort = check_and_clear_cohort($student_cohort, $late_user['username']);
      }
      $user_results[$user_no]['name']             = $late_user['name'];
      $user_results[$user_no]['mark']             = 0;
      $user_results[$user_no]['adj_percent']      = 0;
      $user_results[$user_no]['percent']          = 0;
      $user_results[$user_no]['started']          = '';
      $user_results[$user_no]['username']         = $late_user['username'];
      $user_results[$user_no]['tmp_userID']       = $late_user['tmp_userID'];
      $user_results[$user_no]['student_grade']    = $late_user['grade'];
      $user_results[$user_no]['module']           = $late_user['module'];
      $user_results[$user_no]['year']             = '';
      $user_results[$user_no]['display_started']  = $late_user['display_started'];
      $user_results[$user_no]['started']          = $late_user['started'];
      $user_results[$user_no]['attempt']          = 1;
      $user_results[$user_no]['marking_complete'] = 0;
      $user_results[$user_no]['title']            = $late_user['title'];
      $user_results[$user_no]['surname']          = $late_user['surname'];
      $user_results[$user_no]['initials']         = $late_user['initials'];
      $user_results[$user_no]['first_names']      = $late_user['first_names'];
      $user_results[$user_no]['student_id']       = $late_user['student_id'];
      $user_results[$user_no]['gender']           = $late_user['gender'];
      $user_results[$user_no]['ipaddress']        = '';
      $user_results[$user_no]['duration']         = 0;
      $user_results[$user_no]['questions']        = '';
      $user_results[$user_no]['paper_type']       = $paper_type;
      $user_results[$user_no]['room']             = '';
      $user_results[$user_no]['visible']          = 1;    // Default to visible unless switched off below.
      $user_results[$user_no]['roles']            = 'Student';
      $user_results[$user_no]['rank']             = 99999999999;
      $user_results[$user_no]['decile']           = 10;
      $user_no++;
    }
  }
}

function add_absent_students($absent, &$user_results, $student_cohort) {
  $user_no = count($user_results);

  if ($absent == 1) {
    // Add in students in cohort who haven't taken the exam.
    $count_student_cohort = count($student_cohort);
    
    for ($i=0; $i < $count_student_cohort; $i++) {
      $user_results[$user_no]['name']             = $student_cohort[$i]['name'];
      $user_results[$user_no]['mark']             = 0;
      $user_results[$user_no]['adj_percent']      = 0;
      $user_results[$user_no]['percent']          = 0;
      $user_results[$user_no]['started']          = '';
      $user_results[$user_no]['username']         = $student_cohort[$i]['username'];
      $user_results[$user_no]['tmp_userID']       = $student_cohort[$i]['tmp_userID'];
      $user_results[$user_no]['student_grade']    = $student_cohort[$i]['grade'];
      $user_results[$user_no]['module']           = $student_cohort[$i]['module'];
      $user_results[$user_no]['year']             = '';
      $user_results[$user_no]['display_started']  = '';
      $user_results[$user_no]['title']            = $student_cohort[$i]['title'];
      $user_results[$user_no]['surname']          = $student_cohort[$i]['surname'];
      $user_results[$user_no]['initials']         = $student_cohort[$i]['initials'];
      $user_results[$user_no]['first_names']      = $student_cohort[$i]['first_names'];
      $user_results[$user_no]['student_id']       = $student_cohort[$i]['student_id'];
      $user_results[$user_no]['gender']           = $student_cohort[$i]['gender'];
      $user_results[$user_no]['ipaddress']        = '';
      $user_results[$user_no]['duration']         = 0;
      $user_results[$user_no]['questions']        = '';
      $user_results[$user_no]['paper_type']       = '';
      $user_results[$user_no]['room']             = '';
      $user_results[$user_no]['visible']          = 1;    // Default to visible unless switched off below.
      $user_results[$user_no]['roles']            = 'Student';
      $user_results[$user_no]['late']             = true;
      $user_results[$user_no]['rank']             = 99999999999;
      $user_results[$user_no]['decile']           = 10;
      $user_no++;
    }
  }
}

function convert_moduleIDs(&$user_results, $db) {
  $result_no = count($user_results);
  $moduleIDs = array();
  
  // Build up an array of IDs to module codes.
  for ($i=0; $i<$result_no; $i++) {
    $id = $user_results[$i]['module'];
    if ($id != '') {
      if (!isset($moduleIDs[$id])) {
        $moduleIDs[$id] = module_utils::get_moduleid_from_id($id, $db);
      }
    }
  }
  
  // Loop around the results array and convert to codes.
  for ($i=0; $i<$result_no; $i++) {
    if (isset($moduleIDs[$user_results[$i]['module']])) {
      $user_results[$i]['module'] = $moduleIDs[$user_results[$i]['module']];
    }
  }
}

function get_stats($user_results, $question_no, $propertyObj) {
  // Generate summary statistics.
  $user_no                = count($user_results);
  $stats['sum_of_marks']  = 0;
  $stats['out_of_range']  = 0;
  $stats['completed_no']  = 0;
  $stats['failures']      = 0;
  $stats['passes']        = 0;
  $stats['honours']       = 0;
  $mark_total             = 0;
  $percent_total          = 0;
  $stats['total_time']    = 0;
  $stats['max_mark']      = 0;
  $stats['min_mark']      = 9999;
  $stats['max_percent']   = 0;
  $stats['min_percent']   = 100;
  $median_mark_array      = array();
  $median_percent_array   = array();
  $marks_data             = array();
  $pass_mark              = $propertyObj->get_pass_mark();
  $distinction_mark       = $propertyObj->get_distinction_mark();

  for ($i=0; $i<$user_no; $i++) {
    if (isset($user_results[$i]['adj_percent']) and $user_results[$i]['questions'] >= $question_no and $user_results[$i]['visible'] == 1) {
      $stats['completed_no']++;
      $median_mark_array[] = $user_results[$i]['mark'];
      $median_percent_array[] = $user_results[$i]['adj_percent'];

      $mark_total += $user_results[$i]['mark'];
      $percent_total += $user_results[$i]['adj_percent'];
    }
    if (isset($user_results[$i]['mark']) and $user_results[$i]['visible'] == 1) {
      if (isset($user_results[$i]['adj_percent'])) {
        $marks_data[] = $user_results[$i]['adj_percent'];
        if ($user_results[$i]['adj_percent'] < $pass_mark) $stats['failures']++;
        if ($user_results[$i]['adj_percent'] < $stats['min_percent']) $stats['min_percent'] = $user_results[$i]['adj_percent'];
        if ($user_results[$i]['adj_percent'] > $stats['max_percent']) $stats['max_percent'] = $user_results[$i]['adj_percent'];
      }
      if ($user_results[$i]['adj_percent'] >= $pass_mark and $user_results[$i]['adj_percent'] < $distinction_mark) $stats['passes']++;
      if ($user_results[$i]['adj_percent'] >= $distinction_mark) $stats['honours']++;
      if ($user_results[$i]['mark'] < $stats['min_mark']) $stats['min_mark'] = $user_results[$i]['mark'];
      if ($user_results[$i]['mark'] > $stats['max_mark']) $stats['max_mark'] = $user_results[$i]['mark'];
      $stats['sum_of_marks'] += $user_results[$i]['mark'];
    }
    
    if ($user_results[$i]['visible'] == 0) {
      $stats['out_of_range']++;
    } else {
      $stats['total_time'] += $user_results[$i]['duration'];
    }
  }

  if ($stats['min_mark'] == 9999) $stats['min_mark'] = 0;    // Reset back to zero if still on default.
  $stats['range'] = $stats['max_mark'] - $stats['min_mark'];
  $stats['range_percent'] = $stats['max_percent'] - $stats['min_percent'];

  // Calculate StdDev.
  $xmean_total = 0;
  $xmean_percent_total = 0;
  for ($i=0; $i<$user_no; $i++) {
    if (isset($user_results[$i]['questions']) and $user_results[$i]['questions'] >= $question_no and $user_results[$i]['visible'] == 1 and $stats['completed_no'] > 0) {
      $xmean_total += (($user_results[$i]['mark'] - ($mark_total / $stats['completed_no'])) * ($user_results[$i]['mark'] - ($mark_total / $stats['completed_no'])));
      $xmean_percent_total += (($user_results[$i]['adj_percent'] - ($percent_total / $stats['completed_no'])) * ($user_results[$i]['adj_percent'] - ($percent_total / $stats['completed_no'])));
    }
  }
  if ($stats['completed_no'] > 1) {
    $stats['stddev_mark']    = sqrt($xmean_total / ($stats['completed_no'] - 1));
    $stats['stddev_percent'] = sqrt($xmean_percent_total / ($stats['completed_no'] - 1));
  } else {
    $stats['stddev_mark']    = 0;
    $stats['stddev_percent'] = 0;
  }

  if ($stats['completed_no'] > 0) {
    $stats['mean_mark']      = $mark_total / $stats['completed_no'];
    $stats['mean_percent']   = $percent_total / $stats['completed_no'];
    $stats['median_mark']    = MathsUtils::median($median_mark_array);
    $stats['median_percent'] = MathsUtils::median($median_percent_array);
  } else {
    $stats['mean_mark']      = 0;
    $stats['mean_percent']   = 0;
    $stats['median_mark']    = 0;
    $stats['median_percent'] = 0;
  }

  $stats['q1'] = MathsUtils::percentile($marks_data, 0.75);
  $stats['q2'] = MathsUtils::percentile($marks_data, 0.50);
  $stats['q3'] = MathsUtils::percentile($marks_data, 0.25);

  $decile_values = array();
  for ($i=1; $i<10; $i++) {
    $stats["decile$i"] = MathsUtils::percentile($marks_data, ($i / 10));
  }

  return $stats;  
}

function writeStudentDeciles(&$user_results, $stats) {
  $student_no = count($user_results);
  for ($user_no=0; $user_no<$student_no; $user_no++) {
  
    for ($i=9; $i>0; $i--) {
      if ($user_results[$user_no]['adj_percent'] >= $stats["decile$i"]) {
        $user_results[$user_no]['decile'] = $i;
      }
    }
  
  }
}

function sort_results($percent, $ordering, $sortby, &$user_results) {
  $tmp_sort = ($sortby != 'result' and $sortby != 'percent') ? $sortby : 'adj_percent';
  if ($tmp_sort == 'adj_percent') $tmp_sort = 'mark';
  if ($tmp_sort == 'classification') $tmp_sort = 'mark';
  if ($tmp_sort == 'mark' or $tmp_sort == 'rank' or $tmp_sort == 'decile') {
    $method = SORT_NUMERIC;
  } else {
    $method = SORT_STRING;
  }
  $user_results = array_csort($user_results, $tmp_sort, $ordering, $method);
}
?>