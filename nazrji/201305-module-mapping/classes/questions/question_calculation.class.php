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
 * Class for Multiple Response questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

Class QuestionCALCULATION extends Question {

  protected $units = '';
  protected $answer_decimals = 0;
  protected $tolerance_full = 0;
  protected $tolerance_partial = 0;
  protected $score_method = 'Allow partial Marks';
  public $max_options = 10;
  protected $_allow_partial_marks = true;
  protected $_allow_change_marking_method = false;

  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'units', 'answer_decimals', 'tolerance_full', 'tolerance_partial', 'bloom', 'status');
  protected $_fields_change = array('option_correct', 'option_marks_correct', 'option_marks_incorrect', 'option_marks_partial', 'answer_decimals', 'tolerance_full', 'tolerance_partial');
  protected $_fields_settings = array('units', 'answer_decimals', 'tolerance_full', 'tolerance_partial');

  private $_variables = null;

  function __construct($mysqli, $userObj, $lang_strings, $data = null) {
    parent::__construct($mysqli, $userObj, $lang_strings, $data);

    $this->_score_methods = array($this->_lang_strings['allowpartial']);
    $this->_fields_unified = array('correct' => $this->_lang_strings['correctanswer'], 'marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect'], 'marks_partial' => $this->_lang_strings['markspartial']);

    // Convert the max number of options into a list of variables
    $this->_variables = range('A', chr(64 + $this->max_options));
    $this->option_order = 'display order';

    // Populate the pseudo properties
    $this->get_display_method();
  }

  // ACCESSORS

  /**
   * Get the variables for the question
   * @return integer
   */
  public function get_variables() {
    return $this->_variables;
  }

  /**
   * Get the units for the question
   * @return integer
   */
  public function get_units() {
    return $this->units;
  }

  /**
   * Set the units for the question
   * @param unknown_type $value
   */
  public function set_units($value) {
    if ($value != $this->units) {
      $this->set_modified_field('units', $this->units);
      $this->units = $value;
    }
  }

  /**
   * Get the number of decimal places for the question
   * @return integer
   */
  public function get_answer_decimals() {
    return $this->answer_decimals;
  }

  /**
   * Set the number of decimal places for the question
   * @param unknown_type $value
   */
  public function set_answer_decimals($value) {
    if ($value != $this->answer_decimals) {
      $this->set_modified_field('answer_decimals', $this->answer_decimals);
      $this->answer_decimals = $value;
    }
  }

  /**
   * Get the full marks tolerance for the question
   * @return integer
   */
  public function get_tolerance_full() {
    return $this->tolerance_full;
  }

  /**
   * Set the full marks tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance_full($value) {
    if ($value != $this->tolerance_full) {
      $this->set_modified_field('tolerance_full', $this->tolerance_full);
      $this->tolerance_full = $value;
    }
  }

  /**
   * Get the partial marks tolerance for the question
   * @return integer
   */
  public function get_tolerance_partial() {
    return $this->tolerance_partial;
  }

  /**
   * Set the partial marks tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance_partial($value) {
    if ($value != $this->tolerance_partial) {
      $this->set_modified_field('tolerance_partial', $this->tolerance_partial);
      $this->tolerance_partial = $value;
    }
  }
}

