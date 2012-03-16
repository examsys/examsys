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
 * Class for Correction behaviour for Multiple Response questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class MRQCorrector extends Corrector {
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct new correct answer
   * @param integer $paper_id
   */
  public function execute($new_correct, $paper_id, &$changes) {
    $new_correct_val = $new_correct['option_correct'];
    $errors = array();

    $old_correct_list = '';
    $i = 0;
    $correct_count = 0;
    foreach ($this->_question->options as $option) {
      if ($i == 0) {
        $mark_correct = $option->get_marks_correct();
        $mark_incorrect = $option->get_marks_incorrect();
      }
      $old_correct = $option->get_correct();
      $old_correct_list .= $old_correct . ',';
      if ($new_correct_val[$i] == $this->_question->get_answer_positive()) $correct_count++;
      if ($new_correct_val[$i] != $old_correct) {
        $option->set_correct($new_correct_val[$i]);
        $changes = true;

        $opt_no = $i + 1;
      }
      $i++;
    }

    if ($this->_question->get_display_method() == 'other') {
      $new_correct_val[] = $this->_question->get_answer_negative();
    }

    if ($changes) {
      $this->_question->add_unified_field_modification('correct', $this->_lang_strings['correctanswer'], rtrim($old_correct_list, ','),  implode(',', $new_correct_val), $this->_lang_strings['postexamchange']);

      try {
    	  if(!$this->_question->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          // Remark the student's answers in 'log2'.
          $totalpos = 0;
          $score_method = $this->_question->get_score_method();

          $totalpos = ($score_method == 'Mark per Question') ? $mark_correct : $mark_correct * $correct_count;

    	    $result = $this->_mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->_question->id, $paper_id);
          $result->execute();
          $result->store_result();
          $result->bind_result($user_answer);
          while ($row = $result->fetch()) {
            $user_answers = str_split($user_answer);

            $mark = 0;
            $all_correct = true;

            for ($i=0; $i < count($new_correct_val); $i++) {
              if ($score_method == 'Mark per Option') {
                if ($new_correct_val[$i] == $this->_question->get_answer_positive()) {
                  $mark += ($new_correct_val[$i] == $user_answers[$i]) ? $mark_correct : $mark_incorrect;
                }
              } elseif ($new_correct_val[$i] != $user_answers[$i]) {
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

            $updateLog = $this->_mysqli->prepare("UPDATE log2 SET mark=?, totalpos=? WHERE user_answer=? AND q_id=? AND q_paper=?");
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
