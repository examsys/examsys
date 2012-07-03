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
 * Class for Correction behaviour for Image Hotspot questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class HOTSPOTCorrector extends Corrector {
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct new correct answer
   * @param integer $paper_id
   */
  public function execute($new_correct, $paper_id, &$changes, $paper_type) {
    $errors = array();

    $old_points = $this->_question->get_points1();
    $option = reset($this->_question->options);
    $marks_correct = $option->get_marks_correct();
    $marks_incorrect = $option->get_marks_incorrect();

    if ($old_points != $new_correct['points1']) {
      $changes = true;

      $this->_question->set_points1($new_correct['points1']);
      $this->_question->add_unified_field_modification('points', 'points', $old_points, $new_correct['points1'], $this->_lang_strings['postexamchange']);
    }

    if ($changes) {
      try {
    	  if(!$this->_question->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          $student_records = explode(';', $new_correct['option_correct1']);
          $max_layers = 0;

          foreach ($student_records as $student_record) {
            if (strlen($student_record) > 0) {
              $layers = explode('|',$student_record);
              $mark = 0;
              $correct_count = 0;
              $layer_no = 0;
              foreach ($layers as $layer) {
                $sub_parts = explode(',',$layer);
                if ($layer_no == 0) {
                  $database_id = $sub_parts[0];
                  $is_correct = $sub_parts[1];
                } else {
                  $is_correct = $sub_parts[0];
                }

                if ($is_correct == 1) {
                  $mark += $marks_correct;
                  $correct_count++;
                } else {
                  $mark += $marks_incorrect;
                }

                $layer_no++;
                $max_layers = ($layer_no > $max_layers) ? $layer_no : $max_layers;
              }

              if ($this->_question->get_score_method() == 'Mark per Question') {
                $totalpos = $marks_correct;
                if ($correct_count == $max_layers) {
                  $mark = $marks_correct;
                } else {
                  $mark = $marks_incorrect;
                }
              } else {
                $totalpos = $marks_correct * $max_layers;
              }

              $first_comma = strpos($student_record, ',') + 1;
              $tmp_user_answer = substr($student_record,$first_comma);

              $result = $this->_mysqli->prepare("UPDATE log{$paper_type} SET mark=?, totalpos=?, user_answer=? WHERE id=?");
              $result->bind_param('disi', $mark, $totalpos, $tmp_user_answer, $database_id);
              $result->execute();
              $result->close();
            }
          }
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
    }

    return $errors;
  }
}
