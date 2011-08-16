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
require_once 'touchstone_object.class.php';

Class Option extends TouchStoneObject {

  public $id = -1;
  private $question_id = null;
  private $text = '';
  private $media = '';
  private $media_width = '';
  private $media_height = '';
  private $correct_fback = '';
  private $incorrect_fback = '';
  private $correct = '';
  public $marks = null;
  
  private static $_fields = array('question_id', 'text', 'media', 'media_width', 'media_height', 'correct_fback', 'incorrect_fback', 'correct', 'marks');
  protected $_fields_editable = array('text', 'media', 'correct_fback', 'incorrect_fback', 'marks');
  private $_required_fields = array('question_id', 'correct', 'marks');
  
  private $_mysqli = null;
  private $_data = array();
  
  // Map our 'nice' property names to the database fields
  private $_field_map = array('question_id' => 'o_id', 'text' => 'option_text', 'media' => 'o_media', 'media_width' => 'o_media_width', 'media_height' => 'o_media_height', 'correct_fback' => 'feedback_right', 'incorrect_fback' => 'feedback_wrong');
  private $_pretty_names = array('question_id' => 'Question ID', 'text' => '', 'correct_fback' => 'Correct Feedback', 'incorrect_fback' => 'Incorrect Feedback', 'correct' => 'Correct Value', 'marks' => 'Marks');
  
  /**
   * Create a new option object by either loading an existing option from the database or populating
   * properties from an associative array
   * @param mixed $data
   */
  function __construct($mysqli, $user_id, $data = null) {
    // Store the database connection reference
    $this->_mysqli = $mysqli;
    $this->_user_id = $user_id;
    
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
    } elseif(ctype_digit($data)) {
      // If it is an int use it as an ID for the database lookup
      $this->id = $data;
      if (!$this->get_option()) {
        throw new DatabaseException('Error loading option data.');
      }
    } elseif ($data !== null) {
      throw new DataTypeException('Invalid option ID.');
    }
  }
  
  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   */
  public function save($option_number = 0) {
    $success = false;
    $logger = new Logger($this->_mysqli);
    
    $valid = $this->validate();
    
    if($valid === true) {
      // If $id is -1 we're inserting a new record
      if($this->id == -1) {
        $params = array_merge(array('issiisssd'), $this->_data);
        $query = <<< QUERY
INSERT INTO options(o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
QUERY;
      } else {
        // Otherwise we're updating an existing one
        $params = array_merge(array('issiisssdi'), $this->_data, array(&$this->id));
        $this->last_edited = date("Y-m-d H:i:s");
        $query = <<< QUERY
UPDATE options
SET o_id = ?, option_text = ?, o_media = ?, o_media_width = ?, o_media_height = ?, feedback_right = ?, feedback_wrong = ?, correct = ?, marks = ? 
WHERE id_num = ?
QUERY;
      }
      $result = $this->_mysqli->prepare($query);
      call_user_func_array (array($result,'bind_param'), $params);
      $result->execute();
      $success = ($result->affected_rows > 0);
      
      if($success) {
        if($this->id == -1) {
          $this->id = $this->_mysqli->insert_id;
          $logger->track_change('New Option', $this->question_id, $this->_user_id, $this->text, '', 'Option #' . $option_number);
        } else {
          // Log any changes
          foreach($this->_modified_fields as $field => $value) {
            $db_field = (in_array($field, array_keys($this->_field_map))) ? $this->_field_map[$field] : $field;
            $logger->track_change('Edit Question', $this->question_id, $this->_user_id, $value, $this->$field, $db_field);
          }
        }
      }
      $result->close();
            
      $this->_modified_fields = array();
    } else {
      throw new ValidationException($valid);
    }
    
    return $success;
  }
  
  
  // ACCESSORS
  
  /**
   * Get the ID of the question to which this option relates
   * @return string
   */
  public function get_question_id() {
    return $this->question_id;
  }

  /**
   * Get the option text
   * @return string
   */
  public function get_text() {
    return $this->text;
  }

  /**
   * Set the option text
   * @param string $value
   */
  public function set_text($value) {
    if($value != $this->text) {
      $this->set_modified_field('text', $this->text);
      $this->text = $value;
    }
  }
  
  /**
   * Get the question media as an array containing filename, width and height
   * @return array
   */
  public function get_media() {
    return array('filename' => $this->media, 'width' => $this->media_width, 'height' => $this->media_height);
  }
  
  /**
   * Set the question media as an array containing filename, width and height
   * @param mixed $value Array containing filename, width and height
   */
  public function set_media($value) {
    if($value != $this->media) {
      $this->set_modified_field('media', $this->media);
      $this->media = $value['filename'];
      $this->media_width = (empty($value['width'])) ? 0 : $value['width'];
      $this->media_height = (empty($value['height'])) ? 0 : $value['height'];
    }
  }
  
  /**
   * Get the option correct feedback
   * @return string
   */
  public function get_correct_fback() {
    return $this->correct_fback;
  }
  
  /**
   * Set the option correct feedback
   * @param string $value
   */
  public function set_correct_fback($value) {
    if($value != $this->correct_fback) {
      $this->set_modified_field('correct_fback', $this->correct_fback);
      $this->correct_fback = $value;
    }
  }
  
  /**
   * Get the option incorrect feedback
   * @return string
   */
  public function get_incorrect_fback() {
    return $this->incorrect_fback;
  }
  
  /**
   * Set the option incorrect feedback
   * @param string $value
   */
  public function set_incorrect_fback($value) {
    if($value != $this->incorrect_fback) {
      $this->set_modified_field('incorrect_fback', $this->incorrect_fback);
      $this->incorrect_fback = $value;
    }
  }
  
  /**
   * Get the option correct answer
   * @return string
   */
  public function get_correct() {
    return $this->correct;
  }
  
  /**
   * Set the option correct answer
   * @param string $value
   */
  public function set_correct($value) {
    $this->correct = $value;
  }
  
  /**
   * Get the option marks
   * @return string
   */
  public function get_marks() {
    return $this->marks;
  }
  
  /**
   * Set the option marks
   * @param string $value
   */
  public function set_marks($value) {
    if($value != $this->marks) {
      $this->set_modified_field('marks', $this->marks);
      $this->marks = $value;
    }
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
  public static function delete($mysqli, $user_id, $id, $number, $q_id) {
    $query = <<< QUERY
DELETE FROM options WHERE id_num = ?
QUERY;
    $result = $mysqli->prepare($query);
    $result->bind_param('i', $id);
    $result->execute();
    
    $success = ($result->affected_rows > 0);
    
    if($success) {
      $logger = new Logger($mysqli);
      $logger->track_change('Deleted Option', $q_id, $user_id, '', '', 'Option #' . $number);
    }
    
    return $success;
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
      if(empty($this->$req)) $missing_fields .= $this->_pretty_names[$req] . ', ';
    }
    if($missing_fields != '') {
      $rval = 'The following required fields have not been supplied: ' . rtrim($missing_fields, ', ');
    }
    
    return $rval;
  }
}

?>