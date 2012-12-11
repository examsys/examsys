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
* Class to manage logging changes to questions etc.
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once 'exceptions.inc.php';

Class Logger {
  private $_mysqli;
  
  /**
   * Create a new logger object
   * @param db_link $mysqli Reference to database connection
   */
  function __construct($mysqli) {
    $this->_mysqli = $mysqli;
  }
  
  /**
   * Save a change to the change log table
   * @param string $message Log message describing the change
   * @param integer $object_id ID of object to which the change applies
   * @param integer $user_id ID of user making the change
   * @param string $orig_val Original value of the changed field
   * @param string $new_val New value of the changed field
   * @param string $part Scope of change
   * @return boolean Success or failure of the database operation
   */
  public function track_change($message, $object_id, $user_id, $orig_val, $new_val, $part) {
    $success = true;

    if ($object_id > 0) {

      if (is_array($orig_val)) $orig_val = implode(',',$orig_val);
      if (is_array($new_val)) $new_val = implode(',',$new_val);

      $query = <<< QUERY
INSERT INTO track_changes(type, typeID, editor, old, new, changed, part)
VALUES (?,?,?,?,?,NOW(),?)
QUERY;
      $result = $this->_mysqli->prepare($query);
      $result->bind_param('siisss', $message, $object_id, $user_id, $orig_val, $new_val, $part);
      $success = $result->execute();
      $result->close();
    }

    return $success;
  }
  
  /**
   * Enter description here ...
   * @param string $message Log message describing the change
   * @param integer $object_id ID of object to which the change applies
   * @param integer $user_id ID of user making the change
   * @param string $orig_val Original value of the changed field
   * @param string $new_val New value of the changed field
   * @param string $part Scope of change
   * @param boolean $changes Indication of whether there are changes to the system. Set to true if we have logged a change here, otherwise unaltered
   * @return boolean Success or failure of the database operation
   */
  public function check_and_track_change($message, $object_id, $user_id, $orig_val, $new_val, $part, &$changes) {
    $success = true;
    
    if ($orig_val != $new_val) {
      $success = $this->track_change($message, $object_id, $user_id, $orig_val, $new_val, $part);
      $changes = true;
    }
    
    return $success;
  }
  
  
}