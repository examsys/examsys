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
 * Class for Multiple Response questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'question_mcq.class.php';

class QuestionMRQ extends QuestionEdit
{
    protected $min_options = 3;
    protected $_fields_force = array('display_method');

    public function __construct($mysqli, $userObj, $lang_strings, $data = null)
    {
        parent::__construct($mysqli, $userObj, $lang_strings, $data);

        $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);

        // 'correct' is not a unified field for MRQ
        $this->_fields_editable[] = 'correct';
    }

    public function convert_to_mcq($correct_answer)
    {
        // TODO: update question and get new MCQ object based on it
        $this->set_type('mcq');
        $this->set_display_method('vertical');

        foreach ($this->options as $option) {
            $option->set_correct($correct_answer);
        }

        $this->save();

        $q =  new QuestionMCQ($this->_mysqli, $this->_userObj, $this->_lang_strings, $this->id);
        return $q;
    }

    // ACCESSORS

    /**
     * Get the question display method
     * @return string
     */
    public function get_display_method()
    {
        return $this->display_method;
    }

    /**
     * Set the question display method
     * @param string $value
     */
    public function set_display_method($value)
    {
        if ($value == $this->_answer_negative) {
            $value = '';
        }
        parent::set_display_method($value);
    }
}
