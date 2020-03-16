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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'Corrector.class.php';

class HOTSPOTCorrector extends Corrector
{
    /**
     * Change the correct answer after the question has been locked. Update user marks in summative log table
     * @param integer $new_correct new correct answer
     * @param integer $paper_id
     */
    public function execute($new_correct, $paper_id, &$changes, $paper_type)
    {
        $errors = array();
        
        $old_points = $this->_question->get_points1();
        $option = reset($this->_question->options);
        $marks_correct = $option->get_marks_correct();
        $marks_incorrect = $option->get_marks_incorrect();
        $this->_question->set_points1($new_correct['points1']);
        $this->_question->add_unified_field_modification('points', 'points', $old_points, $new_correct['points1'], $this->_lang_strings['postexamchange']);

        try {
            if (!$this->_question->save()) {
                $errors[] = $this->_lang_strings['datasaveerror'];
            } else {
                // Marking MUST not be client side.
                $student_records = explode(';', $new_correct['option_correct1']);
                foreach ($student_records as $student_record) {
                    if (strlen($student_record) > 0) {
                        $database_id = substr($student_record, 0, strpos($student_record, ','));
                        $answers = ltrim(strstr($student_record, ','), ',');
                        $answers = rtrim($answers, ',');
                        $mark = 0;
                        $all_correct = true;
                        $hotspot_answer = hotspot_helper::get_instance()->mark($answers, $new_correct['points1']);
                        $saved_response = $hotspot_answer;
                        $sub_parts = explode('|', $saved_response);
                        foreach ($sub_parts as $sub_part) {
                            if ($sub_part[0] == 1) {
                                $mark += $marks_correct;
                            } else {
                                $all_correct = false;
                                $mark += $marks_incorrect;
                            }
                        }
                        // Override marks if 'Mark per Question' is set.
                        if ($this->_question->get_score_method() == 'Mark per Question') {
                            if ($all_correct) {
                                $mark = $marks_correct;
                            } else {
                                $mark = $marks_incorrect;
                            }
                            $totalpos = $marks_correct;
                        } else {
                            $totalpos = (substr_count($new_correct['points1'], '|') + 1) * $marks_correct;
                        }

                        $result = $this->_mysqli->prepare("UPDATE log{$paper_type} SET mark = ?, totalpos = ?, user_answer = ? WHERE id = ?");
                        $result->bind_param('disi', $mark, $totalpos, $hotspot_answer, $database_id);
                        $result->execute();
                        $result->close();
                    }
                }
            }
        } catch (ValidationException $vex) {
            $errors[] = $vex->getMessage();
        }

        if (count($errors) == 0) {
            $this->invalidate_paper_cache($paper_id);
        }


        return $errors;
    }
}
