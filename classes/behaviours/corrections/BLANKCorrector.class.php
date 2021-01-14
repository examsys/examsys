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
 * Class for Correction behaviour for Fill in the Blank questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class BLANKCorrector extends Corrector
{
    /**
     * Change the correct answer after the question has been locked. Update user marks in summative log table
     * @param integer $new_correct new correct answer
     * @param integer $paper_id
     */
    public function execute($new_correct, $paper_id, &$changes, $paper_type)
    {
        $errors = array();
        if ($changes) {
            $new_correct_val = $new_correct['option_correct'];

            $correct_count = count($this->_question->options);
            $first = reset($this->_question->options);
            $mark_correct = $first->get_marks_correct();
            $mark_incorrect = $first->get_marks_incorrect();
            $option_text = $first->get_text();
            $display_method = $this->_question->get_display_method();

            try {
                if (!$this->_question->save()) {
                    $errors[] = $this->_lang_strings['datasaveerror'];
                } elseif ($paper_id !== -1) {
                    // Remark the student's answers in 'log{$paper_type}'.
                    $score_method = $this->_question->get_score_method();

                    $totalpos = 0;

                    $blank_details = explode('[blank', $option_text);
                    $no_answers = count($blank_details) - 1;
                    $have_answer = false;

                    $answer_lists = array();
                    $part_marks = array();

                    for ($i = 1; $i <= $no_answers; $i++) {
                        if (preg_match('|mark="([0-9]{1,3})"|', $blank_details[$i], $mark_matches)) {
                            $totalpos += $mark_matches[1];
                            $part_marks[] = $mark_matches[1];
                        } else {
                            $totalpos += $mark_correct;
                            $part_marks[] = $mark_correct;
                        }

                        // Get correct answer.
                        $blank_details[$i] = mb_substr($blank_details[$i], (mb_strpos($blank_details[$i], ']') + 1));
                        $blank_details[$i] = mb_substr($blank_details[$i], 0, mb_strpos($blank_details[$i], '[/blank]'));
                        $answer_list = explode(',', $blank_details[$i]);

                        $answer_list[0] = str_replace('[/blank]', '', $answer_list[0]);
                        if ($display_method != 'textboxes') {
                              $answer_list = array($answer_list[0]);
                        }
                        $answer_list = array_map('mb_strtolower', $answer_list);
                        $answer_list = array_map('trim', $answer_list);

                        $answer_lists[] = $answer_list;
                    }

                    $result = $this->_mysqli->prepare("SELECT l.user_answer, l.id FROM log{$paper_type} l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE l.q_id = ? AND lm.paperID = ?");
                    $result->bind_param('ii', $this->_question->id, $paper_id);
                    $result->execute();
                    $result->store_result();
                    $result->bind_result($user_answer, $id);
                    while ($result->fetch()) {
                        $user_answers = explode('|', $user_answer);
                        // Drop first element
                        array_shift($user_answers);

                        $mark = 0;
                        $all_correct = true;

                        for ($i = 0; $i < count($answer_lists); $i++) {
                            $correct = (isset($user_answers[$i]) and in_array(trim(mb_strtolower($user_answers[$i])), $answer_lists[$i]));
                            if ($score_method == 'Mark per Option') {
                                $mark += ($correct) ? $mark_correct : $mark_incorrect;
                            } elseif (!$correct) {
                                  $all_correct = false;
                            }
                        }

                        if ($score_method == 'Mark per Question') {
                            if ($all_correct) {
                                $mark = $mark_correct;
                            } else {
                                $mark = $mark_incorrect;
                            }
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
