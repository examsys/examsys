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
  private $modules = array();
  private $deleted;
  private $start_date;
  private $end_date;
  private $bidirectional;
  private $pass_mark;
  private $distinction_mark;
  private $owner_id;

  private $_mysqli;
  private $_user_id;
  private $_lang_strings;

  protected $_fields = array('property_id', 'paper_title', 'start_date', 'end_date', 'paper_type', 'bidirectional', 'pass_mark', 'distinction_mark', 'paper_ownerID', 'moduleID');
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
    }
    $result->close();

    return ($success !== false);
  }
}
