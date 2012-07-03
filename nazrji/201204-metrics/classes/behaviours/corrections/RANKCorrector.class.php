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
 * Class for Correction behaviour for Ranking questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class RANKCorrector extends Corrector {
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct new correct answer
   * @param integer $paper_id
   */
  public function execute($new_correct, $paper_id, &$changes, $paper_type) {
    $new_correct_val = $new_correct['option_correct'];
    $errors = array();

    $i = 0;
    foreach ($this->_question->options as $option) {
      if ($i == 0) {
        $mark_correct = $option->get_marks_correct();
        $mark_incorrect = $option->get_marks_incorrect();
        $mark_partial = $option->get_marks_partial();
      }
      if ($new_correct_val[$i] != $option->get_correct()) {
        $option->set_correct($new_correct_val[$i]);
        $changes = true;

        $opt_no = $i + 1;
//        $this->_question->add_unified_field_modification('correct', "Correct Option $opt_no", $option->get_correct(), $new_correct_val[$i], $this->_lang_strings['postexamchange']);
      }
      $i++;
    }

    if ($changes) {
      try {
    	  if(!$this->_question->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          // Remark the student's answers in 'log{$paper_type}'.
          $score_method = $this->_question->get_score_method();
          $correct_rank = true;


    	    $result = $this->_mysqli->prepare("SELECT DISTINCT user_answer FROM log{$paper_type} WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->_question->id, $paper_id);
          $result->execute();
          $result->store_result();
          $result->bind_result($user_answer);
          while ($row = $result->fetch()) {
            $user_answers = explode(',', $user_answer);
            $totalpos = 0;
            $mark = 0;
            $order_correct = true;

            for ($i=0; $i < count($new_correct_val); $i++) {
              if (!$this->_question->is_answer_blank($new_correct_val[$i]) or ($score_method != 'Bonus Mark' and $score_method != 'Allow partial Marks')) $totalpos += $mark_correct;

              if ($user_answers[$i] != 'u') {
                if (!$this->_question->is_answer_blank($user_answers[$i]) and !$this->_question->is_answer_blank($new_correct_val[$i])) {
                  if ($new_correct_val[$i] == $user_answers[$i]) {
                    $mark += $mark_correct;
                  } elseif ($score_method == 'Bonus Mark') {
                    $mark += $mark_correct;
                    $order_correct = false;
                  } elseif ($score_method == 'Allow partial Marks' and abs($new_correct_val[$i] - $user_answers[$i]) == 1) {
                    $mark += $mark_partial;
                  } else {
                    $mark += $mark_incorrect;
                  }
                } elseif ($this->_question->is_answer_blank($user_answers[$i]) and $this->_question->is_answer_blank($new_correct_val[$i])) {
                  if ($score_method != 'Bonus Mark' and $score_method != 'Allow partial Marks') {
                    $mark += $mark_correct;
                  }
                } else {
                  $mark += $mark_incorrect;
                  $order_correct = false;
                }
              } else {
                $order_correct = false;
              }
            }

            // Recalculate total possible marks if 'all correct' or 'bonus mark'.
            if ($score_method == 'Mark per Question') {
              $mark = ($mark == $totalpos) ? $mark_correct : $mark_incorrect;
              $totalpos = $mark_correct;
            } elseif ($score_method == 'Bonus Mark') {
              $totalpos += $mark_correct;
              $mark = ($order_correct) ? $totalpos : $mark;
            }

            $updateLog = $this->_mysqli->prepare("UPDATE log{$paper_type} SET mark=?, totalpos=? WHERE user_answer=? AND q_id=? AND q_paper=?");
            $updateLog->bind_param('disii', $mark, $totalpos, $user_answer, $this->_question->id, $paper_id);
            $updateLog->execute();
            $updateLog->close();
          }
          $result->free_result();
          $result->close();
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
    }

    return $errors;
  }
}
