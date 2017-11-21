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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

/**
 * This is function time_to_seconds
 *
 * @param mixed $seconds time in seconds
 *
 * @return mixed time in hours minutes and seconds
 *
 */
function time_to_seconds($seconds) {
  $hr = intval(substr($seconds, 8, 2));
  $min = intval(substr($seconds, 10, 2));
  $sec = intval(substr($seconds, 12, 2));

  return ($hr * 3600) + ($min * 60) + $sec;
}

/**
 * This is function returns an array of database id q_id mappings for a user on a paper
 * and locks the rows for update
 *
 * @param int $u_id rogo user id
 * @param int $p_id rogo paper id
 * @param mixed $paper_log_type the current type of the paper 1,2,3,4,5,6 or _late
 * @param int $sreen_no the screen of interest
 * @param string $metadataID the id of the session of interest
 * @param MySQLi $db an open database connection
 *
 * @return array of database id q_id mappings
 *
 */
function get_log_ids_and_lock($paper_log_type, $screen_no, $metadataID, $db) {
  $log_ids = array();
  $log_check = $db->prepare("SELECT id, q_id FROM log$paper_log_type WHERE metadataID = ? AND screen = ? FOR UPDATE");
  $log_check->bind_param('ii', $metadataID, $screen_no);
  $log_check->execute();
  $log_check->bind_result($tmp_id, $tmp_q_id);
  while ($log_check->fetch()) {
    $log_ids[$tmp_q_id] = $tmp_id;
  }
  $log_check->close();

  return $log_ids;
}

/**
 * Save the user's answers to the database, inserting or updating as necessary
 *
 * @param  integer $paper_type    Type of paper
 * @param  integer $metadataID    ID of user's record for this paper in metadata table
 * @param  integer $screen_no     Number of current screen
 * @param  array $response_data User's reponses and associated data for current screen
 * @param  mysqli $db            Reference to DB connection
 *
 * @return boolean                Did the save operation succeed?
 */
function save_user_responses($paper_type, $metadataID, $screen_no, $response_data, $paper_id, $db) {
  $userObject = UserObject::get_instance();
  $save_ok = true;

  // Turn off auto commit and start transaction. If PHP exits or we call rollback the inserts/updates will be rolledback.
  $db->autocommit(false);

  $log_ids = get_log_ids_and_lock($paper_type, $screen_no, $metadataID, $db);

  foreach ($response_data as $response) {
    if (count($log_ids) > 0) {
      $log_id = $log_ids[$response['q_id']];
      // prepare to update old records
      $save_answers = $db->prepare("UPDATE log$paper_type SET mark = ?, adjmark = ?, user_answer = ?, duration = ?, updated = NOW(), dismiss = ? WHERE id = ?");
      $save_answers->bind_param('ddsisi', $response['mark'], $response['mark'], $response['saved_response'], $response['tmp_duration'], $response['dismiss'], $log_id);
    } else {
      $save_answers = $db->prepare("INSERT INTO log$paper_type VALUES (NULL, ?, ?, ?, ?, ?, '0', ?, ?, NOW(), ?, ?, ?) ON DUPLICATE KEY UPDATE mark = ?, user_answer = ?, duration = ?, updated = NOW(), dismiss = ? ");
      $save_answers->bind_param('iddisiissidsis', $response['q_id'], $response['mark'], $response['mark'], $response['totalpos'], $response['saved_response'], $response['screen_no'], $response['tmp_duration'], $response['dismiss'], $response['option_order'], $metadataID, $response['mark'], $response['saved_response'], $response['tmp_duration'], $response['dismiss']);
    }
    $res = $save_answers->execute(); // Save to the database.
    //if ($res === false or $save_answers->affected_rows == 0) {
    if ($res === false) {
      $save_ok = false;
      break;
    }
  }
  
  // Check for any save fails.
  if ($paper_type == 2) {   // Only log fails on summative exams.
    $ipaddress = NetworkUtils::get_client_address();
    $save_failed = param::optional('save_failed', '', param::RAW, param::FETCH_POST);
    if ($save_failed != '') {
      $failures = explode('<br />', nl2br($save_failed));
      foreach ($failures as $failure) {
        $parts = explode('-', $failure);
        $stmt = $db->prepare("INSERT INTO save_fail_log VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiisisss', $userObject->get_user_ID(), $paper_id, $screen_no, $ipaddress, $parts[0], $parts[1], $parts[2], $parts[3]);
        $stmt->execute();
        $stmt->close();
      }
    }
  }  

  //
  // Did the all the save to log operations succeed?
  //
  if ($save_ok === false) {
    $db->rollback();			// NO - rollback
  } else {    
    $db->commit();				// YES - commit the updates to the log tables
  }
  // Turn auto commit back on so future queries function as before
  $db->autocommit(true);

  // Close prepared queries
  if (count($response_data) > 0) { 		// Could happen on a screen with just an info block
    $save_answers->close();
  }

  return $save_ok;
}

/**
 * This is function record_marks, is used to mark the users answer, and record it in the database for all question types.
 *
 * @param mixed $paper_id
 * @param mixed $screen_no
 * @param mixed $dblink
 * @param mixed $paper_type T
 * @param mixed $metadataID
 * @throws MissingParameter
 */
function record_marks($paper_id, $db, $paper_type, $metadataID, $preview_q_id = null) {
  global $original_paper_type; //TODO: move this into the function definition
  $screen_no = param::required('old_screen', param::INT, param::FETCH_POST);
  $extra_duration = param::required('previous_duration', param::INT, param::FETCH_POST);

  // Load the structure of the paper into a local array.
  $paper_array = array();
  $tmp_question_no = 0;
  $old_q_id = 0;
  $old_display_pos = 0;
  $old_q_type = '';
  $option_order = '';
  $random_on = false;

  if (isset($preview_q_id)) {
    $questions = $db->prepare("SELECT q_id, scenario, q_type, option_text, q_media, correct, display_method, settings, score_method, marks_correct, marks_incorrect, marks_partial, display_pos FROM papers, questions LEFT JOIN options ON questions.q_id=options.o_id WHERE paper=? AND questions.q_id=? AND papers.question=questions.q_id ORDER BY display_pos, id_num");
    $questions->bind_param('ii', $paper_id, $preview_q_id);
  } else {
    $questions = $db->prepare("SELECT q_id, scenario, q_type, option_text, q_media, correct, display_method, settings, score_method, marks_correct, marks_incorrect, marks_partial, display_pos FROM papers, questions LEFT JOIN options ON questions.q_id=options.o_id WHERE paper=? AND screen=? AND papers.question=questions.q_id  ORDER BY display_pos, id_num");
    $questions->bind_param('ii', $paper_id, $screen_no);
  }
  $questions->execute();
  $questions->store_result();
  $questions->bind_result($q_id, $scenario, $q_type, $option_text, $q_media, $correct, $display_method, $settings, $score_method, $marks_correct, $marks_incorrect, $marks_partial, $display_pos);
  while ($questions->fetch()) {
    if ($old_q_id != $q_id or $old_display_pos != $display_pos) {
      if ($q_type != 'info') $tmp_question_no++;
      $selected_q_id = array();
      $random_on = false;
    }
    if ($q_type == 'random' or $q_type == 'keyword_based') {
      $selected_q_id[] = param::optional('q' . $tmp_question_no . '_randomID', 0, param::INT, param::FETCH_POST);

      if ($random_on == false and count($selected_q_id) > 0) {
        foreach ($selected_q_id as &$used_qid) {

          $random_data = $db->prepare("SELECT q_id, scenario, q_type, option_text, q_media, correct, display_method, settings, "
              . "score_method, marks_correct, marks_incorrect, marks_partial "
              . "FROM (questions) LEFT JOIN options ON questions.q_id = options.o_id WHERE q_id = ? ORDER BY id_num");

          $random_data->bind_param('i', $used_qid);
          $random_data->execute();
          $random_data->store_result();
          $random_data->bind_result($new_q_id, $scenario, $q_type, $option_text, $q_media, $correct, $display_method, $settings,
            $score_method, $marks_correct, $marks_incorrect, $marks_partial);
          if ($q_type == 'enhancedcalc') {
            if (!is_array($settings)) {
                $settings = json_decode($settings, true);
            }
            $marks_correct = $settings['marks_correct'];
            $marks_incorrect = $settings['marks_incorrect'];
            $marks_partial = $settings['marks_partial'];
          }
          while ($random_data->fetch()) {
            $paper_array[] = array('q_id' => $new_q_id, 'scenario' => $scenario, 'q_type' => $q_type, 'option_text' => $option_text,
                'q_media' => $q_media, 'correct' => $correct, 'display_method' => $display_method, 'settings' => $settings,
                'score_method' => $score_method, 'marks_correct' => $marks_correct, 'marks_incorrect' => $marks_incorrect,
                'marks_partial' => $marks_partial, 'question_no' => $tmp_question_no, 'display_pos' => $display_pos);
          }
          /*
          if ($random_data->num_rows == 0) {
            $ex = new RandomQuestionNotFound(sprintf('Random question ID %s not found in paper %s', $used_qid, $paper_id));
            trigger_error($ex->getMessage(), E_USER_WARNING);
            throw($ex);
          }
           * 
           */
          $random_data->close();

        }
        $selected_q_id = array();
        $random_on = true;
      }
    } else {
      $paper_array[] = array('q_id' => $q_id, 'scenario' => $scenario, 'q_type' => $q_type, 'option_text' => $option_text, 'q_media' => $q_media, 'correct' => $correct, 'display_method' => $display_method, 'settings' => $settings, 'score_method' => $score_method, 'marks_correct' => $marks_correct, 'marks_incorrect' => $marks_incorrect, 'marks_partial' => $marks_partial, 'question_no' => $tmp_question_no, 'display_pos' => $display_pos);
    }
    $old_q_type = $q_type;
    $old_q_id = $q_id;
    $old_display_pos = $display_pos;
  }
  $questions->close();

  // Use the new structure to look for posted responses.
  $old_type = '';
  $old_score_method = '';
  $old_display_method = '';
  $old_settings = '';
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

  // Record posted data for saving later
  $response_data = array();

  foreach ($paper_array as &$row) {
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
    $settings = $row['settings'];

    $display_pos = $row['display_pos'];
    if ($old_q_id != $q_id or $display_pos != $old_display_pos) {
      $all_items_abstain = true;
      if ($old_type == 'rank') {
        finalise_rank($old_score_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $saved_response, $totalpos, $mark);
      } elseif ($old_type == 'mrq') {
        finalise_mrq($old_score_method, $old_display_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $question_no, $mrq_options_selected, $totalpos, $mark, $question_part, $qid, $saved_response);
      } elseif ($old_type == 'dichotomous') {
        finalise_dichotomous($old_score_method, $old_marks_correct, $old_marks_incorrect, $no_correct, $no_incorrect, $saved_response, $totalpos, $mark);
      }
      $option_order = param::optional("order$question_no", '', param::TEXT, param::FETCH_POST);
      if ($question_no > 0 and $old_type != 'info') {
        $tmp_duration = time_to_seconds($submit_time) - time_to_seconds($_POST['page_start']);
        if ($tmp_duration < 0) $tmp_duration += 86400;
        $tmp_duration += $extra_duration;

        $response_data[] = array('q_id' => $log_id, 'mark' => $mark, 'totalpos' => $totalpos, 'saved_response' => $saved_response, 'screen_no' => $screen_no, 'tmp_duration' => $tmp_duration, 'dismiss' => $dismiss, 'option_order' => $option_order);

        $saved_response = '';
        $dismiss = '';
      }
      if ($q_type != 'info') $question_no++;
      $question_part = 0;
      $old_q_id = $q_id;
      $old_type = $q_type;
      $old_score_method = $score_method;
      $old_display_method = $display_method;
      $old_settings = $settings;
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
    $option_order = param::optional("order$question_no", '', param::TEXT, param::FETCH_POST);
    if ($option_order !== '') {
      $option_order = explode(',', $option_order);
    }
    $question_part++;

    switch ($q_type) {
      case 'area':
        $mark = 0;
        $qid = 'q' . $question_no;
        $saved_response = param::optional($qid, '', param::TEXT, param::FETCH_POST);
        if ($saved_response !== '') {
          $subparts = explode(';', $saved_response);

          $parts = explode(',', $saved_response);
          $tolerances = json_decode($old_settings, true);
          if ($parts[1] >= $tolerances['correct_full'] and $parts[2] <= $tolerances['error_full']) {
            $mark = $marks_correct;
          } elseif ($parts[1] >= $tolerances['correct_partial'] and $parts[2] <= $tolerances['error_partial']) {
            $mark = $marks_partial;
          } elseif (isset($subparts[1]) and strlen($subparts[1]) > 0) {
            $mark = $marks_incorrect;
          }
        }
        $totalpos = $marks_correct;

        break;
      case 'enhancedcalc':
        //DO NOTHING EXCEPT SAVE DATA
        require_once('../plugins/questions/enhancedcalc/enhancedcalc.class.php');
        $mark = null;

        if (!is_array($settings)) {
          $settings = json_decode($settings, true);
        }
        
        if (isset($settings['marks_correct'])) {
          $totalpos = $settings['marks_correct'];
        } else {
          $totalpos = null;
        }
        $calcanswers = param::required('qid', param::RAW, param::FETCH_POST);
        if (is_array($calcanswers[$log_id])) {
          $saved_response = EnhancedCalc::process_user_answer($calcanswers[$log_id], $_SESSION['qid'][$log_id]);
        }
        break;
      case 'dichotomous':
        $qid = 'q' . $question_no . '_' . $question_part;
        $user_response = param::optional($qid, '', param::ALPHA, param::FETCH_POST);
        if ($user_response == '') {
          $saved_response .= 'u';
        } else {
          $saved_response .= $user_response;
        }
        if ($user_response == $correct) {
          $mark += $marks_correct;
          $no_correct++;
        } elseif ($user_response != '' and $user_response != 'a') {
          $mark += $marks_incorrect;
          $no_incorrect++;
        }
        $totalpos += $marks_correct;
        break;
      case 'likert':
        $qid = 'q' . $question_no . '_' . $question_part;
        $user_response = param::optional($qid, '', param::TEXT, param::FETCH_POST);

        if ($user_response == '') {
          $saved_response .= 'u';
        } else {
          $saved_response .= $user_response;
        }
        break;
      case 'mcq':
        $dismiss = param::optional("dismiss$question_no", '', param::TEXT, param::FETCH_POST);
        $qid = 'q' . $question_no;
        $saved_response = param::optional($qid, '', param::TEXT, param::FETCH_POST);
        if ($saved_response == 'other') { // Survey use - 'other' textbox.
          $qid = 'q' . $question_no . '_other';
          $saved_response = 'other:' . param::optional($qid, '', param::TEXT, param::FETCH_POST);
        }
        if ($saved_response == '') $saved_response = '0';
        if ($totalpos == 0) {
				  if ($saved_response == 'a') {
						$mark += 0;  // Abstain
          } elseif ($saved_response == $correct) {
            $mark += $marks_correct;
          } elseif ($saved_response != '0') {
            $mark += $marks_incorrect;
          }
          $totalpos += $marks_correct;
        }
        break;
      case 'mrq':
        $dismiss = param::optional("dismiss$question_no", '', param::TEXT, param::FETCH_POST);
        $qid = 'q' . $question_no . '_' . $question_part;
        $user_response = param::optional($qid, 'n', param::ALPHA, param::FETCH_POST);
        if ($user_response === 'y') {
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
            $correct_options = explode('|', $scenario);
          } else {
            $correct_options = explode('|', $correct);
          }

          $matching_scenarios = explode('|', $scenario);
          $text_scenarios = 0;
          for ($part_id = 0; $part_id < 10; $part_id++) {
            if (isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id])) != '') $text_scenarios++;
          }

          $matching_media = explode('|', $q_media);
          $media_scenarios = 0;
          for ($part_id = 1; $part_id <= 10; $part_id++) {
            if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') $media_scenarios++;
          }
          $part_no = max($text_scenarios, $media_scenarios);

          $option_no = 1;
          for ($scenario_no = 0; $scenario_no < $part_no; $scenario_no++) {
            if ($option_no > 1) {
              $saved_response .= '|';
            }
            $qid = 'q' . $question_no . '_' . $option_no;
            if (isset($correct_options[$option_no - 1])) {
                $correct_answers = explode('$', $correct_options[$option_no - 1]);
            } else {
                $correct_answers = array();
            }
            $execmatch_answers = param::optional($qid, null, param::ALPHANUM, param::FETCH_POST);
            if (!is_null($execmatch_answers)) {
              $answer_count = count($execmatch_answers);
              if ($score_method == 'Mark per Question' and (is_array($execmatch_answers) or $execmatch_answers != 'u')) {
                $all_items_abstain = false;
              }
            } else {
              $answer_count = 0;
            }

            for ($i = 0; $i < $answer_count; $i++) {
              if ($i == 0) {
                if (count($correct_answers) == 1) {
                  $saved_response .= $execmatch_answers;
                } else {
                  $saved_response .= $execmatch_answers[$i];
                }
              } else {
                $saved_response .= '$' . $execmatch_answers[$i];
              }

              if ($answer_count == 1) {
                if ($execmatch_answers == $correct_answers[0]) {
                  $mark += $marks_correct;
                  $all_items_abstain = false;
                } elseif ($execmatch_answers != 'u') {
                  $mark += $marks_incorrect;
                  $all_items_abstain = false;
                }
              } else {
                if (in_array($execmatch_answers[$i], $correct_answers)) {
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

        if ($score_method == 'Mark per Question') { // Override marks if 'Mark per Question' is set.
          if ($mark == $totalpos) {
            $mark = $marks_correct;
          } elseif ($all_items_abstain) { // Unanswered
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
          $correct_options = explode('|', $correct);
          $matching_scenarios = explode('|', $scenario);
          $option_no = 1;
          $no_correct = 0;
          $no_incorrect = 0;
          $question_parts = 0;
          foreach ($matching_scenarios as $single_scenario) {
            if (trim($single_scenario) != '') {
              $question_parts++;
              $qid = 'q' . $question_no . '_' . $option_no;
              $matrix_option_answer = param::optional($qid, null, param::ALPHANUM, param::FETCH_POST);
              if ($matrix_option_answer == $correct_options[$option_no - 1]) {
                $mark += $marks_correct;
                $no_correct++;
              } elseif (!is_null($matrix_option_answer) and $matrix_option_answer != 'u') {
                $mark += $marks_incorrect;
                $no_incorrect++;
              }
              if ($option_no == 1) {
                if (!is_null($matrix_option_answer)) {
                  $saved_response = $matrix_option_answer;
                } else {
                  $saved_response = '';
                }
              } else {
                $saved_response .= '|';
                if (!is_null($matrix_option_answer)) {
                  $saved_response .= $matrix_option_answer;
                }
              }
              $option_no++;
            }
          }
          $totalpos = $option_no - 1;
        }

        if ($score_method == 'Mark per Question') { // Override marks if 'Mark per Question' is set.
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
        $dismiss = param::optional("dismiss$question_no", '', param::TEXT, param::FETCH_POST);
        $qid = 'q' . $question_no . '_' . $question_part;
        $part_answer = param::optional($qid, 'u', param::ALPHANUM, param::FETCH_POST);
        if ($saved_response == '') {
          $saved_response = $part_answer;
        } else {
          $saved_response .= ',' . $part_answer;
        }
        if ($score_method == 'Mark per Option') {
          if ($part_answer != 'u') {
            $mark += ($part_answer == $correct) ? $marks_correct : $marks_incorrect;
          }
        } elseif ($score_method == 'Mark per Question') {
          if ($part_answer <> $correct) $correct_rank = false;
        } elseif ($score_method == 'Allow partial Marks') {
          if ($part_answer != 'u') {
            if ($part_answer != 0) {
              if ($correct != 0) {
                if ($part_answer == $correct) {
                  $mark += $marks_correct;
                } elseif ($part_answer == ($correct + 1)) {
                  $mark += $marks_partial;
                } elseif ($part_answer == ($correct - 1)) {
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
          // Part answer is not N/A
          if ($part_answer != 0) {
            // Mark if correct answer is not N/A
            $mark += ($correct != 0) ? $marks_correct : $marks_incorrect;
            // Do not assign bonus mark if part answer is not correct or absention.
            if (($part_answer <> $correct) or $part_answer == 'u') $correct_rank = false;
          }
          // Do not assign bonus mark if part answer is not correct or absention.
          if (($part_answer == 0 and $correct != 0) or $part_answer == 'u') $correct_rank = false;
        }
        if ($score_method == 'Mark per Question') {
          $totalpos = $marks_correct;
        } elseif ($correct != 0 or $score_method == 'Mark per Option') {
          $totalpos += $marks_correct;
        }
        break;
      case 'sct':
        $dismiss = param::optional("dismiss$question_no", '', param::TEXT, param::FETCH_POST);
        $qid = 'q' . $question_no;
        $saved_response = param::optional($qid, 0, param::INT, param::FETCH_POST);

        if ($saved_response == $question_part) {
          $max = 0;
          foreach ($paper_array as $tmp_row) {
            if ($tmp_row['q_id'] == $row['q_id'] and $tmp_row['correct'] > $max) $max = $tmp_row['correct'];
          }
          if ($max > 0) {
            $mark = $row['correct'] / $max;
          }
        }
        $totalpos = 1;  // SCT questions are always out of one.
        break;
      case 'true_false':
        $dismiss = '';

        $qid = 'q' . $question_no;
        $saved_response = param::optional($qid, 'u', param::ALPHA, param::FETCH_POST);
        if ($saved_response == '') $saved_response = 'u';

        if ($saved_response == $correct) {
          $mark += $marks_correct;
        } elseif ($saved_response != 'u' and $saved_response != 'a') {
          $mark += $marks_incorrect;
        }
        $totalpos += $marks_correct;

        break;
      case 'blank':
        $blank_details = explode("[blank", $option_text);
        $no_answers = count($blank_details) - 1;
        $have_answer = false;

        $saved_response = array();
        for ($i = 1; $i <= $no_answers; $i++) {
          $qid = 'q' . $question_no . '_' . $i;
          // If the parameter is not sent for some reason assume the part is unanswered.
          $blank_answer = param::optional($qid, 'u', param::TEXT, param::FETCH_POST);
          if (trim($blank_answer) === '') {
            // Assume an answer composed only of spaces is unanswered.
            $saved_response[] = 'u';
          } else {
            // Encode commas.
            $blank_answer = str_replace(',', '&#44;', $blank_answer);
            $saved_response[] = $blank_answer;
          }

          if (preg_match("|mark=\"([0-9]{1,3})\"|", $blank_details[$i], $mark_matches)) {
            $totalpos += $mark_matches[1];
            $individual_q_mark = $mark_matches[1];
          } else {
            $totalpos += $marks_correct;
            $individual_q_mark = $marks_correct;
          }

          // Get correct answer.
          $blank_details[$i] = substr($blank_details[$i], (strpos($blank_details[$i], ']') + 1));
          $blank_details[$i] = substr($blank_details[$i], 0, strpos($blank_details[$i], '[/blank]'));
          $answer_list = explode(',', $blank_details[$i]);

          $answer_list[0] = str_replace("[/blank]", '', $answer_list[0]);
          // Ensure that the answers are filtered to the same level as the user's answers.
          // This will ensure that they can match correctly.
          $answer_list = param::clean_array($answer_list, param::TEXT);
          if ($display_method == 'textboxes') {
            if (!is_null($blank_answer) and $blank_answer != 'u' and $blank_answer != '') {
              $have_answer = true;
              $is_correct = false;
              foreach ($answer_list as $individual_answer) {
                if (str_replace('&nbsp;', ' ', trim(strtolower($blank_answer))) == str_replace('&nbsp;', ' ', trim(strtolower($individual_answer)))) {
                  $is_correct = true;
                  break;
                }
              }
              $mark += ($is_correct) ? $individual_q_mark : $marks_incorrect;
            }
          } else {
            if (!is_null($blank_answer) and $blank_answer != 'u') {
              $have_answer = true;
              $mark += (str_replace('&nbsp;', ' ', trim($blank_answer)) == str_replace('&nbsp;', ' ', trim($answer_list[0]))) ? $individual_q_mark : $marks_incorrect;
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
        $saved_response = json_encode($saved_response);
        break;
      case 'textbox':
        $qid = 'q' . $question_no;
        if (!is_array($settings)) {
          $settings = json_decode($settings, true);
        }
        if (!isset($settings['editor']) or $settings['editor'] == 'plain') {
          $saved_response = htmlspecialchars(param::optional($qid, '', param::RAW, param::FETCH_POST));
        } else {
          $saved_response = param::optional($qid, '', param::HTML, param::FETCH_POST);
        }
        $mark = null;
        $totalpos = $marks_correct;
        break;
      case 'hotspot':
        $qid = 'q' . $question_no;
        $mark = 0;
        $all_correct = true;
        $hotspot_answer = param::optional($qid, null, param::TEXT, param::FETCH_POST);
        if (!is_null($hotspot_answer)) {
          $saved_response = $hotspot_answer;
          $sub_parts = explode('|', $saved_response);
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
        if ($score_method == 'Mark per Question') { // Override marks if 'Mark per Question' is set.
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
        // We cannot clean it fully in one go.
        $saved_response = param::optional($qid, '', param::RAW, param::FETCH_POST);
        if ($saved_response != '') {
          $tmp_first_split = explode(';', $saved_response);
          // Clean the mark and total number of items.
          $tmp_second_split = param::clean_array(explode('$', $tmp_first_split[0]), param::INT);
          if ($tmp_first_split[1] != '') {
            $all_items_abstain = false;
          }
          $mark += $tmp_second_split[0];
          $totalpos += $tmp_second_split[1];
        }

        if ($all_items_abstain) {
          $mark = 0;
        } elseif ($score_method == 'Mark per Question') { // Override marks if 'Mark per Question' is set.
          if ($mark == $totalpos) {
            $mark = $marks_correct;
          } else {
            $mark = $marks_incorrect;
          }
          $totalpos = $marks_correct;
        }
        // Ensure all values are correct and recombine into a final response.
        $user_answer = param::clean_array(explode('$', $tmp_first_split[1]), param::TEXT);
        $saved_response = implode(
            ';',
            array(
              implode('$', $tmp_second_split),
              implode('$', $user_answer),
            )
          );

        break;
      case 'flash':
        $qid = 'q' . $question_no;
        $saved_response = param::optional($qid, '', param::TEXT, param::FETCH_POST);
        $flash_args = explode(',', $saved_response);
        $mark = $flash_args[0]; // Get student marks from Flash.
        $totalpos += $marks_correct;
        break;
    }
  } // End of while loop.

  // Log the very last question block.
  if ($old_type == 'rank') {
    finalise_rank($old_score_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $saved_response, $totalpos, $mark);
  } elseif ($old_type == 'mrq') {
    finalise_mrq($old_score_method, $old_display_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $question_no, $mrq_options_selected, $totalpos, $mark, $question_part, $qid, $saved_response);
  } elseif ($old_type == 'dichotomous') {
    finalise_dichotomous($old_score_method, $old_marks_correct, $old_marks_incorrect, $no_correct, $no_incorrect, $saved_response, $totalpos, $mark);
  }
  $option_order = param::optional("order$question_no", '', param::TEXT, param::FETCH_POST);
  if ($question_no > 0 and $q_type != 'info') {
    $page_start = param::required('page_start', param::ALPHANUM, param::FETCH_POST);
    $tmp_duration = time_to_seconds($submit_time) - time_to_seconds($page_start);
    if ($tmp_duration < 0) $tmp_duration += 86400;
    $tmp_duration += $extra_duration;

    $response_data[] = array('q_id' => $log_id, 'mark' => $mark, 'totalpos' => $totalpos, 'saved_response' => $saved_response, 'screen_no' => $screen_no, 'tmp_duration' => $tmp_duration, 'dismiss' => $dismiss, 'option_order' => $option_order);

    $saved_response = '';
    $dismiss = '';
  }

  // Save the results to the database
  $save_ok = save_user_responses($paper_type, $metadataID, $screen_no, $response_data, $paper_id, $db);

  return $save_ok;

}

/**
 * Check if any of the variables used in a formula evaluates to ERROR (a student answer from a previous question that
 * hasn't been answered), in which case it cannot be processed
 *
 * @param $formula Formula to be evaluated
 * @param $variables Array of variable values
 *
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
      $mark += $old_marks_correct; // Add one mark if the user has all options in the correct order
    }
  } elseif ($old_score_method == 'Mark per Question') {
    $totalpos = $old_marks_correct;
    // If question is unanswered, $saved_response will be a comma separated list of 'u' so check for other characters
    $answered = preg_match('/[^u,]/', $saved_response);
    if ($correct_rank) {
      $mark = ($answered > 0) ? $old_marks_correct : 0;
    } else {
      $mark = ($answered > 0) ? $old_marks_incorrect : 0;
    }
  }
}

function finalise_mrq($old_score_method, $old_display_method, $correct_rank, $old_marks_correct, $old_marks_incorrect, $question_no, $mrq_options_selected, $totalpos, &$mark, &$question_part, &$qid, &$saved_response) {
  $abstained = param::optional('q' . $question_no . '_abstain', null, param::ALPHA, param::FETCH_POST);
  if (!is_null($abstained)) {
    $mark = 0;
    $saved_response = 'a';
  } elseif ($old_score_method == 'Mark per Question') {
    if ($correct_rank) {
      $mark = $old_marks_correct;
    } else {
      $mark = $old_marks_incorrect;
    }
  } elseif ($old_score_method == 'Mark per Option' and ($mrq_options_selected * $old_marks_correct) > $totalpos) {
    $mark = 0;
  }
  if ($old_display_method == 'other') {
    $question_part++;
    $qid = 'q' . $question_no . '_' . $question_part;
    $next_response = param::optional($qid, null, param::ALPHA, param::FETCH_POST);
    if ($next_response == 'y') {
      $saved_response .= 'y';
      $qid = 'q' . $question_no . '_other';
      $saved_response .= param::optional($qid, null, param::TEXT, param::FETCH_POST);
    } else {
      $saved_response .= 'n';
    }
  }
}

function finalise_dichotomous($old_score_method, $old_marks_correct, $old_marks_incorrect, $no_correct, $no_incorrect, $saved_response, &$totalpos, &$mark) {
  if ($old_score_method == 'Mark per Question') {
    $totalpos = $old_marks_correct;
    if ($no_correct == 0 and $no_incorrect == 0) { // Abstained to all options
      $mark = 0;
    } elseif ($no_correct == strlen($saved_response)) {
      $mark = $old_marks_correct;
    } else {
      $mark = $old_marks_incorrect;
    }
  }

}

?>
