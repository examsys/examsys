<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Class for Correction behaviour for Calculation questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class MARKSCorrector extends Corrector
{
    /**
     * Change the marks for a question
     *
     * WARNING: This behaviour must be followed by an additional corrector that commits changes to the database
     *
     * @param integer $new_correct new correct answer
     * @param integer $paper_id
     */
    public function execute($new_correct, $paper_id, &$changes, $paper_type)
    {
        $errors = array();

        $i = 0;
        foreach ($this->_question->options as $option) {
            if ($i == 0) {
                $mark_correct = $option->get_marks_correct();
                if (isset($new_correct['option_marks_correct']) and $new_correct['option_marks_correct'] != $mark_correct) {
                    $mark_correct = $new_correct['option_marks_correct'];
                    $changes = true;
                    $this->_question->add_unified_field_modification('marks_correct', $this->_lang_strings['markscorrect'], $option->get_marks_correct(), $mark_correct, $this->_lang_strings['postexamchange']);
                }
                $mark_incorrect = $option->get_marks_incorrect();
                if (isset($new_correct['option_marks_incorrect']) and $new_correct['option_marks_incorrect'] != $mark_incorrect) {
                    $mark_incorrect = $new_correct['option_marks_incorrect'];
                    $changes = true;
                    $this->_question->add_unified_field_modification('marks_incorrect', $this->_lang_strings['marksincorrect'], $option->get_marks_incorrect(), $mark_incorrect, $this->_lang_strings['postexamchange']);
                }
                $mark_partial = $option->get_marks_partial();
                if ($this->_question->allow_partial_marks() and isset($new_correct['option_marks_partial']) and $new_correct['option_marks_partial'] != $mark_partial) {
                    $mark_partial = $new_correct['option_marks_partial'];
                    $changes = true;
                    $this->_question->add_unified_field_modification('marks_partial', $this->_lang_strings['markspartial'], $option->get_marks_partial(), $mark_partial, $this->_lang_strings['postexamchange']);
                }
            }
            if ($changes) {
                $option->set_marks_correct($mark_correct, false);
                $option->set_marks_incorrect($mark_incorrect, false);
                $option->set_marks_partial($mark_partial, false);
            }
            $i++;
        }

        return $errors;
    }
}
