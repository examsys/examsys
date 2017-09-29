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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
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

      if (is_array($orig_val)) $orig_val = implode(',', $orig_val);
      if (is_array($new_val)) $new_val = implode(',', $new_val);

      $query = "INSERT INTO track_changes(type, typeID, editor, old, new, changed, part) VALUES (?,?,?,?,?,NOW(),?)";

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

  /**
   * Get all the changes from the log table for a given type of object
   * @param  string $type      The type of object we want to examine
   * @param  integer $typeID   ID of a particular object of type $type
   * @param  mixed  $callbacks An array of callbacks that may be triggered for a
   *                           particular 'part' or type of change in the format
   *                           array(<part name> => <callback>)
   * @return array             The list of changes
   */
  public function get_changes($type, $typeID, $callbacks = '') {
    $change_data = array();

    $query = 'SELECT title, initials, surname, old, new, part, UNIX_TIMESTAMP(changed) AS changed FROM track_changes, users WHERE track_changes.editor = users.id AND type = ? AND typeID = ? ORDER BY changed desc';
    $result = $this->_mysqli->prepare($query);
    $result->bind_param('si', $type, $typeID);
    $result->execute();
    $result->bind_result($title, $initials, $surname, $old, $new, $part, $changed);
    while ($result->fetch()) {
      $change_data[] = array('title'=>$title, 'initials'=>$initials, 'surname'=>$surname, 'old'=>$old, 'new'=>$new, 'part'=>$part, 'date'=>$changed);

      // Fire callback if defined for this part type
      if (is_array($callbacks) and isset($callbacks[$part])) {
        call_user_func($callbacks[$part], $old, $new);
      }
    }
    $result->close();

    return $change_data;
  }

  /**
   * Record that a user tried to access a page and tried to was denied access.
   *
   * @param int $user_id The internal rogo id of the user
   * @param string $title The type of error
   * @param string $msg Details about why the user was denied access
   */
  public function record_access_denied($user_id, $title, $msg) {
    $current_address = NetworkUtils::get_client_address();

    $configObject = Config::get_instance();

    $path = str_replace($configObject->get('cfg_web_root'), '', $_SERVER['SCRIPT_FILENAME']);

    if (isset($_SERVER['QUERY_STRING'])) {
      // The page has a query part for it's URL.
      $page = $path . '?'. $_SERVER['QUERY_STRING'];
    }

    $result = $this->_mysqli->prepare('INSERT INTO denied_log VALUES(NULL, ?, NOW(), ?, ?, ?, ?)');
    $result->bind_param('issss', $user_id, $current_address, $page, $title, $msg);
    $result->execute();
    $result->close();
  }

  public function record_access($user_id, $type, $page) {
    $current_address = NetworkUtils::get_client_address();

    $result = $this->_mysqli->prepare('INSERT INTO access_log VALUES(NULL, ?, ?, NOW(), ?, ?)');
    $result->bind_param('isss', $user_id, $type, $current_address, $page);
    $result->execute();
    $result->close();
  }

  /**
   * Record application warning
   * @param integer $userid id of user that had the error
   * @param string $type type of error
   * @param string $errorstring the error
   * @param string $errorfile the file
   * @param integer $errorline the line number
   * @param array|string $variables variables to log
   */
  public function record_application_warning($userid, $type, $errorstring, $errorfile, $errorline, $variables = '') {
    if ($variables != '' and !is_string($variables)) {
        $variables = base64_encode(serialize($variables));
    }
    $log_error = $this->_mysqli->prepare("INSERT INTO sys_errors (id, occurred, userID, auth_user, errtype, errstr, errfile, errline, variables) 
        VALUES (NULL, NOW(), ?, ?, ?, ?, ?, ?, ?)");
    $errortype = 'Application Warning';
    $log_error->bind_param('issssss', $userid, $type, $errortype, $errorstring, $errorfile, $errorline, $variables);
    $log_error->execute();
    $log_error->close();
  }
}