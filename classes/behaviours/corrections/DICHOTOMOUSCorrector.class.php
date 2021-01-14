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
 * Class for Correction behaviour for Dichotomous questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class DICHOTOMOUSCorrector extends Corrector
{
    /**
     * Change the correct answer after the question has been locked. Update user marks in summative log table
     * @param integer $new_correct new correct answer
     * @param integer $paper_id
     */
    public function execute($new_correct, $paper_id, &$prev_changes, $paper_type)
    {
        $new_correct_val = $new_correct['option_correct'];
        $errors = array();
        $changes = false;

        $old_correct_list = '';
        $i = 0;
        foreach ($this->_question->options as $option) {
            if ($i == 0) {
                $mark_correct = $option->get_marks_correct();
                $mark_incorrect = $option->get_marks_incorrect();
            }
            $old_correct = $option->get_correct();
            $old_correct_list .= $old_correct . ',';
            if ($new_correct_val[$i] != $old_correct) {
                $option->set_correct($new_correct_val[$i]);
                $changes = true;
            }
            $i++;
        }
        $totalpos = $i * $mark_correct;

        if ($prev_changes or $changes) {
            if ($changes) {
                $prev_changes = $changes;
                $this->_question->add_unified_field_modification('correct', $this->_lang_strings['correctanswer'], rtrim($old_correct_list, ','), implode(',', $new_correct_val), $this->_lang_strings['postexamchange']);
            }
            try {
                if (!$this->_question->save()) {
                    $errors[] = $this->_lang_strings['datasaveerror'];
                } elseif ($paper_id !== -1) {
                    // Remark the student's answers in 'log{$paper_type}'.
                    $score_method = $this->_question->get_score_method();

                    $result = $this->_mysqli->prepare("SELECT l.user_answer, l.id FROM log{$paper_type} l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE l.q_id = ? AND lm.paperID = ?");
                    $result->bind_param('ii', $this->_question->id, $paper_id);
                    $result->execute();
                    $result->store_result();
                    $result->bind_result($user_answer, $id);
                    while ($result->fetch()) {
                        $user_answers = mb_str_split($user_answer);
                        $mark = 0;

                        for ($i = 0; $i < count($new_correct_val); $i++) {
                            // Don't do anything if option is unanswered
                            if ($user_answers[$i] == $this->_question->get_answer_positive() or $user_answers[$i] == $this->_question->get_answer_negative()) {
                                if ($score_method == 'Mark per Question' and $new_correct_val[$i] == $user_answers[$i]) {
                                    // 'Mark' here is just a count of correct answers
                                    $mark++;
                                } else {
                                    $mark += ($new_correct_val[$i] == $user_answers[$i]) ? $mark_correct : $mark_incorrect;
                                }
                            }
                        }

                        // Set mark for per-question settings
                        if ($score_method == 'Mark per Question') {
                            $mark = ($mark == count($new_correct_val)) ? $mark_correct : $mark_incorrect;
                            $totalpos = $mark_correct;
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
