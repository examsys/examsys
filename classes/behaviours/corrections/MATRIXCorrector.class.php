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
 * Class for Correction behaviour for Matrix questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class MATRIXCorrector extends Corrector {
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct new correct answer
   * @param integer $paper_id
   */
  public function execute($new_correct, $paper_id, &$prev_changes, $paper_type) {
    $new_correct_val = $new_correct['option_correct'];
    $errors = array();
    $changes = true;

    $first = reset($this->_question->options);
    $old_correct = $first->get_all_corrects();
    $mark_correct = $first->get_marks_correct();
    $mark_incorrect = $first->get_marks_incorrect();
    $correct_count = 0;
    $data = array();

    for ($i = 0; $i < $this->_question->max_stems; $i++) {
      $data['option_correct' . strval($i + 1)] = $new_correct_val[$i];
      $correct_count += ($new_correct_val[$i] > 0) ? 1 : 0;
      if (count($new_correct_val[$i]) > 0 and $new_correct_val[$i] != $old_correct[$i]) {
        $changes = true;
      }
    }


    if ($prev_changes or $changes) {
      if ($changes) {
        $prev_changes = $changes;
        $opt_ids = array_keys($this->_question->options);
        $existing = array();
        for ($option_no = 1; $option_no <= count($this->_question->options); $option_no++) {
          $option = $this->_question->options[$opt_ids[$option_no - 1]];
          $option->populate_compound(array('correct'), $data, $existing, 'option_', $this->_lang_strings['postexamchange']);
        }
      }

      try {
    	  if (!$this->_question->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          // Remark the student's answers in 'log{$paper_type}'.
          $score_method = $this->_question->get_score_method();

          $totalpos = ($score_method == 'Mark per Question') ? $mark_correct : $mark_correct * $correct_count;

    	    $result = $this->_mysqli->prepare("SELECT l.user_answer, l.id FROM log{$paper_type} l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE l.q_id = ? AND lm.paperID = ?");
          $result->bind_param('ii', $this->_question->id, $paper_id);
          $result->execute();
          $result->store_result();
          $result->bind_result($user_answer, $id);
          while ($result->fetch()) {
            $big_user_parts = explode('|',$user_answer);
            $mark = 0;
            $all_correct = true;

            for ($i=0; $i < $correct_count; $i++) {
              if (isset($big_user_parts[$i]) and $big_user_parts[$i] != '' and $big_user_parts[$i] != 'u') {
                if ($score_method == 'Mark per Option') {
                  $mark += ($new_correct_val[$i] == $big_user_parts[$i]) ? $mark_correct : $mark_incorrect;
                } elseif ($new_correct_val[$i] != $big_user_parts[$i]) {
                  $all_correct = false;
                }
              } else {
                $all_correct = false;
              }
            }

            if ($score_method == 'Mark per Question') {
              if ($all_correct) {
                $mark = $mark_correct;
              } else {
                $mark = $mark_incorrect;
              }
            }

            $updateLog = $this->_mysqli->prepare("UPDATE log{$paper_type} SET mark = ?, totalpos = ? WHERE id = ?");
            $updateLog->bind_param('dii', $mark, $totalpos, $id);
            $updateLog->execute();
            $updateLog->close();
          }
          $result->free_result();
          $result->close();
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}

      if (count($errors) == 0) {
        $this->invalidate_paper_cache($paper_id);
      }
    }

    return $errors;
  }
}
