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
  
  private $_required_fields = array('question_id', 'correct', 'marks');
  
  /**
   * Create a new option object by either loading an existing option from the database or populating
   * properties from an associative array
   * @param mixed $data
   */
  function __construct($data = -1) {
    // Check the type of $data
    
    // If it is an int use it as an ID for the database lookup
    
    // If it is an array, assume an associative array of fields for creating a new object (but not saving it to the database
    
    // If it is -1 (i.e. not specified) create a new empty object
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
  
  // STATIC FUNCTIONS
  
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
  
  // PRIVATE FUNCTIONS
  
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