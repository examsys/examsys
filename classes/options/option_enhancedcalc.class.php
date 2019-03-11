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
 * Class for Extended Calculation question options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

Class OptionENHANCEDCALC extends OptionEdit {

  // Option level pseudo-properties for Calculation
  private $variable = '';
  protected $min = '';
  protected $max = '';
  protected $decimals = '';
  protected $increment = '';
  protected $formula = '';
  protected $units = '';

  protected $_fields_editable = array('min', 'max', 'decimals', 'increment', 'formula', 'units');
  private $_fields_var = array('min', 'max', 'decimals', 'increment');
  private $_fields_ans = array('formula', 'units');
  protected $_fields_compound = array();

  /**
   * This option is not directly persisted
   * @param int $option_number Index of this option
   * @return boolean
   */
  public function save($option_number = 0) {
    $logger = new Logger($this->_mysqli);

    if ($this->is_new($this->_fields_var)) {
      $this->track_new_var($logger, $option_number);
      $this->clear_mods($this->_fields_var);
    }

    if ($this->is_new($this->_fields_ans)) {
      $this->track_new_ans($logger, $option_number);
      $this->clear_mods($this->_fields_ans);
    }

    if ($this->is_deleted(array('min'))) {
      $this->track_delete_var($logger, $option_number);
      $this->clear_mods($this->_fields_var);
    }

    if ($this->is_deleted(array('formula'))) {
      $this->track_delete_ans($logger, $option_number);
      $this->clear_mods($this->_fields_ans);
    }

    // Log any remaining changes
    $this->save_changes($logger, $option_number);

    return true;
  }

  /**
   * Is this option blank?
   * @return boolean
   */
  public function is_blank() {
    $this->get_text();
    return ($this->min == '' and $this->max == '' and $this->formula =='' and $this->units = '');
  }

  /**
   * Check that the minimum set of fields exist in the given data to create a new option
   * @param array $data
   * @param array $files expects PHP FILES array
   * @param integer $index option number
   * @return boolean
   */
  public function minimum_fields_exist($data, $files, $index) {
    return ((isset($data["option_min$index"]) and $data["option_min$index"] != '') or $data["option_formula$index"] != '');
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
    return $this->min;
  }

  /**
   * Set the minimum value for the option
   * @param integer $value
   */
  public function set_min($value) {
    if ($value != $this->min) {
      $this->set_modified_field('min', $this->min);
      $this->min = $value;
    }
  }

  /**
   * Get the maximum value for the option
   * @return integer
   */
  public function get_max() {
    return $this->max;
  }

  /**
   * Set the maximum value for the option
   * @param integer $value
   */
  public function set_max($value) {
    if ($value != $this->max) {
      $this->set_modified_field('max', $this->max);
      $this->max = $value;
    }
  }

  /**
   * Get the number of decimal places for the option
   * @return integer
   */
  public function get_decimals() {
    return $this->decimals;
  }

  /**
   * Set the number of decimal places for the option
   * @param integer $value
   */
  public function set_decimals($value) {
    if ($value != $this->decimals) {
      $this->set_modified_field('decimals', $this->decimals);
      $this->decimals = $value;
    }
  }

  /**
   * Get the increment for the option
   * @return integer
   */
  public function get_increment() {
    return $this->increment;
  }

  /**
   * Set the increment for the option
   * @param integer $value
   */
  public function set_increment($value) {
    if ($value != $this->increment) {
      $this->set_modified_field('increment', $this->increment);
      $this->increment = $value;
    }
  }

  /**
   * Get the formula for the option
   * @return string
   */
  public function get_formula() {
    return $this->formula;
  }

  /**
   * Set the formula for the option
   * @param string $value
   */
  public function set_formula($value) {
    if ($value != $this->formula) {
      $this->set_modified_field('formula', $this->formula);
      $this->formula = $value;
    }
  }

  /**
   * Get the units for the option
   * @return string
   */
  public function get_units() {
    return $this->units;
  }

  /**
   * Set the units for the option
   * @param string $value
   */
  public function set_units($value) {
    if ($value != $this->units) {
      $this->set_modified_field('units', $this->units);
      $this->units = $value;
    }
  }

  /**
   * Dummy method for required settings for corrector
   * @return array
   */
  public function get_all_corrects() {
    return array();
  }

  /**
   * Dummy method for required settings for corrector
   */
  public function set_all_corrects() {
    // Do nothing
  }

  /**
   * Dummy method for required settings for corrector
   * @return string
   */
  public function get_option_formula() {
    return '';
  }

  /**
   * Dummy method for required settings for corrector
   */
  public function set_option_formula() {
    // Do nothing
  }

  /**
   * Dummy method for required settings for corrector
   * @return string
   */
  public function get_option_units() {
    return '';
  }

  /**
   * Dummy method for required settings for corrector
   */
  public function set_option_units() {
    // Do nothing
  }

  // PRIVATE / PROTECTED METHODS


  /**
   * Track the addition of a new variable.
   * @param Logger $logger
   * @param integer $option_number
   */
  protected function track_new_var($logger, $option_number) {
    if ($this->min != '') {
      $logger->track_change($this->_lang_strings['newvariable'], $this->question_id, $this->_user_id, '', $this->min . ',' . $this->max, $this->_lang_strings['variable'] . chr(64 + $option_number));
    }
  }

  /**
   * Track the addition of a new answer.
   * @param Logger $logger
   * @param integer $option_number
   */
  protected function track_new_ans($logger, $option_number) {
    if ($this->formula != '') {
      $logger->track_change($this->_lang_strings['newanswer'], $this->question_id, $this->_user_id, '', $this->formula . ',' . $this->units, $this->_lang_strings['answer'] . ' ' . $option_number);
    }
  }

  /**
   * Track the change of an option.  The message may be different in other question types so allow this method to be overridden
   * @param Logger $logger
   * @param integer $option_number
   * @param mixed $old
   * @param mixed $new
   * @param string $field
   */
  protected function track_change($logger, $option_number, $old, $new, $field) {
    $logger->track_change($this->_lang_strings['edit'] . ' ' . ucwords($field), $this->question_id, $this->_user_id, $old, $new, $this->_lang_strings['variable'] . chr(64 + $option_number));
  }

  /**
   * Track the deletion of an option
   * @param Logger $logger
   * @param integer $option_number
   */
  protected function track_delete_var($logger, $option_number) {
    $logger->track_change($this->_lang_strings['deletedvar'], $this->question_id, $this->_user_id, '', '', $this->_lang_strings['variable'] . chr(64 + $option_number));
  }

  /**
   * Track the deletion of an option
   * @param Logger $logger
   * @param integer $option_number
   */
  protected function track_delete_ans($logger, $option_number) {
    $logger->track_change($this->_lang_strings['deletedanswer'], $this->question_id, $this->_user_id, '', '', $this->_lang_strings['answer'] . ' ' . $option_number);
  }

  /**
   * Check if this is a new variable or answer by comparing the old values of the relevant fields
   * @param array $fields Fields to compare
   * @return bool
   */
  private function is_new($fields) {
    foreach ($fields as $varfield) {
      if (!isset($this->_modified_fields[$varfield]) or $this->_modified_fields[$varfield]['value'] != '') {
        return false;
      }
    }

    return true;
  }

  /**
   * Check if this is a new variable or answer by comparing the old values of the relevant fields
   * @param array $fields Fields to compare
   * @return bool
   */
  private function is_deleted($fields) {
    foreach ($fields as $varfield) {
      if (!isset($this->_modified_fields[$varfield]) or $this->_modified_fields[$varfield]['value'] == '' or $this->$varfield != '') {
        return false;
      }
    }

    return true;
  }

  /**
   * Clear the modification records for the given fields. Used when we have a new variable or answer
   * @param array $fields Fields to clear
   */
  private function clear_mods($fields) {
    foreach ($fields as $varfield) {
      if (isset($this->_modified_fields[$varfield])) {
        unset($this->_modified_fields[$varfield]);
      }
    }
  }

  /*
   * Get post parameters for option_min field
   * @param integer $option_no option number
   * @return array parsed post data
   */
  public function get_post_min($option_no) {
    // Variable defintion for calculation questions.
    // Can be a link to another variable i.e. $A,
    // a floating point or integer number i.e. 10.1,
    // a link to another questions answer or variable i.e. ans10 or var$A99,
    // a simple formula using [+,-,*,/] i.e. $A/$B
    $option['option_min' . $option_no] = param::optional(
      'option_min' . $option_no,
      null,
      param::REGEXP,
      param::FETCH_POST,
      array(
          'default' => null,
          'regexp' => '#^((\$[A-Z][0-9]*|var\$[A-Z][0-9]*|ans[0-9]*|[-]?[0-9]*[.]?[0-9]+)([+-/*]?))+$#',
      )
    );

    return $option;
  }
  
  /*
   * Get post parameters for option_max field
   * @param integer $option_no option number
   * @return array parsed post data
   */
  public function get_post_max($option_no) {
    // Variable defintion for calculation questions.
    // Can be a link to another variable i.e. $A,
    // a floating point or integer number i.e. 10.1,
    // a link to another questions answer or variable i.e. ans10 or var$A99,
    // a simple formula using [+,-,*,/] i.e. $A/$B
    $option['option_max' . $option_no] = param::optional(
      'option_max' . $option_no,
      null,
      param::REGEXP,
      param::FETCH_POST,
      array(
          'default' => null,
          'regexp' => '#^((\$[A-Z][0-9]*|var\$[A-Z][0-9]*|ans[0-9]*|[-]?[0-9]*[.]?[0-9]+)([+-/*]?))+$#',
       )
    );
    return $option;
  }
  
  /*
   * Get post parameters for option_decimals field
   * @param integer $option_no option number
   * @return array parsed post data
   */
  public function get_post_decimals($option_no) {
    $option['option_decimals' . $option_no] = param::optional(
      'option_decimals' . $option_no, null,
      param::INT, param::FETCH_POST,
      array('default' => null, 'min_range' => 0, 'max_range' => 8)
    );
    return $option;
  }
  
  /*
   * Get post parameters for option_increment field
   * @param integer $option_no option number
   * @return array parsed post data
   */
  public function get_post_increment($option_no) {
    $option['option_increment' . $option_no] = param::optional('option_increment' . $option_no, null, param::FLOAT, param::FETCH_POST);
    return $option;
  }
  
  /*
   * Get post parameters for option_formula field
   * @param integer $option_no option number
   * @return array parsed post data
   */
  public function get_post_formula($option_no) {
    $option['option_formula' . $option_no] = param::optional('option_formula' . $option_no, null, param::TEXT, param::FETCH_POST);
    return $option;
  }
  
  /*
   * Get post parameters for option_units field
   * @param integer $option_no option number
   * @return array parsed post data
   */
  public function get_post_units($option_no) {
    $option['option_units' . $option_no] = param::optional(
      'option_units' . $option_no,
      null,
      param::REGEXP, 
      param::FETCH_POST,
        array(
              'default' => '',
              'regexp' => '/^[\s\w\d\/^%-°\p{L}]*$/u',
        )
      );
    return $option;
  }
}

