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
 * Class for Fill in the Blank questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionBLANK extends Question {
  
  public $max_options = 1;
  protected $_answer_negative = null;
  protected $_allow_correction = false;
  

  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);
    
    $this->_fields_unified = array('text' => $this->_lang_strings['questionstem'], 'marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
    $this->_display_methods = array('dropdown' => $this->_lang_strings['dropdownlists'], 'textboxes' => $this->_lang_strings['textboxes']);
    
    // 'correct' is not a unified field for Dichotomous questions
    $this->_fields_editable[] = 'correct';
  }
}

