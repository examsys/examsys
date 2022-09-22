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
 * Class for Multiple Response options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class OptionSCT extends OptionEdit
{
    public function save($option_number = 0)
    {
        if ($this->_question->get_max_experts() == 0) {
            $this->set_marks_correct(0);
        } else {
            $this->set_marks_correct($this->correct / $this->_question->get_max_experts());
        }

        return parent::save($option_number);
    }

    /**
     * Set the option correct answer
     * @param string $value
     */
    public function set_correct($value)
    {
        if ($value != $this->correct and !in_array('correct', array_keys($this->_question->get_unified_fields()))) {
            $this->set_modified_field('correct', $this->correct, "Option #{$this->_number} Experts");
        }
        $this->correct = $value;
    }
    /**
     * Set the option marks for correct answers
     * @param string $value
     */
    public function set_marks_correct($value, $log_change = true)
    {
        $this->marks_correct = $value;
    }
}
