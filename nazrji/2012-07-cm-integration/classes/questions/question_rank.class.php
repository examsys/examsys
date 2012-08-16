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
 * Class for Ranking questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

Class QuestionRANK extends Question {

  protected $min_options = 2;
  protected $_answer_negative = 0;
  protected $_allow_partial_marks = true;
  
  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);
    
    $this->_score_methods = array($this->_lang_strings['markperquestion'], $this->_lang_strings['markperoption'], $this->_lang_strings['allowpartial'], $this->_lang_strings['bonusmark']);
    $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect'], 'marks_partial' => $this->_lang_strings['markspartial']);
    
    // 'correct' is not a unified field for Rank questions
    $this->_fields_editable[] = 'correct';
  }

  public function is_answer_blank($value) {
    return ($value == 0 or $value == '');
  }
  
  public function convert_to_mcq($correct_answer) {
    $this->set_type('mcq');
    $this->set_option_order('vertical');

    foreach ($this->options as $option) {
      $option->set_correct($correct_answer);
    }
    
    $this->save();

    return new QuestionMCQ($this->_mysqli, $this->_user_id, $this->id);
  }
}

