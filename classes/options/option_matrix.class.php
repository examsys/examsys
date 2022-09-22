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
 * Class for Extended Matching options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class OptionMATRIX extends OptionEdit
{
    protected $all_corrects = array();
    protected $_fields_compound = array('correct' => 'raw');

    // ACCESSORS

    /**
     * Get all the correct answers for this option.  Actually the correct answer across the board. Return as an array of array of correct
     * answers for each 'question'
     * @return string
     */
    public function get_all_corrects()
    {
        $this->get_correct();
        return $this->all_corrects;
    }

    public function set_all_corrects($value)
    {
        $stems = $this->_question->get_all_stems();
        $this->all_corrects = array();

        for ($i = 0; $i < $this->_question->max_stems; $i++) {
            $this->all_corrects[] = (isset($stems[$i]) and $stems[$i] != '') ? $value[$i] : '';
        }
        $this->set_correct('dummy');
    }

    public function get_correct()
    {
        $this->all_corrects = ($this->correct != '') ? explode('|', $this->correct) : array();
        return $this->correct;
    }

    public function set_correct($value)
    {
        $this->correct = implode('|', $this->all_corrects);
    }
}
