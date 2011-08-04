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
require_once 'option.class.php';

Class Question {

  public $id = -1;
  public $type = null;
  public $theme = '';
  public $scenario = '';
  private $scenario_plain = '';
  public $leadin = '';
  private $leadin_plain = '';
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
  
  private $_fields = array('type', 'theme', 'scenario', 'scenario_plain', 'leadin', 'leadin_plain', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'option_order', 'standards_setting', 'bloom', 'owner_id', 'media', 'media_width', 'media_height', 'group', 'checkout_time', 'checkout_author_id', 'created', 'last_edited', 'locked', 'deleted', 'status');
  private $_required_fields = array('type', 'leadin', 'score_method', 'option_order', 'owner_id', 'status');
  private $_mysqli = null;
  private $_data = array();
  
  public static $types = array('blank' => 'Fill in the Blank', 'calculation' => 'calculation', 'dichotomous' => 'Dichotomous', 'extmatch' => 'Extended Matching', 'flash' => 'Flash', 'hotspot' => 'Image Hotspot', 'info' => 'Information Block', 'keyword_based' => 'Keyword Based', 'labelling' => 'Labelling', 'likert' => 'Likert Scale', 'matrix' => 'Matrix', 'mcq' => 'Multiple Choice', 'mrq' => 'Multiple Response', 'random' => 'Random', 'rank' => 'Ranking', 'sct' => 'Script COncordance', 'textbox' => 'Text Box', 'timedate' => 'Time / Date');
  
  /**
   * Create a new question object by either loading an existing question from the database or populating
   * properties from an associative array
   * @param mixed $data
   */
  function __construct($mysqli, $data = null) {
    // Store the database connection reference
    $this->_mysqli = $mysqli;
    
    // Array of references to the fields.  Allows succinct use of call_user_func_array for saving
    foreach($this->_fields as $field) {
      $this->_data[] = &$this->$field;
    }
    
    // Check the type of $data
    if(is_array($data)) {
      // If it is an array, assume an associative array of fields for creating a new object (but not 
      // saving it to the database)
      foreach($data as $field => $val) {
        $this->$field = $val;
      }
    } elseif(ctype_digit($data)) {
      // If it is an int use it as an ID for the database lookup
      $this->id = $data;
      $this->get_question();
    } elseif ($data !== null) {
      throw new DataTypeException('Invalid type for constructor data');
    }
  }
  
  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   * @throws ValidationException
   */
  public function save($clear_checkout = true) {
    $success = false;
    
    $valid = $this->validate();
    
    if($valid === true) {
      // Clear any existing checkout
      if($clear_checkout) {
        $this->checkout_author_id = null;
        $this->checkout_time = null;
      }
      
      // Make sure plain versions of scenario and leadin are up to date
      $this->get_scenario_plain();
      $this->get_leadin_plain();
      
      // If $id is -1 we're inserting a new record
      if($this->id == -1) {
        $params = array_merge(array('sssssssssssisisiississsss'), $this->_data);
        $i_query = <<< QUERY
INSERT INTO questions(q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes, correct_fback, incorrect_fback, score_method, 
q_option_order, std, bloom, ownerID, q_media, q_media_width, q_media_height, q_group, checkout_time, checkout_authorID, creation_date, 
last_edited, locked, deleted, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
QUERY;
        $result = $this->_mysqli->prepare($i_query);
        call_user_func_array (array($result,'bind_param'), $params);
        $success = $result->execute();
        if($success)  $this->id = $this->_mysqli->insert_id;
        $result->close();
      } else {
        // Otherwise we're updating an existing one
        $params = array_merge(array('sssssssssssisisiississsssi'), $this->_data, array(&$this->id));
        $this->last_edited = date("Y-m-d H:i:s");
        $u_query = <<< QUERY
UPDATE questions
SET q_type = ?, theme = ?, scenario = ?, scenario_plain = ?, leadin = ?, leadin_plain = ?, notes = ?, correct_fback = ?, incorrect_fback = ?, 
score_method = ?, q_option_order = ?, std = ?, bloom = ?, ownerID = ?, q_media = ?, q_media_width = ?, q_media_height = ?, q_group = ?, 
checkout_time = ?, checkout_authorID = ?, creation_date = ?, last_edited = ?, locked = ?, deleted = ?, status = ?
WHERE q_id = ?
QUERY;
        $result = $this->_mysqli->prepare($u_query);
        call_user_func_array (array($result,'bind_param'), $params);
        $success = $result->execute();
        $result->close();
      }
      
      if($success) {
        // Call save() on the options too if successful
        foreach($this->options as $oid => $option)
        {
          $option->save();
        }
      }
    } else {
      throw new ValidationException($valid);
    }
    
    return $success;
  }
  
  /**
   * Check out the question for editing
   * @param int $user_id ID of the user who is currently editing the question
   * @return boolean Success or failure of the checkout operation
   */
  public function checkout($user_id) {
    $success = false;
    
    $this->checkout_author_id = $user_id;
    $this->checkout_time = date ("Y-m-d H:i:s");
    
    $u_query = <<< QUERY
UPDATE questions
SET checkout_time = ?, checkout_authorID = ?
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($u_query);
    $result->bind_param('sii', $this->checkout_time, $this->checkout_author_id, $this->id);
    $success = $result->execute();
    $result->close();
    
    return $success;
  }
  
  /**
   * Lock the question, e.g. when a summative paper has started
   * @return boolean Success or failure of the lock operation
   */
  public function lock() {
    $success = false;
    
    $this->locked = date ("Y-m-d H:i:s");
    
    $u_query = <<< QUERY
UPDATE questions
SET locked = ?
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($u_query);
    $result->bind_param('si', $this->locked, $this->id);
    $success = $result->execute();
    $result->close();
    
    return $success;
  }
  
  // ACCESSORS
  
  /**
   * Get the 'plain' version of the scenario, i.e. stripped of HTML and special characters
   * @return string
   */
  public function get_scenario_plain() {
    $this->scenario_plain = trim(strip_tags($this->scenario));
    return $this->scenario_plain;
  }
  
  /**
   * Get the 'plain' version of the leadin, i.e. stripped of HTML and special characters
   * @return string
   */
  public function get_leadin_plain() {
    $this->leadin_plain = trim(strip_tags($this->leadin));
    return $this->leadin_plain;
  }
  
  
  // STATIC METHODS
  
  /**
   * Get a list of questions for the given paper
   * @param int $paper_id
   * @return multitype: an array of question objects
   */
  public static function get_questions($paper_id) {
    //TODO: Get questions
    $questions = array();
    
    return $questions;
  }
  
  /**
   * Delete the question with the given ID. Will not actually delete the question from the database, just mark 
   * it as deleted
   * @param int $id
   * @return bool True or false depending on success or failure of the delete operation
   */
  public static function delete($id) {
    $success = false;
    
    return Question::update_deletion_status($id, date ("Y-m-d H:i:s"));
  }
  
  /**
   * Restore a previously deleted question
   * @param int $id
   * @return bool True or false depending on success or failure of the restore operation
   */
  public static function restore($id) {
    $success = false;
    
    return Question::update_deletion_status($id, null);
  }
  
  /**
   * Build an array of question objects with options already in place. This will allow a bunch questions to be 
   * built up from a single query rather than requiring queries for each of the questions and their options
   * @param array $questions An array of question IDs to build
   * @return array An array of complete question objects 
   */
  public static function build($questions) {
    $qn_arr = array();
    
    return $qn_arr;
  }
  
  
  // PRIVATE METHODS
  
  /**
   * Get the actual data for the question and its options
   */
  private function get_question() {
    // Get the question
    $q_query = <<< QUERY
SELECT q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes, correct_fback, incorrect_fback, score_method, q_option_order,
 std, bloom, ownerID, q_media, q_media_width, q_media_height, q_group, checkout_time, checkout_authorID, creation_date, last_edited,
 locked, deleted, status
FROM questions
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($q_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    call_user_func_array(array($result, "bind_result"), $this->_data);
    $result->fetch();
    $result->close();
    
    // Build array of references to option data for use in call_user_func_array
    $opt_fields = Option::get_field_array();
    $opt_data = array();
    $params = array();
    $params[] = &$opt_data['id'];
    foreach($opt_fields as $field) {
      $params[] = &$opt_data[$field];
    }
    
    // Get the options
    $o_query = <<< QUERY
SELECT id_num, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks
FROM options
WHERE o_id = ?
QUERY;
    $result = $this->_mysqli->prepare($o_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    call_user_func_array(array($result, "bind_result"), $opt_data);
    // TODO: handle 'correctness' more nicely
    while($result->fetch()) {
      $this->options[$opt_data['id']] = new Option($this->_mysqli, $opt_data);
    }
  }
  
  /**
   * Validate the question object before saveing
   * @return Ambigous <boolean, string>
   */
  private function validate() {
    $rval = true;
    
    // If there are errors return an appropriate message
    $missing_fields = '';
    foreach($this->_required_fields as $req) {
      if(empty($this->$req)) $missing_fields .= $req . ',';
    }
    if($missing_fields != '') {
      $rval = 'The following required fields have not been supplied: ' . rtrim($missing_fields, ',');
    }
    
    return $rval;
  }

    /**
   * Perform delete or restore operation
   * @param int $id
   * @return bool True or false depending on success or failure of the operation
   */
  private static function update_deletion_status($id, $status) {
    $success = false;
    
    $d_query = <<< QUERY
UPDATE questions
SET deleted = ?
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($d_query);
    $result->bind_param('i', $status, $id);
    $success = $result->execute();
    $result->close();
    
    return $success;
  }
  
}

?>