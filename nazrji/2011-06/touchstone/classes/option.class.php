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
 * Main class for core question options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

require_once 'exceptions.inc.php';

Class Option {

  public $id = -1;
  public $question_id = null;
  public $text = '';
  public $media = '';
  public $media_width = '';
  public $media_height = '';
  public $correct_fback = '';
  public $incorrect_fback = '';
  public $correct = '';
  public $marks = null;
  
  private static $_fields = array('question_id', 'text', 'media', 'media_width', 'media_height', 'correct_fback', 'incorrect_fback', 'correct', 'marks');
  private $_required_fields = array('question_id', 'correct', 'marks');
  private $_mysqli = null;
  private $_data = array();
  
  /**
   * Create a new option object by either loading an existing option from the database or populating
   * properties from an associative array
   * @param mixed $data
   */
  function __construct($mysqli, $data = -1) {
    // Store the database connection reference
    $this->_mysqli = $mysqli;
    
    // Array of references to the fields.  Allows succinct use of call_user_func_array
    foreach(self::$_fields as $field) {
      $this->_data[] = &$this->$field;
    }
    
    // Check the type of $data
    if(is_array($data)) {
      // If it is an array, assume an associative array of fields for creating a new object (but not 
      // saving it to the database)
      foreach($data as $field => $val) {
        $this->$field = $val;
      }
    } elseif(is_int($data)) {
      // If it is an int use it as an ID for the database lookup
      // If it is -1 (i.e. not specified) create a new empty object
      if($data != -1) {
        $this->id = $data;
        $this->get_option();
      }
    } else {
      throw new DataTypeException('Invalid type for constructor data');
    }
  }
  
  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   */
  public function save() {
    $valid = $this->validate();
    
    if($valid === true) {
      // If $id is -1 we're inserting a new record
      
      // Otherwise we're updating an existing one
    
    } else {
      throw new ValidationException($valid);
    }
    
    return true;
  }
  
  // STATIC METHODS
  
  /**
   * Get an array with the names of the properties of this class
   * @return array Array of property names
   */
  public static function get_field_array() {
    return self::$_fields;
  }
  
  /**
   * Get a list of options for the given question
   * @param int $question_id
   * @return multitype: an array of option objects
   */
  public static function get_options($question_id) {
    $options = array();
    
    return $options;
  }
  
  /**
   * Delete the option with the given ID
   * @param int $id
   * @return bool True of false depending on success or failure of the delete operation
   */
  public static function delete($id) {
    return true;
  }
  
  // PRIVATE METHODS
  
  /**
   * Get the actual data for the option from the database
   */
  private function get_option() {
    $o_query = <<< QUERY
SELECT o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks
FROM options
WHERE id_num = ?
QUERY;
    $result = $this->_mysqli->prepare($o_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    call_user_func_array(array($result, "bind_result"), $this->_data);
    $result->fetch();
  }
  
  private function validate() {
    $rval = true;
    
    // If there are errors return an appropriate message
    $missing_fields = '';
    foreach($this->_required_fields as $req) {
      if(empty($this->$req)) $missing_fields .= $req . ',';
    }
    if($missing_fields != '') {
      $rval = 'The following required fields have not been supplied' . rtrim($missing_fields, ',');
    }
    
    return $rval;
  }
  
}

?>