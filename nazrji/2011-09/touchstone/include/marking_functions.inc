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
* Function used to mark questions used in ../start.php and ../finsh.php
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
require_once realpath(dirname(__FILE__).DIR_SEPARATOR.'../classes/networkutils.class.php');

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

  $log_ids = array();
  $log_check = $dblink->prepare("SELECT id, q_id FROM log$paper_type WHERE userID=? AND q_paper=? AND screen=? AND started=?");
  $log_check->bind_param('siis', $userid, $paper_id, $screen_no, $_POST['sessionid']);
  $log_check->execute();
  $log_check->bind_result($tmp_id, $tmp_q_id);
  while ($row = $log_check->fetch()) {
    $log_ids[$tmp_q_id] = $tmp_id;
  }
  $log_check->close();

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
  while ($row = $questions->fetch()) {
    if ($old_q_id != $q_id or $old_display_pos != $display_pos) {
      if ($q_type != 'info') $tmp_question_no++;
      $selected_q_id = array();
      $branch_no = 0;
      $random_on = false;
    }
    if ($q_type == 'random' or $q_type == 'branching' or $q_type == 'keyword_based') {
      if ($q_type == 'random' or $q_type == 'keyword_based') {
        $selected_q_id[] = $_POST['q' . $tmp_question_no . '_randomID'];
      } else {
  	    if (isset($_POST['q' . $q_id . '_' . $branch_no . '_branchID'])) {
	        // the user went down branch $branch_no so saw the questions stored in option option_text
          // put thes in an array to lookup and add to the paper_array
	        $selected_q_id = explode(',',$option_text);
	      }
	      $branch_no++;
      }

      if ($random_on == false and count($selected_q_id) > 0) {
        foreach($selected_q_id as &$used_qid) {
	        // Look up database.
          $random_data = $dblink->prepare("SELECT q_id, scenario, q_type, option_text, q_media, correct, display_method, score_method, marks_correct FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
          $random_data->bind_param('i', $used_qid); 
          $random_data->execute();
          $random_data->store_result();
          $random_data->bind_result($new_q_id, $scenario, $q_type, $option_text, $q_media, $correct, $display_method, $score_method, $marks_correct);
          while ($random_row = $random_data->fetch()) {
            $paper_array[] = array('q_id'=>$new_q_id,'scenario'=>$scenario,'q_type'=>$q_type,'option_text'=>$option_text,'q_media'=>$q_media,'correct'=>$correct,'display_method'=>$display_method,'score_method'=>$score_method,'marks_correct'=>$marks_correct,'marks_incorrect'=>$marks_incorrect,'marks_partial'=>$marks_partial,'question_no'=>$tmp_question_no,'display_pos'=>$display_pos);
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

  // Set up new insert query ready.
  if (count($log_ids) > 0) {
    $save_answers = $dblink->prepare("UPDATE log$paper_type SET mark=?, user_answer=?, duration=?, updated=NOW(), dismiss=? WHERE id=?");
    $save_answers->bind_param('dsisi', $mark, $saved_response, $tmp_duration, $dismiss, $q_log_id);
  } else {
    if ($screen_no == 1) {       // Write log_metadata first on screen 1 only
      $tmp_ip_address = NetworkUtils::get_ipaddress();
      $save_answers = $dblink->prepare("INSERT INTO log_metadata VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)");
      $save_answers->bind_param('iisssii', $userid, $paper_id, $_POST['sessionid'], $tmp_ip_address, $student_grade, $student_year, $attempt);
      $save_answers->execute();
      $save_answers->close();
    }
    $save_answers = $dblink->prepare("INSERT INTO log$paper_type VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
    $save_answers->bind_param('isiidisiiss', $userid, $_POST['sessionid'], $paper_id, $log_id, $mark, $totalpos, $saved_response, $screen_no, $tmp_duration, $dismiss, $option_order);
  }
    
  // Use the new structure to look for posted responses.
  $old_type = '';
  $old_score_method = '';
  $correct_rank = true;
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
  foreach ($paper_array as &$row){
    $q_log_id = 0;
    $q_id = $row['q_id'];
    $scenario = $row['scenario'];
    $q_type = $row['q_type'];
    $option_text = $row['option_text'];
    $q_media = $row['q_media'];
    $correct = $row['correct'];
    $score_method = $row['score_method'];
    $marks_correct = $row['marks_correct'];
    $marks_incorrect = $row['marks_incorrect'];
    $marks_partial = $row['marks_partial'];
    $dismiss = '';
    $display_pos = $row['display_pos'];
    if ($old_q_id != $q_id or $display_pos != $old_display_pos) {
      if ($old_type == 'rank') {
        if ($old_score_method == 'Bonus Mark') {
          $totalpos++;
          if ($correct_rank == true and $mark == ($totalpos - 1)) {
            $mark++;  // Add one mark if the user has all options in the correct order
          }
        } elseif ($old_score_method == 'Mark per Question') {
          $totalpos = $old_marks_correct;
          if ($correct_rank) {
            $mark = $old_marks_correct;
          } else {
            $mark = $old_marks_incorrect;
          }
        }
      } elseif ($old_type == 'mrq') {
        if ($old_score_method == 'Mark per Question') {
          if ($correct_rank) {
            $mark = 1;
          } else {
            $mark = 0;
          }
        } elseif ($old_score_method == 'Mark per Option' and $mrq_options_selected > $totalpos) {
          $mark = 0;
        } elseif ($old_score_method == 'other') {
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
      $old_display_pos = $display_pos;
      
      $old_marks_correct = $marks_correct;
      $old_marks_incorrect = $marks_incorrect;
      $old_marks_partial = $marks_partial;

      $correct_rank = true;
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
        // Log format is 'users answer', 'correct answer', 'variable values used'.
        $score_array = explode(',', $display_method);
      
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

        if ($saved_response != '') {
          $saved_response_clean = str_replace(',', '', str_replace(' ', '', $saved_response));
          $variable_array = explode(',',$question_variables);
          $var_no = 1;
          foreach($variable_array as $individual_variable) {
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
          $answer_equation = $correct;
          if ($score_array[2] == 'Formula') {
            // formula answer just do a string comp on the useranswer and the correct one (replace with CAS system later)
            if ($answer_equation != '') {
              eval ("\$answer = \"" . $answer_equation . "\";");
            }
            if ($saved_response_clean == $answer) {
              $mark = $marks_correct;
            }
          } else {
            if ($answer_equation != '') {
              eval ("\$answer = $answer_equation;");
              $answer = round($answer, $score_array[0]);
            }
            if ($saved_response_clean == $answer) {
              $mark = $marks_correct;
            } elseif (abs($saved_response_clean - $answer) <= $score_array[1]) {
              $mark = $marks_correct;
            } else {
              $mark = $marks_incorrect;
            }
          }
        } else {
          $answer = '';
          $mark = 0;
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
          $mark++;
        } else {
          if (($display_method == 'TF_Negative' or $display_method == 'TF_NegativeAbstain' or $display_method == 'YN_NegativeAbstain') and $user_response != 'a' and $user_response != '') {
            $mark--;
          } elseif ($display_method == 'TF_NegativeAbstainHalf' and $user_response != 'a' and $user_response != '') {
            $mark-=0.5;
          }
        }
        $totalpos++;
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
        if(isset($_POST["dismiss$question_no"])) {
          $dismiss = $_POST["dismiss$question_no"];
        } else {
          $dismiss = '';
        }
        $qid = 'q' . $question_no . '_' . $question_part;
        if (isset($_POST[$qid]) AND $_POST[$qid] == 'y') {
          $user_response = 'y';
          $mrq_options_selected++;
        } else {
          $user_response = 'n';
        }
        $saved_response .= $user_response;
        if ($score_method == 'Mark per Option') {
          if ($user_response == 'y' and $correct == 'y') $mark += $marks_correct;
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
          $totalpos = 1;
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
              
              for ($x=0; $x<count($correct_answers); $x++) {
                if ($answer_count == 1) {
                  if ($_POST[$qid] == $correct_answers[0]) {
                    $mark += $marks_correct;
                    $all_items_abstain = false;
                  } elseif ($_POST[$qid] != 'u') {
                    $mark += $marks_incorrect;
                    $all_items_abstain = false;
                  }
                } else {
                  if ($_POST[$qid][$i] == $correct_answers[$x]) $mark += $marks_correct;
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
          foreach ($matching_scenarios as $single_scenario) {
            if (trim($single_scenario) != '') {
              $qid = 'q' . $question_no . '_' . $option_no;
              if (isset($_POST[$qid]) and $_POST[$qid] == $correct_options[$option_no - 1]) {
                $mark += $marks_correct;
              } elseif (isset($_POST[$qid]) and $_POST[$qid] != 'u') {
                $mark += $marks_incorrect;
                $all_items_abstain = false;
              }
              if ($option_no == 1) {
                if(isset($_POST[$qid])) {
                  $saved_response = $_POST[$qid];
                } else {
                  $saved_response = '';
                }
              } else {
                $saved_response .= '|';
                if(isset($_POST[$qid])) $saved_response .= $_POST[$qid];
              }
              $option_no++;
            }
          }
          $totalpos = $option_no - 1;
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
      case 'rank':
        if(isset($_POST["dismiss$question_no"])) {
          $dismiss = $_POST["dismiss$question_no"];
        } else {
          $dismiss = '';
        }
        if ($question_part == 1 and $score_method == 'BonusMark') $totalpos++;
        $qid = 'q' . $question_no . '_' . $question_part;
        if ($saved_response == '') {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response .= ',' . $_POST[$qid];
        }
        if ($score_method == 'Mark per Option') {
          if ($_POST[$qid] == $correct) $mark++;
        } elseif ($score_method == 'Mark per Question') {
          if ($question_part == 1) $totalpos++;
          if ($_POST[$qid] != 'u' and $_POST[$qid] <> $correct) $correct_rank = false;
        } elseif ($score_method == 'Allow partial Marks') {
          if ($_POST[$qid] != 0 and $correct != 0) {
            if ($_POST[$qid] == $correct) {
              $mark++;
            } elseif ($_POST[$qid] == ($correct + 1)) {
              $mark = $mark + 0.5;
            } elseif ($_POST[$qid] == ($correct - 1)) {
              $mark = $mark + 0.5;
            }
          }
        } elseif ($score_method == 'Bonus Mark') {
          if ($_POST[$qid] != 0) {
            if ($correct != 0) $mark++;
            if ($_POST[$qid] <> $correct) $correct_rank = false;
          }
          if ($_POST[$qid] == 0 and $correct != 0) $correct_rank = false;
        }
        if ($correct != 0 and $score_method != 'Mark per Question') {
          $totalpos++;
        } elseif ($score_method == 'Mark per Option') {
          $totalpos++;
        }
        break;
      case 'sct':
        if(isset($_POST["dismiss$question_no"])) {
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
          $mark = $marks_correct;
        }
        if ($totalpos == 0) {
          $totalpos += $marks_correct;
        }
        break;
      case 'timedate':
        $date_details = explode('|',$score_method);
        switch ($date_details[0]) {
        	case '1':		// dd/MM/yyyy hh:mm:ss
	          $qid = 'q' . $question_no . '_day';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_month';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_year';
	          $saved_response .= $_POST[$qid];
	          $qid = 'q' . $question_no . '_hour';
	          $saved_response .= ' ' . $_POST[$qid];
	          $qid = 'q' . $question_no . '_minute';
	          $saved_response .= ':' . $_POST[$qid];
	          $qid = 'q' . $question_no . '_second';
	          $saved_response .= ':' . $_POST[$qid];
	          break;
        	case '2': 	// dd/MM/yyyy hh:mm
	          $qid = 'q' . $question_no . '_day';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_month';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_year';
	          $saved_response .= $_POST[$qid];
	          $qid = 'q' . $question_no . '_hour';
	          $saved_response .= ' ' . $_POST[$qid];
	          $qid = 'q' . $question_no . '_minute';
	          $saved_response .= ':' . $_POST[$qid];
	          break;
        	case '3':		// dd/MM/yyyy
	          $qid = 'q' . $question_no . '_day';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_month';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_year';
	          $saved_response .= $_POST[$qid];
	          break;
        	case '4':		// mm/dd/yyyy
        		$qid = 'q' . $question_no . '_month';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_day';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_year';
	          $saved_response .= $_POST[$qid];
	          break;
        	case '5':		// dd/MMMM/yyyy
	          $qid = 'q' . $question_no . '_day';
	          $saved_response .= $_POST[$qid] . '/';
        		$qid = 'q' . $question_no . '_month';
	          $saved_response .= $_POST[$qid] . '/';
	          $qid = 'q' . $question_no . '_year';
	          $saved_response .= $_POST[$qid];
	          break;
        	case '6':		// hh:mm:ss
	          $qid = 'q' . $question_no . '_hour';
	          $saved_response = $_POST[$qid] . ':';
	          $qid = 'q' . $question_no . '_minute';
	          $saved_response .= $_POST[$qid] . ':';
	          $qid = 'q' . $question_no . '_second';
	          $saved_response .= $_POST[$qid];
	          break;
	        case '7':		// hh:mm (date)
        	case '8':		// hh:mm (duration)
	          $qid = 'q' . $question_no . '_hour';
	          $saved_response = $_POST[$qid] . ':';
	          $qid = 'q' . $question_no . '_minute';
	          $saved_response .= $_POST[$qid];
	         	break;
        }
        if ($saved_response == $correct) {
          $mark++;
        }
        $totalpos++;
        break;
      case 'blank':
        $blank_details = explode("[blank",$option_text);
        $no_answers = count($blank_details) - 1;
       
        $saved_response = '';
        for ($i=1; $i<=$no_answers; $i++) {
          $qid = 'q' . $question_no . '_' . $i;
          if(isset($_POST[$qid])) {
            if ($_POST[$qid] == '') {
              $saved_response .= '|u';
            } else {
              $saved_response .= '|' . $_POST[$qid];
            }
          }
          if (preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$i],$mark_matches)) {
            $totalpos += $mark_matches[1];
            $individual_q_mark = $mark_matches[1];
          } else {
            $totalpos++;
            $individual_q_mark = 1;
          }

          // Get correct answer.
          $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
          $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
          $answer_list = explode(',',$blank_details[$i]);
          $answer_list[0] = str_replace("[/blank]",'',$answer_list[0]);
          if ($score_method == 'textboxes') {
            foreach ($answer_list as $individual_answer) {
              if (isset($_POST[$qid]) and str_replace('&nbsp;',' ',trim($_POST[$qid])) == str_replace('&nbsp;',' ',trim($individual_answer))) $mark += $individual_q_mark;
            }
          } else {
            if (isset($_POST[$qid]) and str_replace('&nbsp;',' ',trim($_POST[$qid])) == str_replace('&nbsp;',' ',($answer_list[0]))) $mark += $individual_q_mark;
          }
        }
        break;
      case 'textbox':
        $qid = 'q' . $question_no;
        if(isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
        } else {
          $saved_response = '';
        }
        $mark = 'NULL';
        $totalpos += $marks_correct;
        break;
      case 'hotspot':
        $qid = 'q' . $question_no;
        $mark = 0;
        if (isset($_POST[$qid])) {
          $saved_response = $_POST[$qid];
          $sub_parts = explode('|',$saved_response);
          foreach ($sub_parts as $sub_part) {
            if ($sub_part{0} == 1) {
              $mark += $marks_correct;
            } else {
              $mark += $marks_incorrect;
            }
            $totalpos += $marks_correct;
          }
        } else {
          $saved_response = '';
        }
        if ($score_method == 'Mark per Question') {   // Override marks if 'Mark per Question' is set.
          if ($mark == $totalpos) {
            $mark = $marks_correct;
          } elseif ($saved_response == 'u') {
            $mark = 0;
          } else {
            $mark = $marks_incorrect;
          }
          $totalpos = $marks_correct;
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
        
        if ($score_method == 'Mark per Question') {   // Override marks if 'Mark per Question' is set.
          if ($mark == $totalpos) {
            $mark = $marks_correct;
          } elseif (strlen($saved_response) < 6) {
            $mark = 0;
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
    if ($correct_rank == true and $mark == ($totalpos - 1) and $old_score_method == 'Bonus Mark') {
      $mark++;  // Add one mark if the user has all options in the correct order
    } elseif ($old_score_method == 'Mark per Question') {
      $totalpos = $old_marks_correct;
      if ($correct_rank) {
        $mark = $old_marks_correct;
      } else {
        $mark = $old_marks_incorrect;
      }
    }
  } elseif ($old_type == 'mrq') {
    if ($old_score_method == 'Mark per Question') {
      if ($correct_rank) {
        $mark = 1;
      } else {
        $mark = 0;
      }
    } elseif ($old_score_method == 'Mark per Option' and $mrq_options_selected > $totalpos) {
      $mark = 0;
    } elseif ($old_score_method == 'other') {
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
?>