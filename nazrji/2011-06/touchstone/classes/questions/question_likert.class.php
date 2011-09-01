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
 * Class for Likert Scale questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionLIKERT extends Question {
  
  protected $scale_type = '';
  protected $not_applicable = 'false';
  protected $custom_scales = array();
  public $max_stems = 10;
  
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'scale_type', 'not_applicable', 'option_order', 'bloom', 'status');
  protected $_fields_compound = array('custom_scale');
  protected $_fields_force = array('not_applicable');
  
  
  protected $_scale_types = array(
  	'OSCE Stations Scales' => array('0|1' => '0, 1', '0|1|2' => '0, 1, 2', 'Fail|Borderline|Pass' => 'Fail, Borderline, Pass'),
  	'3 Point Scales' => array('Low||High' => 'Low to High', 'Never||Always' => 'Never to Always', 'Disagree|Neutral|Agree' => 'Disagree, Neutral, Agree'),
  	'4 Point Scales' => array('Low|||High' => 'Low to High', 'Never|||Always' => 'Never to Always', 'Strongly<br />Disagree|Disagree|Agree|Strongly<br />Agree' => 'Strongly Disagree, Disagree, Agree, Strongly Agree'),
  	'5 Point Scales' => array('Low||||High' => 'Low to High', 'Never||||Always' => 'Never to Always', 'Strongly<br />Disagree|Disagree|Neither Disagree<br />nor Agree|Agree|Strongly<br />Agree' => 'Strongly Disagree, Disagree, Neither Disagree nor Agree, Agree, Strongly Agree', 'Strongly<br />Disagree|Disagree|Uncertain|Agree|Strongly<br />Agree' => 'Strongly Disagree, Disagree, Uncertain, Agree, Strongly Agree', 'Strongly<br />Disagree|Disagree|Neutral|Agree|Strongly<br />Agree' => 'Strongly Disagree, Disagree, Neutral, Agree, Strongly Agree')
  );
  
  function __construct($mysqli, $user_id, $data = null) {
    parent::__construct($mysqli, $user_id, $data);

    $this->get_all_custom_scales();
  }
  
  /**
   * Does this question type allow changes to the correct answer after it is locked?
   * @return boolean
   */
  public function allow_correction() {
    return true;
  }
  
  // ACCESSORS
  
  /**
   * Get the range of available scale types for the question
   * @return multitype  
   */
  public function get_scale_types() {
    return $this->_scale_types;
  }
  
  /**
   * Get the question scale type
   * @return string
   */
  public function get_scale_type() {
    $this->get_score_method();
    return $this->scale_type;
  }

  /**
   * Set the scale type for the question
   * @param unknown_type $value
   */
  public function set_scale_type($value) {
    if ($value != $this->get_scale_type()) {
      $this->set_modified_field('scale_type', $this->scale_type);
      $this->scale_type = $value;
    }
    $this->set_score_method();
  }
  
  /**
   * Get whether 'not applicable' should be applied to scales for this question
   * @return string
   */
  public function get_not_applicable() {
    $this->get_score_method();
    return $this->not_applicable;
  }

  /**
   * Set whether 'not applicable' should be applied to scales for this question
   * @param unknown_type $value
   */
  public function set_not_applicable($value) {
    $value = ($value === 'on') ? 'true' : 'false';
    if ($value != $this->get_not_applicable()) {
      $this->set_modified_field('not_applicable', $this->not_applicable);
      $this->not_applicable = $value;
    }
    $this->set_score_method();
  }
  
  /**
   * Get the custom scale values for this question. This is the same as scale_type if set. Compound field is required to
   * allow values to be saved using the same model as other question types
   * @return multitype:string
   */
  public function get_all_custom_scales() {
    $scale = $this->get_scale_type();  

    if ($scale != 'custom' and !$this->multi_array_key_exists($scale, $this->_scale_types)) {  
      $this->custom_scales = explode('|', $scale);
    }
    return $this->custom_scales;
  }
  
  /**
   * Compound the scale items into a string and set as scale type (and hence score method) 
   * @return multitype:
   */
  public function set_all_custom_scales($value) {
    $scale = $this->get_scale_type();
    if ($scale == 'custom') {
      $this->custom_scales = $value;
      $this->set_scale_type(implode('|', $value));
    }
  }
  
  /**
   * Get the question score method, populating pseudo-properties as we go
   * @return string
   */
  public function get_score_method() {
    if ($this->score_method != '') {
      $pos = strrpos($this->score_method, '|');
      $this->scale_type = substr($this->score_method, 0, $pos);
      $this->not_applicable = substr($this->score_method, $pos + 1);
    }
    return $this->score_method;
  }
  
  /**
   * Set the score method for the question - this is a composite of decimals, tolerance and units
   * @param unknown_type $value
   */
  public function set_score_method($value=-1) {
    $this->score_method = $this->scale_type . '|' . $this->not_applicable;
  }

	/**
	 * multi_array_key_exists function.
	 *
	 * @param mixed $needle The key you want to check for
	 * @param mixed $haystack The array you want to search
	 * @return bool
	 */
  private function multi_array_key_exists($needle, $haystack) {
    foreach ($haystack as $key => $value) {
      if ($needle == $key) return true;
       
      if (is_array($value)) {
        if ($this->multi_array_key_exists($needle, $value) == true) {
          return true;
        } else {
          continue;
        }
      }
    }   
    return false;
  }
}

