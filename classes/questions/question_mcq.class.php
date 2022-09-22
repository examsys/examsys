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
 * Class for Multiple Choice questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class QuestionMCQ extends QuestionEdit
{
    protected $min_options = 2;
    protected $_allow_change_marking_method = false;

    public function __construct($mysqli, $userObj, $lang_strings, $data = null)
    {
        parent::__construct($mysqli, $userObj, $lang_strings, $data);

        $this->_display_methods = array('vertical' => $this->_lang_strings['vertical'], 'vertical_other' => $this->_lang_strings['verticalother'], 'horizontal' => $this->_lang_strings['horizontal'], 'dropdown' => $this->_lang_strings['dropdownlist']);
    }
}
