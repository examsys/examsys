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
 * Class for Correction behaviour for Calculation questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'classes/stringutils.class.php';
include_once 'Corrector.class.php';

class CALCULATIONCorrector extends Corrector {
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct new correct answer
   * @param integer $paper_id
   */
  public function execute($new_correct, $paper_id, &$changes) {
    $errors = array();

    $first = reset($this->_question->options);
    $old_correct = $first->get_correct();
    $mark_correct = $first->get_marks_correct();
    $mark_incorrect = $first->get_marks_incorrect();
    $mark_partial = $first->get_marks_partial();

    if ($new_correct['option_correct'] != $old_correct) {
      foreach ($this->_question->options as $option) {
        $option->set_correct($new_correct['option_correct']);
      }

      $this->_question->add_unified_field_modification('correct', 'formula', $old_correct, $new_correct['option_correct'], $this->_lang_strings['postexamchange']);
      $changes = true;
    }

    $old_decimals = $this->_question->get_answer_decimals();
    if ($new_correct['answer_decimals'] != $old_decimals) {
      $this->_question->set_answer_decimals($new_correct['answer_decimals']);

      $this->_question->add_unified_field_modification('answer_decimals', 'answer_decs ', $old_decimals, $new_correct['answer_decimals'], $this->_lang_strings['postexamchange']);
      $changes = true;
    }

    $old_tolerance_full = $this->_question->get_tolerance_full();
    if ($new_correct['tolerance_full'] != $old_tolerance_full) {
      $this->_question->set_tolerance_full($new_correct['tolerance_full']);

      $this->_question->add_unified_field_modification('tolerance_full', 'tolerance_full', $old_tolerance_full, $new_correct['tolerance_full'], $this->_lang_strings['postexamchange']);
      $changes = true;
    }

    $old_tolerance_partial = $this->_question->get_tolerance_partial();
    if ($new_correct['tolerance_partial'] != $old_tolerance_partial) {
      $this->_question->set_tolerance_partial($new_correct['tolerance_partial']);

      $this->_question->add_unified_field_modification('tolerance_partial', 'tolerance_partial', $old_tolerance_partial, $new_correct['tolerance_partial'], $this->_lang_strings['postexamchange']);
      $changes = true;
    }

    if ($changes) {
      try {
    	  if (!$this->_question->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          $decimals = $this->_question->get_answer_decimals();
          $answer_equation = $first->get_correct();

          // Remark the student's answers in 'log2'.
          $result = $this->_mysqli->prepare("SELECT user_answer, id FROM log2 WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->_question->id, $paper_id);
          $result->execute();
          $result->store_result();
          $result->bind_result($user_answer, $id);
          while ($result->fetch()) {
            // Split up the user answer into its constituent parts.
            $answer_parts = explode('|',$user_answer);
            $variable_array = explode(',',$answer_parts[2]);
            $saved_response = $answer_parts[0];
            $var_no = 1;
            foreach($variable_array as $individual_variable) {
              $var = chr(64 + $var_no);
              $$var = $individual_variable;
              $var_no++;
            }
            $mark = 0;

            eval ("\$answer = $answer_equation;");
            $answer = round($answer, $decimals);

            $tolerance_full = $this->_question->get_tolerance_full();
            if (StringUtils::ends_with($tolerance_full, '%')) {
              $tolerance_perc = rtrim($tolerance_full, '%');
              $tolerance_full = $answer * ($tolerance_perc/100);
            }
            $tolerance_partial = $this->_question->get_tolerance_partial();
            if (StringUtils::ends_with($tolerance_partial, '%')) {
              $tolerance_perc = rtrim($tolerance_partial, '%');
              $tolerance_partial = $answer * ($tolerance_perc/100);
            }

            $saved_response_clean = preg_replace('([^0-9\.\-])', '', $saved_response);
            $difference = round(abs($saved_response_clean - $answer), 12);

            if ($saved_response_clean != '') {
              if ($saved_response_clean == $answer) {
                $mark = $mark_correct;
              } elseif ($difference > 0 and $difference <= $tolerance_full and $tolerance_full > 0) {
                $mark = $mark_correct;
              } elseif ($difference > 0 and $difference <= $tolerance_partial and $tolerance_partial > 0) {
                $mark = $mark_partial;
              } else {
                $mark = $mark_incorrect;
              }
            }
            $saved_response .= '|' . $answer . '|' . $answer_parts[2];
            
            $updateLog = $this->_mysqli->prepare("UPDATE log2 SET mark=?, user_answer=? WHERE id=? AND q_paper=?");
            $updateLog->bind_param('dsii', $mark, $saved_response, $id, $paper_id);
            $updateLog->execute();
            $updateLog->close();
          }
          $result->close();
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
    }

    return $errors;
  }
}
