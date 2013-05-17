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
 * Class for Extended Matching questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

Class QuestionMATRIX extends Question {
  
  protected $stems = array();  
  protected $_answer_negative = 0;
  public $max_options = 10;
  public $max_stems = 10;
  
  protected $_fields_required = array('type', 'leadin', 'option_order', 'owner_id', 'status');
  protected $_fields_editable = array('theme', 'leadin', 'notes', 'correct_fback', 'score_method', 'option_order', 'bloom', 'status');
  protected $_fields_compound = array('stem');
  
  function __construct($mysqli, $userObj, $lang_strings, $data = null) {
    parent::__construct($mysqli, $userObj, $lang_strings, $data);
    
    // 'correct' is not a unified field for Matrix because it is compound
    $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
  }


  // ACCESSORS
  
  /**
   * Get an array of stems for the compounded scenarios
   * @return multitype:
   */
  public function get_all_stems() {
    $this->get_scenario();
    return $this->stems;
  }
  
  /**
   * Compound the stems into a single string and set as the scenario
   * @return multitype:
   */
  public function set_all_stems($value) {
    $this->stems = $value;
    $this->set_scenario('dummy');
  }
  
  /**
   * Get the question scenario
   * @return string
   */
  public function get_scenario() {
    if ($this->scenario != '') {
      $this->stems = explode('|', $this->scenario);
    }
    return $this->scenario;
  }

  /**
   * Set the question scenario
   * @param string $value
   */
  public function set_scenario($value) {
    $this->scenario = implode('|', $this->stems);
  }
}

