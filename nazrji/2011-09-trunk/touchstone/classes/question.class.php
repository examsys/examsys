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
 * Main class for core questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

require_once 'exceptions.inc.php';

Class Question {

  public $id = -1;
  public $type = null;
  public $theme = '';
  public $scenario = '';
  public $scenario_plain = '';
  public $leadin = '';
  public $leadin_plain = '';
  public $notes = '';
  public $correct_fback = '';
  public $incorrect_fback = '';
  public $score_method = '';
  public $option_order = null;
  public $standards_setting = '';
  public $bloom = null;
  public $owner_id = null;
  public $media = '';
  public $media_width = '';
  public $media_height = '';
  public $group = '';
  public $checkout_time = null;
  public $checkout_author_id = '';
  public $created = null;
  public $last_edited = null;
  public $locked = null;
  public $deleted = null;
  public $status = null;
  public $options = array();
  
  private $_required_fields = array('type', 'leadin', 'score_method', 'option_order', 'owner_id', 'status');
  
  /**
   * Create a new question object by either loading an existing question from the database or populating
   * properties from an associative array
   * @param mixed $data
   */
  function __construct($data = -1) {
    // Check the type of $data
    
    // If it is an int use it as an ID for the database lookup
    
    // If it is an array, assume an associative array of fields for creating a new object (but not saving it to the database)
    
    // If it is -1 (i.e. not specified) create a new empty object
  }
  
  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   * @throws ValidationException
   */
  public function save() {
    $valid = $this->validate();
    
    if($valid === true) {
      // If $id is -1 we're inserting a new record
      
      // Otherwise we're updating an existing one
      
      // Remember to call save() on the options too if successful
    
    } else {
      throw new ValidationException($valid);
    }
    
    return true;
  }
  
  /**
   * Check out the question for editing
   * @param int $user_id ID of the user who is currently editing the question
   * @return boolean Success or failure of the checkout operation
   */
  public function checkout($user_id) {
    $this->checkout_author_id = $user_id;
    $this->checkout_time = date ("Y-m-d H:i:s");
    
    return $this->save();
  }
  
  /**
   * Lock the question, e.g. when a summative paper has started
   * @return boolean Success or failure of the lock operation
   */
  public function lock() {
    $this->locked = date ("Y-m-d H:i:s");
    
    return $this->save();
  }
  
  // STATIC FUNCTIONS
  
  /**
   * Get a list of questions for the given paper
   * @param int $paper_id
   * @return multitype: an array of question objects
   */
  public static function get_questions($paper_id) {
    $questions = array();
    
    return $questions;
  }
  
  /**
   * Delete the question with the given ID. Will not actually delete the question from the database, just mark it as deleted
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