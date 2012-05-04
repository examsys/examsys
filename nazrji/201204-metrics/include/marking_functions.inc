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
* Function used to mark questions used in ../start.php and ../finsh.php
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/
require_once $cfg_web_root . 'classes/networkutils.class.php';
require_once $cfg_web_root . 'classes/stringutils.class.php';

/**
 * This is function time_to_seconds
 *
 * @param mixed $seconds time in seconds
 * @return mixed time in hours minutes and seconds
 *
 */
function time_to_seconds($seconds) {
  $hr = intval(substr($seconds,8,2));
  $min = intval(substr($seconds,10,2));
  $sec = intval(substr($seconds,12,2));
  return ($hr * 3600) + ($min * 60) + $sec;
}

/**
 * This is function record_marks, is used to mark the users answer, and record it in the database for all question types. 
 *
 * @param mixed $paper_id 
 * @param mixed $screen_no 
 * @param mixed $dblink 
 * @param mixed $userid 
 * @param mixed $paper_type T
 * @param mixed $student_grade 
 * @param mixed $student_year
 * @param mixed $userroles 
 *
 */
function record_marks($paper_id, $dblink, $userid, $paper_type, $student_grade, $student_year, $attempt, $userroles) {
  $screen_no =  $_POST['old_screen'];
  $extra_duration = $_POST['previous_duration'];

  // Load the structure of the paper into a local array.
  $paper_array = array();
  $tmp_question_no = 0;
  $old_q_id = 0;
  $old_q_type = '';
  $random_on = false;
  $branch_no = 0;
  $option_order = '';
  $old_display_pos = 0;
  $questions = $dblink->prepare("SELECT q_id, scenario, q_type, option_text, q_media, correct, display_method, score_method, marks_correct, marks_incorrect, marks_partial, display_pos FROM papers, questions, options WHERE paper=? AND screen=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
  $questions->bind_param('ii', $paper_id, $screen_no); 
  $questions->execute();
  $questions->store_result();
  $questions->bind_result($q_id, $scenario, $q_type, $option_text, $q_media, $correct, $display_method, $score_method, $marks_correct, $marks_incorrect, $marks_partial, $display_pos);
  while ($questions->fetch()) {
    if ($old_q_id != $q_id or $old_display_pos != $display_pos) {
      if ($q_type != 'info') $tmp_question_no++;
      $selected_q_id = array();
      $branch_no = 0;
      $random_on = false;
    }
    if ($q_type == 'random' or $q_type == 'branching' or $q_type == 'keyword_based') {
      if ($q_type == 'random' or $q_type == 'keyword_based') {
        $selected_q_id[] = (isset($_POST['q' . $tmp_question_no . '_randomID'])) ? $_POST['q' . $tmp_question_no . '_randomID'] : 0;
      } else {
  	    if (isset($_POST['q' . $q_id . '_' . $branch_no . '_branchID'])) {
	        // the user went down branch $branch_no so saw the questions stored in option option_text
          // put thes in an array to lookup and add to the paper_array
	        $selected_q_id = explode(',',$option_text);
	      }
	      $branch_no++;
      }

      if ($random_on == false and count($selected_q_id) > 0) {
        foreach ($selected_q_id as &$used_qid) {
	        // Look up database.
          $random_data = $dblink->prepare("SELECT q_id, scenario, q_type, option_text, q_media, correct, display_method, score_method, marks_correct FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
          $random_data->bind_param('i', $used_qid); 
          $random_data->execute();
          $random_data->store_result();
          $random_data->bind_result($new_q_id, $scenario, $q_type, $option_text, $q_media, $correct, $display_method, $score_method, $marks_correct);
          while ($random_data->fetch()) {
            $paper_array[] = array('q_id'=>$new_q_id,'scenario'=>$scenario,'q_type'=>$q_type,'option_text'=>$option_text,'q_media'=>$q_media,'correct'=>$correct,'display_method'=>$display_method,'score_method'=>$score_method,'marks_correct'=>$marks_correct,'marks_incorrect'=>$marks_incorrect,'marks_partial'=>$marks_partial,'question_no'=>$tmp_question_no,'display_pos'=>$display_pos);
          }
          if ($random_data->num_rows == 0) {
            $paper_array[] = array('q_id'=>0,'scenario'=>'','q_type'=>'','option_text'=>'','q_media'=>'','correct'=>'','display_method'=>'','score_method'=>'','marks_correct'=>0,'marks_incorrect'=>0,'marks_partial'=>0,'question_no'=>$tmp_question_no,'display_pos'=>$display_pos);
          }
          $random_data->close();
          
        }
	      $selected_q_id = array();
        $random_on = true;
      }
    } else {
      $paper_array[] = array('q_id'=>$q_id,'scenario'=>$scenario,'q_type'=>$q_type,'option_text'=>$option_text,'q_media'=>$q_media,'correct'=>$correct,'display_method'=>$display_method,'score_method'=>$score_method,'marks_correct'=>$marks_correct,'marks_incorrect'=>$marks_incorrect,'marks_partial'=>$marks_partial,'question_no'=>$tmp_question_no,'display_pos'=>$display_pos);
    }
    $old_q_type = $q_type;
    $old_q_id = $q_id;
    $old_display_pos = $display_pos;
  }
  $questions->close();
  
  $log_ids = array();
  $log_check = $dblink->prepare("SELECT id, q_id FROM log$paper_type WHERE userID=? AND q_paper=? AND screen=? AND started=?");
  $log_check->bind_param('siis', $userid, $paper_id, $screen_no, $_POST['sessionid']);
  $log_check->execute();
  $log_check->bind_result($tmp_id, $tmp_q_id);
  while ($log_check->fetch()) {
    $log_ids[$tmp_q_id] = $tmp_id;
  }
  $log_check->close();

  // Set up new insert query ready.
  if (count($log_ids) > 0) {
    $save_answers = $dblink->prepare("UPDATE log$paper_type SET mark=?, user_answer=?, duration=?, updated=NOW(), dismiss=? WHERE id=?");
    $save_answers->bind_param('dsisi', $mark, $saved_response, $tmp_duration, $dismiss, $q_log_id);
  } else {
    if ($screen_no == 1) {       // Write log_metadata first on screen 1 only
      $tmp_ip_address = NetworkUtils::get_ipaddress();
      
      $save_metadata = $dblink->prepare("INSERT INTO log_metadata VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)");
      $save_metadata->bind_param('iisssii', $userid, $paper_id, $_POST['sessionid'], $tmp_ip_address, $student_grade, $student_year, $attempt);
      $save_metadata->execute();
      $save_metadata->close();
    }
    $save_answers = $dblink->prepare("INSERT INTO log$paper_type VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
    $save_answers->bind_param('isiidisiiss', $userid, $_POST['sessionid'], $paper_id, $log_id, $mark, $totalpos, $saved_response, $screen_no, $tmp_duration, $dismiss, $option_order);
  }
    
  // Use the new structure to look for posted responses.
  $old_type = '';
  $old_score_method = '';
  $old_display_method = '';
  $correct_rank = true;
  
  $no_correct = 0;
  $no_incorrect = 0;
  
  $question_no = 0;
  $q_type = '';
  $question_part = 0;
  $saved_response = '';
  $mrq_options_selected = 0;
  $log_id = 0;
  $mark = 0;
  $totalpos = 0;
  $old_q_id = 0;
  $submit_time = date("YmdHis", time());
  $log_query = '';
  $display_pos = 0;
  $old_display_pos = 0;
  $all_items_abstain = true;
  $dismiss = '';
  foreach ($paper_array as &$row) {
    $q_log_id = 0;
    $q_id = $row['q_id'];
    $scenario = $row['scenario'];
    $q_type = $row['q_type'];
    $option_text = $row['option_text'];
    $q_media = $row['q_media'];
    $correct = $row['correct'];
    $score_method = $row['score_method'];
    $display_method = $row['display_method'];
    $marks_correct = $row['marks_correct'];
    $marks_incorrect = $row['marks_incorrect'];
    $marks_partial = $row['marks_partial'];

    $display_pos = $row['display_pos'];
    if ($old_q_id != $q_id or $display_pos != $old_display_pos) {
      if ($old_type == 'rank') {
        finalise_rank($old_score_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $saved_response, $totalpos, $mark);
      } elseif ($old_type == 'mrq') {
        finalise_mrq($old_score_method, $old_display_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $question_no, $mrq_options_selected, $totalpos, $mark, $question_part, $qid, $saved_response);
      } elseif ($old_type == 'dichotomous') {
        finalise_dichotomous($old_score_method, $old_marks_correct, $old_marks_incorrect, $no_correct, $no_incorrect, $saved_response, $totalpos, $mark);
      }
      if (isset($_POST["order$question_no"])) {
        $option_order = $_POST["order$question_no"];
      } else {
        $option_order = '';
      }
      if ($question_no > 0 and $old_type != 'info') {
        $tmp_duration = time_to_seconds($submit_time) - time_to_seconds($_POST['page_start']);
        if ($tmp_duration < 0) $tmp_duration += 86400;
        $tmp_duration += $extra_duration;

        if (isset($log_ids[$old_q_id])) {
          $q_log_id = $log_ids[$old_q_id];
        }
        $save_answers->execute();     // Save to the database.
     
        $saved_response = '';
        $dismiss = '';
      }      
      if ($q_type != 'info') $question_no++;
      $question_part = 0;
      $old_q_id = $q_id;
      $old_type = $q_type;
      $old_score_method = $score_method;
      $old_display_method = $display_method;
      $old_display_pos = $display_pos;
      
      $old_marks_correct = $marks_correct;
      $old_marks_incorrect = $marks_incorrect;
      $old_marks_partial = $marks_partial;

      $correct_rank = true;
      $no_correct = 0;
      $no_incorrect = 0;
      $mrq_options_selected = 0;
      $mark = 0;
      $totalpos = 0;
      $saved_response = '';
    }  

    $log_id = $q_id;
    if (isset($_POST["order$question_no"])) {
      $option_order = explode(',',$_POST["order$question_no"]);
    } else {
      $option_order = '';
    }
    $question_part++;

    switch ($q_type) {
      case 'calculation':
        $mark = 0;
        // Log format is 'users answer', 'correct answer', 'variable values used'.
        $score_array = explode(',', $old_display_method);
      
        $qid = 'q' . $question_no;
        if (isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response = '';
        }
        $saved_response = trim($saved_response);

        $qid = 'q' . $question_no . '_answer';
        if (isset($_POST[$qid])) {
          $question_variables = $_POST[$qid];
        } else {
          $question_variables = '';
        }

        $saved_response_clean = preg_replace('([^0-9\.\-])', '', $saved_response);
        
        $variable_array = explode(',',$question_variables);
        $var_no = 1;
        foreach ($variable_array as $individual_variable) {
          switch ($var_no) {
            case 1:
              $A = $individual_variable;
              break;
            case 2:
              $B = $individual_variable;
              break;
            case 3:
              $C = $individual_variable;
              break;
            case 4:
              $D = $individual_variable;
              break;
            case 5:
              $E = $individual_variable;
              break;
            case 6:
              $F = $individual_variable;
              break;
            case 7:
              $G = $individual_variable;
              break;
            case 8:
              $H = $individual_variable;
              break;
            case 9:
              $I = $individual_variable;
              break;
            case 10:
              $J = $individual_variable;
              break;
          }
          $var_no++;
        }
        $answer_equation = trim($correct);

        if ($answer_equation != '' and check_formula($answer_equation, $variable_array)) {
          eval ("\$answer = $answer_equation;");
          $decimals = ($score_array[0] != '') ? $score_array[0] : 0;
          $answer = round($answer, $decimals);
        } else {
          $decimals = 0;
          $answer = '';
        }

        $difference = round(abs($saved_response_clean - $answer), 12);

        $tolerance_full = $score_array[1];
        if (StringUtils::ends_with($tolerance_full, '%')) {
          $tolerance_perc = rtrim($tolerance_full, '%');
          $tolerance_full = $answer * ($tolerance_perc/100);
        }
        $tolerance_partial = $score_array[2];
        if (StringUtils::ends_with($tolerance_partial, '%')) {
          $tolerance_perc = rtrim($tolerance_partial, '%');
          $tolerance_partial = $answer * ($tolerance_perc/100);
        }

        if ($saved_response_clean != '') {
          if ($saved_response_clean == '' . $answer) {    // Want to treat $answer as a string here
            $mark = $marks_correct;
          } elseif ($difference > 0 and $difference <= $tolerance_full and $tolerance_full > 0) {
            $mark = $marks_correct;
          } elseif ($difference > 0 and $difference <= $tolerance_partial and $tolerance_partial > 0) {
            $mark = $marks_partial;
          } else {
            $mark = $marks_incorrect;
          }
        }

        $saved_response .= '|' . $answer . '|' . $question_variables;
        $totalpos = $marks_correct;
        break;
      case 'dichotomous':
        $qid = 'q' . $question_no . '_' . $question_part;
        if (isset($_POST[$qid])) {
          $user_response = $_POST[$qid];
        } else {
          $user_response = '';
        }
        if ($user_response == '') {
          $saved_response .= 'u';
        } else {
          $saved_response .= $user_response;
        }
        if ($user_response == $correct) {
          $mark += $marks_correct;
          $no_correct++;
        } elseif ($user_response != '') {
          $mark += $marks_incorrect;
          $no_incorrect++;
        }
        $totalpos += $marks_correct;
        break;
      case 'likert':
        $qid = 'q' . $question_no . '_' . $question_part;
        if (isset($_POST[$qid])) {
          $user_response = $_POST[$qid];
        } else {
          $user_response = '';
        }
        
        if ($user_response == '') {
          $saved_response .= 'u';
        } else {
          $saved_response .= $user_response;
        }
        break;
      case 'mcq':
        if (isset($_POST["dismiss$question_no"])) {
          $dismiss = $_POST["dismiss$question_no"];
        } else {
          $dismiss = '';
        }
        $qid = 'q' . $question_no;
        if (isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response = '';
        }
        if ($saved_response == 'other') {      // Survey use - 'other' textbox.
          $qid = 'q' . $question_no . '_other';
          $saved_response = 'other:' . $_POST[$qid];
        }
        if ($saved_response == '') $saved_response = '0';
        if ($totalpos == 0) {
          if ($saved_response == $correct) {
            $mark += $marks_correct;
          } elseif ($saved_response != '0') {
            $mark += $marks_incorrect;
          }
          $totalpos += $marks_correct;
        }
        break;
      case 'mrq':
        if (isset($_POST["dismiss$question_no"])) {
          $dismiss = $_POST["dismiss$question_no"];
        } else {
          $dismiss = '';
        }
        $qid = 'q' . $question_no . '_' . $question_part;
        if (isset($_POST[$qid]) and $_POST[$qid] == 'y') {
          $user_response = 'y';
          $mrq_options_selected++;
        } else {
          $user_response = 'n';
        }
        $saved_response .= $user_response;
        if ($score_method == 'Mark per Option') {
          if ($user_response == 'y' and $correct == 'y') {
            $mark += $marks_correct;
          } elseif ($user_response == 'n' and $correct == 'y') {
            $mark += $marks_incorrect;
          }
        } else {
          if ($user_response == $correct) {
            $mark += $marks_correct;
          } else {
            $mark += $marks_incorrect;
          }
        }
        if ($score_method == 'Mark per Option') {
          if ($correct == 'y') $totalpos += $marks_correct;
        } elseif ($score_method == 'Mark per Question') {
          $totalpos = $marks_correct;
          if (($user_response == 'y' and $correct == 'n') or ($user_response == 'n' and $correct == 'y')) {
            $correct_rank = false;
          }
        } else {
          $totalpos++;
        }
        break;
      case 'extmatch':
        // Individual scenarios are separated by '|' characters.
        // Separate options are separated by '$' characters.
        if ($question_part == 1) {
          $part_no = 0;
          if ($paper_type == 3) {
            $correct_options = explode('|',$scenario);
          } else {
            $correct_options = explode('|',$correct);
          }
          foreach ($correct_options as $individual_correct) {
            if (trim($individual_correct) != '') $part_no++;
          }
        
          $option_no = 1;
          for ($scenario_no=0; $scenario_no<$part_no; $scenario_no++) {
            if ($option_no > 1) {
              $saved_response .= '|';
            }
            $qid = 'q' . $question_no . '_' . $option_no;
            $correct_answers = explode('$',$correct_options[$option_no - 1]);
            if (isset($_POST[$qid])) {
              $answer_count = count($_POST[$qid]);
            } else {
              $answer_count = 0;
            }
              
            for ($i=0; $i<$answer_count; $i++) {
              if ($i == 0) {
                if (count($correct_answers) == 1) {
                  $saved_response .= $_POST[$qid];
                } else {
                  $saved_response .= $_POST[$qid][$i];
                }
              } else {
                $saved_response .= '$' . $_POST[$qid][$i];
              }
              
              
              if ($answer_count == 1) {
                if ($_POST[$qid] == $correct_answers[0]) {
                  $mark += $marks_correct;
                  $all_items_abstain = false;
                } elseif ($_POST[$qid] != 'u') {
                  $mark += $marks_incorrect;
                  $all_items_abstain = false;
                }
              } else {
                if (in_array($_POST[$qid][$i], $correct_answers)) {
                  $mark += $marks_correct;
                } else {
                  $mark += $marks_incorrect;
                }
              }
            }
            $totalpos += count($correct_answers) * $marks_correct;
            $option_no++;
          }
        }

        if ($score_method == 'Mark per Question') {   // Override marks if 'Mark per Question' is set.
          if ($mark == $totalpos) {
            $mark = $marks_correct;
          } elseif ($all_items_abstain) {
            $mark = 0;
          } else {
            $mark = $marks_incorrect;
          }
          $totalpos = $marks_correct;
        }
        break;
      case 'matrix':
        // Individual scenarios are separated by '|' characters.
        if ($question_part == 1) {
          $correct_options = explode('|',$correct);
          $matching_scenarios = explode('|', $scenario);
          $option_no = 1;
          $no_correct = 0;
          $no_incorrect = 0;
          $question_parts = 0;
          foreach ($matching_scenarios as $single_scenario) {
            if (trim($single_scenario) != '') {
              $question_parts++;
              $qid = 'q' . $question_no . '_' . $option_no;
              if (isset($_POST[$qid]) and $_POST[$qid] == $correct_options[$option_no - 1]) {
                $mark += $marks_correct;
                $no_correct++;
              } elseif (isset($_POST[$qid]) and $_POST[$qid] != 'u') {
                $mark += $marks_incorrect;
                $no_incorrect++;
              }
              if ($option_no == 1) {
                if(isset($_POST[$qid])) {
                  $saved_response = $_POST[$qid];
                } else {
                  $saved_response = '';
                }
              } else {
                $saved_response .= '|';
                if (isset($_POST[$qid])) $saved_response .= $_POST[$qid];
              }
              $option_no++;
            }
          }
          $totalpos = $option_no - 1;
        }

        if ($score_method == 'Mark per Question') {   // Override marks if 'Mark per Question' is set.
          if ($no_correct == $question_parts) {
            $mark = $marks_correct;
          } elseif ($no_correct == 0 and $no_incorrect == 0) {
            $mark = 0;
          } else {
            $mark = $marks_incorrect;
          }
          $totalpos = $marks_correct;
        }
        break;
      case 'rank':
        if(isset($_POST["dismiss$question_no"])) {
          $dismiss = $_POST["dismiss$question_no"];
        } else {
          $dismiss = '';
        }
        $qid = 'q' . $question_no . '_' . $question_part;
        if ($saved_response == '') {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response .= ',' . $_POST[$qid];
        }
        if ($score_method == 'Mark per Option') {
          if ($_POST[$qid] != 'u') {
            $mark += ($_POST[$qid] == $correct) ? $marks_correct : $marks_incorrect;
          }
        } elseif ($score_method == 'Mark per Question') {
          if ($_POST[$qid] != 'u' and $_POST[$qid] <> $correct) $correct_rank = false;
        } elseif ($score_method == 'Allow partial Marks') {
          if ($_POST[$qid] != 'u') {
            if ($_POST[$qid] != 0) {
              if ($correct != 0) {
                if ($_POST[$qid] == $correct) {
                  $mark += $marks_correct;
                } elseif ($_POST[$qid] == ($correct + 1)) {
                  $mark += $marks_partial;
                } elseif ($_POST[$qid] == ($correct - 1)) {
                  $mark += $marks_partial;
                } else {
                  $mark += $marks_incorrect;
                }
              } elseif ($correct == 0) {
                $mark += $marks_incorrect;
              }
            }
          }
        } elseif ($score_method == 'Bonus Mark') {
          if ($_POST[$qid] != 0) {
            $mark += ($correct != 0) ? $marks_correct : $marks_incorrect;
            if ($_POST[$qid] <> $correct) $correct_rank = false;
          }
          if ($_POST[$qid] == 0 and $correct != 0) $correct_rank = false;
        }
        if ($score_method == 'Mark per Question') {
          $totalpos = $marks_correct;
        } elseif ($correct != 0 or $score_method == 'Mark per Option') {
          $totalpos += $marks_correct;
        }
        break;
      case 'sct':
        if (isset($_POST["dismiss$question_no"])) {
          $dismiss = $_POST["dismiss$question_no"];
        } else {
          $dismiss = '';
        }
        $qid = 'q' . $question_no;
        if (isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response = '';
        }
        if ($saved_response == '') $saved_response = '0';
        
        if ($saved_response == $question_part) {
          $max = 0;
          foreach ($paper_array as $tmp_row) {
            if ($tmp_row['q_id'] == $row['q_id'] and $tmp_row['correct'] > $max) $max = $tmp_row['correct'];
          }
          if ($max > 0) {
            $mark = $row['correct'] / $max;
          }
        }
        if ($totalpos == 0) {
          $totalpos += $marks_correct;
        }
        break;
      case 'true_false':
        $dismiss = '';
 
        $qid = 'q' . $question_no;
        if (isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response = '';
        }
        
        if ($saved_response == '') $saved_response = 'u';
        
        if ($saved_response == $correct) {
          $mark += $marks_correct;
        } elseif ($saved_response != 'u') {
          $mark += $marks_incorrect;
        }
        $totalpos += $marks_correct;
        
        break;
      case 'blank':
        $blank_details = explode("[blank",$option_text);
        $no_answers = count($blank_details) - 1;
        $have_answer = false;
       
        $saved_response = '';
        for ($i=1; $i<=$no_answers; $i++) {
          $qid = 'q' . $question_no . '_' . $i;
          if (isset($_POST[$qid])) {
            if ($_POST[$qid] == '') {
              $saved_response .= '|u';
            } else {
              $saved_response .= '|' . $_POST[$qid];
            }
          }
          
          if (preg_match("|mark=\"([0-9]{1,3})\"|", $blank_details[$i], $mark_matches)) {
            $totalpos += $mark_matches[1];
            $individual_q_mark = $mark_matches[1];
          } else {
            $totalpos += $marks_correct;
            $individual_q_mark = $marks_correct;
          }

          // Get correct answer.
          $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
          $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
          $answer_list = explode(',',$blank_details[$i]);

          $answer_list[0] = str_replace("[/blank]",'',$answer_list[0]);
          if ($display_method == 'textboxes') {
            if (isset($_POST[$qid]) and $_POST[$qid] != 'u' and $_POST[$qid] != '') {
              $have_answer = true;
              $is_correct = false;
              foreach ($answer_list as $individual_answer) {
                if (str_replace('&nbsp;',' ',trim(strtolower($_POST[$qid]))) == str_replace('&nbsp;',' ',trim(strtolower($individual_answer)))) {
                  $is_correct = true;
                  break;
                }
              }
              $mark += ($is_correct) ? $individual_q_mark : $marks_incorrect;
            }
          } else {
            if (isset($_POST[$qid]) and $_POST[$qid] != 'u') {
              $have_answer = true;
              $mark += (str_replace('&nbsp;',' ',trim($_POST[$qid])) == str_replace('&nbsp;',' ',($answer_list[0]))) ? $individual_q_mark : $marks_incorrect;
            }
          }
        }
        
        // Recalculate if mark per question
        if ($score_method == 'Mark per Question') {
          if ($have_answer) {
            $mark = ($mark == $totalpos) ? $marks_correct : $marks_incorrect;
          }
          $totalpos = $marks_correct;
        }
        break;
      case 'textbox':
        $qid = 'q' . $question_no;
        if (isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response = '';
        }
        $mark = NULL;
        $totalpos += $marks_correct;
        break;
      case 'hotspot':
        $qid = 'q' . $question_no;
        $mark = 0;
        $all_correct = true;
        if (isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
          $sub_parts = explode('|',$saved_response);
          foreach ($sub_parts as $sub_part) {
            if ($sub_part{0} == 1) {
              $mark += $marks_correct;
            } else {
              $all_correct = false;
              if ($saved_response != 'u') {
                $mark += $marks_incorrect;
              }
            }
          }
        } else {
          $all_correct = false;
          $saved_response = '';
        }
        if ($score_method == 'Mark per Question') {   // Override marks if 'Mark per Question' is set.
          if ($all_correct) {
            $mark = $marks_correct;
          } elseif ($saved_response == 'u' or $saved_response == '') {
            $mark = 0;
          } else {
            $mark = $marks_incorrect;
          }
          $totalpos = $marks_correct;
        } else {
          $totalpos = (substr_count($correct, '|') + 1) * $marks_correct;
        }
        break;
      case 'labelling':
        $qid = 'q' . $question_no;
        if (isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response = '';
        }
        if ($saved_response != '') {        
          $tmp_first_split = explode(';',$saved_response);
          $tmp_second_split = explode('$',$tmp_first_split[0]);
          if ($tmp_first_split[1] != '') {
            $all_items_abstain = false;
          }
          $mark += $tmp_second_split[0];
          $totalpos += $tmp_second_split[1];
        }
        
        if ($all_items_abstain) {
          $mark = 0;
        } elseif ($score_method == 'Mark per Question') {   // Override marks if 'Mark per Question' is set.
          if ($mark == $totalpos) {
            $mark = $marks_correct;
          } else {
            $mark = $marks_incorrect;
          }
          $totalpos = $marks_correct;
        }
        break;
      case 'flash':
        $qid = 'q' . $question_no;
        if(isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response = '';
        }
        $flash_args = explode(',',$saved_response);
        $mark = $flash_args[0];     // Get student marks from Flash.
        $totalpos += $marks_correct;
        break;
    }
  }                    // End of while loop.

  // Log the very last question block.
  if ($old_type == 'rank') {
    finalise_rank($old_score_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $saved_response, $totalpos, $mark);
  } elseif ($old_type == 'mrq') {
    finalise_mrq($old_score_method, $old_display_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $question_no, $mrq_options_selected, $totalpos, $mark, $question_part, $qid, $saved_response);
  } elseif ($old_type == 'dichotomous') {
    finalise_dichotomous($old_score_method, $old_marks_correct, $old_marks_incorrect, $no_correct, $no_incorrect, $saved_response, $totalpos, $mark);
  }
  if (isset($_POST["order$question_no"])) {
    $option_order = $_POST["order$question_no"];
  } else {
    $option_order = '';
  }
  if ($question_no > 0 and $q_type != 'info') {
    $tmp_duration = time_to_seconds($submit_time) - time_to_seconds($_POST['page_start']);
    if ($tmp_duration < 0) $tmp_duration += 86400;
    $tmp_duration += $extra_duration;

    if (isset($log_ids[$old_q_id])) {
      $q_log_id = $log_ids[$old_q_id];
    }
    $save_answers->execute();     // Save to the database.
 
    $saved_response = '';
    $dismiss = '';
  }      
        
  // Close still open database prepared queries
  $save_answers->close();
}

/**
 * Check if any of the variables used in a formula evaluates to ERROR (a student answer from a previous question that
 * hasn't been answered), in which case it cannot be processed
 * @param $formula Formula to be evaluated
 * @param $variables Array of variable values
 * @return bool
 */
function check_formula($formula, $variables) {
  $ok = true;
  preg_match_all('/\$[A-J]{1}/', $formula, $matches);
  if (is_array($matches[0])) {
    foreach ($matches[0] as $var) {
      $index = ord(substr($var, 1, 1)) - 65;
      if ($variables[$index] == 'ERROR') {
        $ok = false;
      }
    }
  }
  return $ok;
}

function finalise_rank($old_score_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $saved_response, &$totalpos, &$mark) {
  if ($old_score_method == 'Bonus Mark') {
    $totalpos += $old_marks_correct;
    if ($correct_rank == true and $mark == ($totalpos - $old_marks_correct)) {
      $mark += $old_marks_correct;  // Add one mark if the user has all options in the correct order
    }
  } elseif ($old_score_method == 'Mark per Question') {
    $totalpos = $old_marks_correct;
    if ($correct_rank) {
      // If question is unanswered, $saved_response will be a comma separated list of 'u' so check for other characters
      $answered = preg_match('/[^u,]/', $saved_response);
      $mark = ($answered > 0) ? $old_marks_correct : 0;
    } else {
      $mark = $old_marks_incorrect;
    }
  }
}

function finalise_mrq($old_score_method, $old_display_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $question_no, $mrq_options_selected, $totalpos, &$mark, &$question_part, &$qid, &$saved_response) {
  if ($old_score_method == 'Mark per Question') {
    if ($correct_rank) {
      $mark = $old_marks_correct;
    } else {
      $mark = $old_marks_incorrect;
    }
  } elseif ($old_score_method == 'Mark per Option' and $mrq_options_selected > $totalpos) {
    $mark = 0;
  }
  if ($old_display_method == 'other') {
    $question_part++;
    $qid = 'q' . $question_no . '_' . $question_part;
    if (isset($_POST[$qid]) and $_POST[$qid] == 'y') {
      $saved_response .= 'y';
      $qid = 'q' . $question_no . '_other';
      $saved_response .= $_POST[$qid];
    } else {
      $saved_response .= 'n';
    }
  }
}

function finalise_dichotomous($old_score_method, $old_marks_correct, $old_marks_incorrect, $no_correct, $no_incorrect, $saved_response, &$totalpos, &$mark) {
  if ($old_score_method == 'Mark per Question') {
    $totalpos = $old_marks_correct;
    if ($no_correct == 0 and $no_incorrect == 0) {    // Abstained to all options
      $mark = 0;
    } elseif ($no_correct == strlen($saved_response)) {
      $mark = $old_marks_correct;
    } else {
      $mark = $old_marks_incorrect;
    }
  }

}
?>