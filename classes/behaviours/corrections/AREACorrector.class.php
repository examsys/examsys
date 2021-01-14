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

include_once 'Corrector.class.php';

class AREACorrector extends Corrector
{
    /**
     * Change the correct answer after the question has been locked. Update user marks in summative log table
     * @param integer $new_correct new correct answer
     * @param integer $paper_id
     */
    public function execute($new_correct, $paper_id, &$changes, $paper_type)
    {
        $errors = array();

        $first = reset($this->_question->options);
        $marks_correct = $first->get_marks_correct();
        $marks_incorrect = $first->get_marks_incorrect();
        $marks_partial = $first->get_marks_partial();

        $old_correct_full = $this->_question->get_correct_full();
        if ($new_correct['correct_full'] != $old_correct_full) {
            $this->_question->set_correct_full($new_correct['correct_full']);

            $this->_question->add_unified_field_modification('correct_full', 'correct_full ', $old_correct_full, $new_correct['correct_full'], $this->_lang_strings['postexamchange']);
            $changes = true;
        }

        $old_error_full = $this->_question->get_error_full();
        if ($new_correct['error_full'] != $old_error_full) {
            $this->_question->set_error_full($new_correct['error_full']);

            $this->_question->add_unified_field_modification('error_full', 'error_full ', $old_error_full, $new_correct['error_full'], $this->_lang_strings['postexamchange']);
            $changes = true;
        }

        $old_correct_partial = $this->_question->get_correct_partial();
        if ($new_correct['correct_partial'] != $old_correct_partial) {
            $this->_question->set_correct_partial($new_correct['correct_partial']);

            $this->_question->add_unified_field_modification('correct_partial', 'correct_partial ', $old_correct_partial, $new_correct['correct_partial'], $this->_lang_strings['postexamchange']);
            $changes = true;
        }

        $old_error_partial = $this->_question->get_error_partial();
        if ($new_correct['error_partial'] != $old_error_partial) {
            $this->_question->set_error_partial($new_correct['error_partial']);

            $this->_question->add_unified_field_modification('error_partial', 'error_partial ', $old_error_partial, $new_correct['error_partial'], $this->_lang_strings['postexamchange']);
            $changes = true;
        }


        if ($changes) {
            try {
                if (!$this->_question->save()) {
                    $errors[] = $this->_lang_strings['datasaveerror'];
                } elseif ($paper_id !== -1) {
                    $correct_full = $this->_question->get_correct_full();
                    $error_full = $this->_question->get_error_full();
                    $correct_partial = $this->_question->get_correct_partial();
                    $error_partial = $this->_question->get_error_partial();

                    // Remark the student's answers in 'log{$paper_type}'.
                    $result = $this->_mysqli->prepare("SELECT l.user_answer, l.id, l.mark FROM log{$paper_type} l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE l.q_id = ? AND lm.paperID = ?");
                    $result->bind_param('ii', $this->_question->id, $paper_id);
                    $result->execute();
                    $result->store_result();
                    $result->bind_result($user_answer, $id, $user_mark);
                    while ($result->fetch()) {
                        // Split up the user answer into its constituent parts.
                        $answer_parts = explode(',', $user_answer);

                        $mark = 0;

                        if ($answer_parts[1] >= $correct_full and $answer_parts[2] <= $error_full) {
                            $mark = $marks_correct;
                        } elseif ($answer_parts[1] >= $correct_partial and $answer_parts[2] <= $error_partial) {
                            $mark = $marks_partial;
                        } else {
                            $mark = $marks_incorrect;
                        }
                        $totalpos = $marks_correct;

                        if ($mark != $user_mark) {
                            $updateLog = $this->_mysqli->prepare("UPDATE log{$paper_type} SET mark = ?, totalpos = ? WHERE id = ?");
                            $updateLog->bind_param('dii', $mark, $totalpos, $id);
                            $updateLog->execute();
                            $updateLog->close();
                        }
                    }
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
