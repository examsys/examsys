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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once dirname(dirname(dirname(__DIR__))) . '/plugins/questions/enhancedcalc/helpers/enhancedcalc_helper.php';

class ENHANCEDCALCCorrector extends Corrector
{

    /**
     * Change the correct answer after the question has been locked. Update user marks in summative log table
     * @param mixed $new_correct Array of new values for fields that can be corrected
     * @param integer $paper_id
     * @param boolean $changes True if changes have been made by a previous corrector
     * @param integer $paper_type Integer index for type of paper
     * @return array[$string] Any errors encountered in the correction process
     */
    public function execute($new_correct, $paper_id, &$changes, $paper_type)
    {
        $errors = array();

        $marks_correct = $this->_question->get_marks_correct();
        $marks_incorrect = $this->_question->get_marks_incorrect();
        $marks_partial = $this->_question->get_marks_partial();

        $tolerance_full = $this->_question->get_tolerance_full();
        if ($tolerance_full != $new_correct['tolerance_full']) {
            $this->_question->set_tolerance_full($new_correct['tolerance_full']);
            $changes = true;

            $this->_question->add_unified_field_modification('tolerance_full', 'tolerance_full', $tolerance_full, $new_correct['tolerance_full'], $this->_lang_strings['postexamchange']);
        }

        $tolerance_partial = $this->_question->get_tolerance_partial();
        if ($tolerance_partial != $new_correct['tolerance_partial']) {
            $this->_question->set_tolerance_partial($new_correct['tolerance_partial']);
            $changes = true;

            $this->_question->add_unified_field_modification('tolerance_partial', 'tolerance_partial', $tolerance_partial, $new_correct['tolerance_partial'], $this->_lang_strings['postexamchange']);
        }

        $answer_precision = $this->_question->get_answer_precision();
        if ($answer_precision != $new_correct['answer_precision']) {
            $this->_question->set_answer_precision($new_correct['answer_precision']);
            $changes = true;

            $this->_question->add_unified_field_modification('answer_precision', 'answer_precision', $answer_precision, $new_correct['answer_precision'], $this->_lang_strings['postexamchange']);
        }

        $strict_zeros = $this->_question->get_strict_zeros();
        // Need to be careful of how the correction code builds the values for check boxes
        if (isset($new_correct['strict_zeros'])) {
            $new_strict_zeros = (is_array($new_correct['strict_zeros'])) ? $new_correct['strict_zeros'][0] : $new_correct['strict_display'];
        } else {
            $new_strict_zeros = false;
        }
        if ($strict_zeros != $new_strict_zeros) {
            $this->_question->set_strict_zeros($new_strict_zeros);
            $changes = true;

            $this->_question->add_unified_field_modification('strict_zeros', 'strict_zeros', $strict_zeros, $new_strict_zeros, $this->_lang_strings['postexamchange']);
        }


        // Parse answers
        $opts = $this->_question->options;
        for ($i = 1; $i <= $this->_question->max_options; $i++) {
            if (isset($opts[$i])) {
                $ans = $opts[$i]->get_formula();
                $units = $opts[$i]->get_units();

                if ($ans != '' and $new_correct['option_formula'][$i - 1] == '') {
                    $opts[$i]->set_formula('');
                    $opts[$i]->set_units('');
                    $changes = true;
                    $this->_question->add_unified_field_modification('Deleted Answer ' . $i, 'Deleted Answer ' . $i, $ans . ', ' . $units, '', $this->_lang_strings['postexamchange']);
                } else {
                    if ($ans != $new_correct['option_formula'][$i - 1]) {
                        $opts[$i]->set_formula($new_correct['option_formula'][$i - 1]);
                        $changes = true;

                        if ($ans != '') {
                            $this->_question->add_unified_field_modification('option_formula' . $i, 'option_formula' . $i, $ans, $new_correct['option_formula'][$i - 1], $this->_lang_strings['postexamchange']);
                        }
                    }

                    if ($units != $new_correct['option_units'][$i - 1]) {
                        $opts[$i]->set_units($new_correct['option_units'][$i - 1]);
                        $changes = true;

                        if ($ans != '') {
                            $this->_question->add_unified_field_modification('option_units' . $i, 'option_units' . $i, $units, $new_correct['option_units'][$i - 1], $this->_lang_strings['postexamchange']);
                        }
                    }

                    if ($ans == '') {
                        $this->_question->add_unified_field_modification('New Answer ' . $i, 'New Answer ' . $i, '', $new_correct['option_formula'][$i - 1] . ', ' . $new_correct['option_units'][$i - 1], $this->_lang_strings['postexamchange']);
                    }
                }
            } elseif ($new_correct['option_formula'][$i - 1] != '') {
                // Complete new answer
                $changes = true;
                $userObj = UserObject::get_instance();
                $this->_question->options[$i] = new OptionENHANCEDCALC($this->_mysqli, $userObj->get_user_ID(), $this->_question, $i, $this->_lang_strings, array('formula' => $new_correct['option_formula'][$i - 1], 'units' => $new_correct['option_units'][$i - 1]));
                $this->_question->add_unified_field_modification('New Answer ' . $i, 'New Answer ' . $i, '', $new_correct['option_formula'][$i - 1] . ', ' . $new_correct['option_units'][$i - 1], $this->_lang_strings['postexamchange']);
            }
        }

        if ($changes) {
            try {
                if (!$this->_question->save()) {
                    $errors[] = $this->_lang_strings['datasaveerror'];
                } elseif ($paper_id !== -1) {
                    enhancedcalc_remark($paper_type, $paper_id, $this->_question->id, $this->_question->get_settings(), $this->_mysqli, 'all');
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
