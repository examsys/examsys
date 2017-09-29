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
 * Class for Likert Scale questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

Class QuestionLIKERT extends QuestionEdit {
  
  protected $scale_type = '';
  protected $not_applicable = 'false';
  protected $custom_scales = array();
  public $max_options = 1;
  public $max_stems = 10;
  protected $_allow_correction = false;
  
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'scale_type', 'not_applicable', 'option_order', 'bloom', 'status');
  protected $_fields_compound = array('custom_scale');
  protected $_fields_force = array('not_applicable');
  
  
  protected $_scale_types;
  
  function __construct($mysqli, $userObj, $lang_strings, $data = null) {
    parent::__construct($mysqli, $userObj, $lang_strings, $data);

    $this->_scale_types = array(
      $this->_lang_strings['oscescales'] => array('0|1' => '0, 1', '0|1|2' => '0, 1, 2', '0|1|2|3' => '0, 1, 2, 3', '0|1|2|3|4' => '0, 1, 2, 3, 4', '0|1|2|3|4|5' => '0, 1, 2, 3, 4, 5'),
    	'3 ' . $this->_lang_strings['pointscales'] => array($this->_lang_strings['lowhigh3'] => $this->_lang_strings['lowhigh'], $this->_lang_strings['neveralways3'] => $this->_lang_strings['neveralways'], $this->_lang_strings['disagre3'] => $this->_lang_strings['disagre3point']),
    	'4 ' . $this->_lang_strings['pointscales'] => array($this->_lang_strings['lowhigh4'] => $this->_lang_strings['lowhigh'], $this->_lang_strings['neveralways4'] => $this->_lang_strings['neveralways'], $this->_lang_strings['disagre4'] => $this->_lang_strings['disagre4point']),
    	'5 ' . $this->_lang_strings['pointscales'] => array($this->_lang_strings['lowhigh5'] => $this->_lang_strings['lowhigh'], $this->_lang_strings['neveralways5'] => $this->_lang_strings['neveralways'], $this->_lang_strings['disagre5a'] => $this->_lang_strings['disagre5pointneither'], $this->_lang_strings['disagre5b'] => $this->_lang_strings['disagre5pointuncertain'], $this->_lang_strings['disagre5c'] => $this->_lang_strings['disagre5pointneutral'])
    );
    
    $this->get_all_custom_scales();
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
    $this->get_display_method();
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
    $this->set_display_method('dummy');
  }
  
  /**
   * Get whether 'not applicable' should be applied to scales for this question
   * @return string
   */
  public function get_not_applicable() {
    $this->get_display_method();
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
    $this->set_display_method('dummy');
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
   * Compound the scale items into a string and set as scale type (and hence display method) 
   * @return multitype:
   */
  public function set_all_custom_scales($value) {
    $scale = $this->get_scale_type();
    if ($scale == 'custom') {
      $this->custom_scales = $this->array_trim($value);
      $this->set_scale_type(implode('|', $this->custom_scales));
    }
  }
  
  /**
   * Get the question display method, populating pseudo-properties as we go
   * @return string
   */
  public function get_display_method() {
    if ($this->display_method != '') {
      $pos = strrpos($this->display_method, '|');
      $this->scale_type = substr($this->display_method, 0, $pos);
      $this->not_applicable = substr($this->display_method, $pos + 1);
    }
    return $this->display_method;
  }
  
  /**
   * Set the display method for the question - this is a composite of decimals, tolerance and units
   * @param unknown_type $value
   */
  public function set_display_method($value) {
    $this->display_method = $this->scale_type . '|' . $this->not_applicable;
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

  // PRIVATE FUNCTIONS
  
  private function array_trim($input) {
    $adding = false;
    $input_rev = array_reverse($input);
    $new_array = array();
    
    foreach ($input_rev as $value) {
      if (!$adding and !empty($value)) {
        $adding = true;
      }
      if ($adding) {
        array_unshift($new_array, $value);
      }
    }
    return $new_array;
  }
}
?>