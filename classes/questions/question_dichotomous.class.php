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
 * Class for Dichotomous questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class QuestionDICHOTOMOUS extends QuestionEdit
{
    public $max_options = 15;
    protected $_answer_positive = 't';
    protected $_answer_negative = 'f';
    protected $display_method = 'TF_Positive';
    protected $_fields_change = array('option_correct', 'option_marks_correct', 'option_marks_incorrect');

    public function __construct($mysqli, $userObj, $lang_strings, $data = null)
    {
        parent::__construct($mysqli, $userObj, $lang_strings, $data);

        $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
        $this->_display_methods = array('TF_NegativeAbstain' => $this->_lang_strings['tfnegativeabstain'], 'TF_Positive' => $this->_lang_strings['tfpositive'], 'YN_NegativeAbstain' => $this->_lang_strings['ynnegativeabstain'], 'YN_Positive' => $this->_lang_strings['ynpositive']);

        // 'correct' is not a unified field for Dichotomous questions
        $this->_fields_editable[] = 'correct';
    }

    /**
     * Get the labels for true/false options. These change depending on the score method
     */
    public function get_tf_labels()
    {
        if (mb_substr($this->get_display_method(), 0, 2) == 'YN') {
            $labels = array('true' => mb_substr($this->_lang_strings['yes'], 0, 1), 'false' => mb_substr($this->_lang_strings['no'], 0, 1));
        } else {
            $labels = array('true' => mb_substr($this->_lang_strings['true'], 0, 1), 'false' => mb_substr($this->_lang_strings['false'], 0, 1));
        }

        return $labels;
    }
}
