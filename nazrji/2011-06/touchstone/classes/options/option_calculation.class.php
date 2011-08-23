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
 * Class for Multiple Response options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class OptionCALCULATION extends Option {
  
  // Option level pseudo-properties for Calculation
  private $variable = '';
  protected $min = '';
  protected $max = '';
  protected $decimals = '';
  protected $increment = '';

  protected $_fields_editable = array('min', 'max', 'decimals', 'increment');
  
  /**
   * Ensure that text is in correct format before calling parent save() function
   * @return integer
   */
  public function save($option_number = 0) {
    $this->set_text();
    return parent::save($option_number);
  }
  
  /**
   * Is this option blank?
   * @return boolean
   */
  public function is_blank() {
    $this->get_text();
    return ($this->min == '' and $this->max == '');
  } 
    
  /**
   * Check that the minimum set of fields exist in the given data to create a new option 
   * @param array $data
   * @param array $files expects PHP FILES array
   * @param integer $index option number
   * @return boolean
   */
  public function minimum_fields_exist($data, $files, $index) {
    return (!empty($data["option_min$index"]) and !empty($data["option_max$index"]));
  }
  
  
  // ACCESSORS
  
  /**
   * Get the variable name for the option
   * @return integer
   */
  public function get_variable() {
    return $this->variable;
  }
  
  /**
   * Set the variable for the option
   * @param string $value
   */
  public function set_variable($value) {
    $this->variable = $value;
  }
  
  /**
   * Get the minimum value for the option
   * @return integer
   */
  public function get_min() {
    $this->get_text();
    return $this->min;
  }
  
  /**
   * Set the minimum value for the option
   * @param integer $value
   */
  public function set_min($value) {
    if ($value != $this->get_min()) {
      $this->set_modified_field('min', $this->min);
      $this->min = $value;
      $this->set_text();
    }
  }
  
  /**
   * Get the maximum value for the option
   * @return integer
   */
  public function get_max() {
    $this->get_text();
    return $this->max;
  }
  
  /**
   * Set the maximum value for the option
   * @param integer $value
   */
  public function set_max($value) {
    if ($value != $this->get_max()) {
      $this->set_modified_field('max', $this->max);
      $this->max = $value;
      $this->set_text();
    }
  }
  
  /**
   * Get the number of decimal places for the option
   * @return integer
   */
  public function get_decimals() {
    $this->get_text();
    return $this->decimals;
  }
  
  /**
   * Set the number of decimal places for the option
   * @param integer $value
   */
  public function set_decimals($value) {
    if ($value != $this->get_decimals()) {
      $this->set_modified_field('decimals', $this->decimals);
      $this->decimals = $value;
      $this->set_text();
    }
  }
  
  /**
   * Get the increment for the option
   * @return integer
   */
  public function get_increment() {
    $this->get_text();
    return $this->increment;
  }
  
  /**
   * Set the increment for the option
   * @param integer $value
   */
  public function set_increment($value) {
    if ($value != $this->get_increment()) {
      $this->set_modified_field('increment', $this->increment);
      $this->increment = $value;
      $this->set_text();
    }
  }

  /**
   * Extract the option text into pseudo-properties
   * @return string
   */
  public function get_text() {
    if ($this->text != '') {
      $parts = explode(',', $this->text);
      $this->min = $parts[0];
      $this->max = $parts[1];
      $this->increment = $parts[2];
      $this->decimals = $parts[3];
    }
    return $this->text;
  }

  /**
   * Set the option text
   * @param string $value
   */
  public function set_text($value=-1) {
    $this->text = $this->min . ',' . $this->max . ',' . $this->increment . ',' . $this->decimals;
  }
  
  // PRIVATE / PROTECTED METHODS
  
  
  /**
   * Track the addition of a new option.
   * @param Logger $option_number
   * @param integer $option_number
   */
  protected function track_new($logger, $option_number) {
    $logger->track_change('New Variable', $this->question_id, $this->_user_id, '', $this->min . ',' . $this->max, 'Variable $' . chr(64 + $option_number));
  }
  
  /**
   * Track the change of an option.  The message may be different in other question types so allow this method to be overridden
   * @param Logger $option_number
   * @param integer $option_number
   * @param mixed $old
   * @param mixed $new
   * @param string $field
   */
  protected function track_change($logger, $option_number, $old, $new, $field) {
    $logger->track_change('Edit ' . ucwords($field), $this->question_id, $this->_user_id, $old, $new, 'Variable $' . chr(64 + $option_number));
  }

  /**
   * Track the deletion of an option
   * @param Logger $option_number
   * @param integer $option_number
   */
  protected function track_delete($logger, $option_number) {
    $logger->track_change('Deleted Variable', $this->question_id, $this->_user_id, '', '', 'Variable $' . chr(64 + $option_number));
  }
}

