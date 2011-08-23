<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Class for Multiple Response questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionCALCULATION extends Question {
  
  protected $units = '';
  protected $answer_decimals = 0;
  protected $tolerance = 0;
  public $max_options = 10;
  
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'units', 'answer_decimals', 'tolerance', 'bloom', 'status');
  protected $_fields_unified = array('marks' => 'Marks');
  
  private $_variables = null;
  
  function __construct($mysqli, $user_id, $data = null) {
    parent::__construct($mysqli, $user_id, $data);

    // Convert the max number of options into a list of variables
    $this->_variables = range('A', chr(64 + $this->max_options));
    $this->option_order = 'display order';
  }

  /**
   * Ensure that score_method is in correct format before calling parent save() function
   * @return integer
   */
  public function save($clear_checkout = true) {
    $this->set_score_method();
    return parent::save($clear_checkout);
  }
  
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct array of new correct answers
   * @param integer $paper_id
   */
  public function update_correct($new_correct, $paper_id) {
    $errors = array();
    
    return $errors;
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
    $this->get_score_method();
    return $this->units;
  }
  
  /**
   * Set the units for the question
   * @param unknown_type $value
   */
  public function set_units($value) {
    if ($value != $this->get_units()) {
      $this->set_modified_field('units', $this->units);
      $this->units = $value;
      $this->set_score_method();
    }
  }

  /**
   * Get the number of decimal places for the question
   * @return integer
   */
  public function get_answer_decimals() {
    $this->get_score_method();
    return $this->answer_decimals;
  }
  
  /**
   * Set the number of decimal places for the question
   * @param unknown_type $value
   */
  public function set_answer_decimals($value) {
    if ($value != $this->get_answer_decimals()) {
      $this->set_modified_field('answer_decimals', $this->answer_decimals);
      $this->answer_decimals = $value;
      $this->set_score_method();
    }
  }

  /**
   * Get the tolerance for the question
   * @return integer
   */
  public function get_tolerance() {
    $this->get_score_method();
    return $this->tolerance;
  }
  
  /**
   * Set the tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance($value) {
    if ($value != $this->get_tolerance()) {
      $this->set_modified_field('tolerance', $this->tolerance);
      $this->tolerance = $value;
      $this->set_score_method();
    }
  }

  /**
   * Get the question score method, populating pseudo-properties as we go
   * @return string
   */
  public function get_score_method() {
    if ($this->score_method != '') {
      $parts = explode(',', $this->score_method);
      $this->answer_decimals = $parts[0];
      $this->tolerance = $parts[1];
      $this->units = $parts[2];
    }
    return $this->score_method;
  }
  
  /**
   * Set the score method for the question - this is a composite of decimals, tolerance and units
   * @param unknown_type $value
   */
  public function set_score_method($value=-1) {
    $this->score_method = $this->answer_decimals . ',' . $this->tolerance . ',' . $this->units;
  }
}

