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
 * Main class for core questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require_once 'exceptions.inc.php';

class Paper {

  private $id = -1;
  private $title;
  private $type;
  private $module_raw;
  private $deleted;
  private $start_date;
  private $end_date;
  private $bidirectional;
  private $pass_mark;
  private $distinction_mark;
  private $owner_id;
  private $modules = array();

  private $_mysqli;
  private $_user_id;
  private $_lang_strings;

  protected $_fields = array('id', 'title', 'start_date', 'end_date', 'type', 'bidirectional', 'pass_mark', 'distinction_mark', 'owner_id', 'module_raw');
  protected $_data = array();

  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    // Store the database connection reference and current user
    $this->_mysqli = $mysqli;
    $this->_user_id = $user_id;
    $this->_lang_strings = $lang_strings;

    // Array of references to the fields.  Allows succinct use of call_user_func_array for saving
    foreach($this->_fields as $field) {
      $this->_data[] = &$this->$field;
    }

    // TODO: error messages language specific
    // Check the type of $data
    if (is_array($data)) {
      // If it is an array, assume an associative array of fields for creating a new object (but not
      // saving it to the database)
      foreach($data as $field => $val) {
        $this->$field = $val;
      }
    } elseif (ctype_digit($data)) {
      // If it is an int use it as an ID for the database lookup
      $this->id = $data;
      if (!$this->get_paper()) {
        throw new DatabaseException('Could not connect to database.');
      }
    } elseif ($data !== null) {
      throw new DataTypeException('Invalid data');
    }
  }


  /**
   * Get the paper details from the database given an ID
   *
   * @return bool
   */
  private function get_paper() {
    $success = false;

    $p_query = <<< QUERY
SELECT property_id, paper_title, start_date, end_date, paper_type, bidirectional, pass_mark, distinction_mark, paper_ownerID, moduleID
FROM properties
WHERE property_id = ?
QUERY;
    $result = $this->_mysqli->prepare($p_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    call_user_func_array(array($result, "bind_result"), $this->_data);
    if ($result->fetch()) {
      $success = true;
      if ($result->num_rows == 0) {
        throw new RecordNotFoundException('Paper not found');
      }
    }
    $result->close();

    return ($success !== false);
  }

  /**
   * @param $bidirectional
   */
  public function set_bidirectional($bidirectional) {
    $this->bidirectional = $bidirectional;
  }

  /**
   * @return mixed
   */
  public function get_bidirectional() {
    return $this->bidirectional;
  }

  /**
   * @param $deleted
   */
  public function set_deleted($deleted) {
    $this->deleted = $deleted;
  }

  /**
   * @return mixed
   */
  public function get_deleted() {
    return $this->deleted;
  }

  /**
   * @param $distinction_mark
   */
  public function set_distinction_mark($distinction_mark) {
    $this->distinction_mark = $distinction_mark;
  }

  /**
   * @return mixed
   */
  public function get_distinction_mark() {
    return $this->distinction_mark;
  }

  /**
   * @param $end_date
   */
  public function set_end_date($end_date) {
    $this->end_date = $end_date;
  }

  /**
   * @return mixed
   */
  public function get_end_date() {
    return $this->end_date;
  }

  /**
   * @return int|null
   */
  public function get_id() {
    return $this->id;
  }

  /**
   * @param $modules
   */
  public function set_modules($modules) {
    $this->modules = $modules;
  }

  /**
   * @return array
   */
  public function get_modules() {
    if (count($this->modules) == 0 and $this->module_raw != '') {
      $this->modules = explode(',', $this->module_raw);
    }
    return $this->modules;
  }

  /**
   * @param $owner_id
   */
  public function set_owner_id($owner_id) {
    $this->owner_id = $owner_id;
  }

  /**
   * @return mixed
   */
  public function get_owner_id() {
    return $this->owner_id;
  }

  /**
   * @param $pass_mark
   */
  public function set_pass_mark($pass_mark) {
    $this->pass_mark = $pass_mark;
  }

  /**
   * @return mixed
   */
  public function get_pass_mark() {
    return $this->pass_mark;
  }

  /**
   * @param $start_date
   */
  public function set_start_date($start_date) {
    $this->start_date = $start_date;
  }

  /**
   * @return mixed
   */
  public function get_start_date() {
    return $this->start_date;
  }

  /**
   * @param $title
   */
  public function set_title($title) {
    $this->title = $title;
  }

  /**
   * @return mixed
   */
  public function get_title() {
    return $this->title;
  }

  /**
   * @param $type
   */
  public function set_type($type) {
    $this->type = $type;
  }

  /**
   * @return mixed
   */
  public function get_type() {
    return $this->type;
  }
}
