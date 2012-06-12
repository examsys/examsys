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
 * Class for Correction behaviour for Labelling questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class LABELLINGCorrector extends Corrector {
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct new correct answer
   * @param integer $paper_id
   */
  public function execute($new_correct, $paper_id, &$changes, $paper_type) {
    $errors = array();

    if ($changes) {
      $correct = $this->_question->get_points1();
      $option = reset($this->_question->options);
      $mark_correct = $option->get_marks_correct();
      $mark_incorrect = $option->get_marks_incorrect();

      try {
        if(!$this->_question->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          // Remark the student's answers in 'log{$paper_type}'.
          $label_details = $option->get_correct();

          // Calculate how many correct labels - are they on the canvas (x > 219px)
          $correct_labels = array();
          $tmp_first_split = explode(';', $label_details);
          $tmp_second_split = explode('|', $tmp_first_split[11]);
          $label_count = 0;
          foreach ($tmp_second_split as $label) {
            $tmp_third_split = explode('$', $label);
            if (isset($tmp_third_split[4]) and $tmp_third_split[4] != '') {
              $label_count++;
              if ($tmp_third_split[2] > 219) {
                $x = $tmp_third_split[2];
                $y = $tmp_third_split[3] - 25;
                $correct_labels[$x . 'x' . $y] = $tmp_third_split[4];
                $tmp_pos = strpos($correct_labels[$x . 'x' . $y], '~');
                if ($tmp_pos !== false) {
                  $correct_labels[$x . 'x' . $y] = substr($correct_labels[$x . 'x' . $y], 0, $tmp_pos);
                }
              }
            }
          }

          $correct_count = count($correct_labels);


          $score_method = $this->_question->get_score_method();

          $totalpos = ($score_method == 'Mark per Question') ? $mark_correct : $mark_correct * $correct_count;

    	    $result = $this->_mysqli->prepare("SELECT DISTINCT user_answer FROM log{$paper_type} WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->_question->id, $paper_id);
          $result->execute();
          $result->store_result();
          $result->bind_result($user_answer);
          while ($row = $result->fetch()) {
            $mark = 0;
            $all_correct = true;
            $correct_no = 0;

            if ($user_answer != '') {
              $user_split1 = explode(';', $user_answer);
              $user_split2 = explode('$', $user_split1[1]);

              $i = 0;
              for ($a=0; $a<count($user_split2)-3; $a+=4) {
                $x = $user_split2[$a];
                $y = $user_split2[$a+1];
                if (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] == $user_split2[$a+2]) {
                  $mark += $mark_correct;
                  $correct_no++;
                } else {
                  $mark += $mark_incorrect;
                  $all_correct = false;
                }
                $i++;
              }
            }

            if ($score_method == 'Mark per Question') {
              if ($correct_no == $correct_count) {
                $mark = $mark_correct;
              } elseif ($correct_no == 0 and $all_correct == true) {
                $mark = 0;
              } else {
                $mark = $mark_incorrect;
              }
            }


            $user_split1[0] = $mark . '$' . $totalpos;
            $user_answer_new = implode(';', $user_split1);

            $updateLog = $this->_mysqli->prepare("UPDATE log{$paper_type} SET mark=?, totalpos=?, user_answer=? WHERE user_answer=? AND q_id=? AND q_paper=?");
            $updateLog->bind_param('dissii', $mark, $totalpos, $user_answer_new, $user_answer, $this->_question->id, $paper_id);
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
