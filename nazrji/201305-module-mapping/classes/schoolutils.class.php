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
* Utility class for functionality related to schools
*
* @author Anthony Brown, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/


Class SchoolUtils {

  static function add_school($facultyID, $school, $db) {
    if ($facultyID == '' or $school == '') {
      return false;
    }
  
    $result = $db->prepare("INSERT INTO schools(school, facultyID) VALUES (?, ?)");
    $result->bind_param('si', $school, $facultyID);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }

    return $db->insert_id;
  }

  static function get_school_list_by_id($db) {
    $school_list = array();

    $stmt = $db->prepare("SELECT id, school, facultyID FROM schools WHERE deleted IS NULL");
    $stmt->execute();
    $stmt->bind_result($id, $school, $faculityID);
    while ($stmt->fetch()) {
      $school_list[$id]['school'] = $school;
      $school_list[$id]['faculityID'] = $faculityID;
    }
    $stmt->close();

    return $school_list;
  }

  static function get_school_list_by_name($db) {
    $school_list = array();

    $stmt = $db->prepare("SELECT id, school FROM schools WHERE deleted IS NULL");
    $stmt->execute();
    $stmt->bind_result($id, $school);
    while ($stmt->fetch()) {
      $school_list['school'] = $id;
    }
    $stmt->close();

    return $school_list;
  }

  static function get_school_id_by_name($school_name, $db) {
    if ($school_name == '') {
      return false;
    }
    
    $id = false;

    $stmt = $db->prepare("SELECT id FROM schools WHERE deleted IS NULL and school = ?");
    $stmt->bind_param('s', $school_name);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->store_result();
    $stmt->fetch();
    $row = $stmt->num_rows;
    $stmt->close();
    //TODO current UoN Fudge for some data that doesnt follow convention should shift to saturn abstraction
    if ($row == 0) {
      $stmt = $db->prepare("SELECT id FROM schools WHERE deleted IS NULL and school = CONCAT('School of ', ?)");
      $stmt->bind_param('s', $school_name);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->store_result();
      $stmt->fetch();
      $row = $stmt->num_rows;
      $stmt->close();
      if ($row == 0) {
        $stmt = $db->prepare("SELECT id FROM schools WHERE deleted IS NULL and school = 'UNKNOWN School'");
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->store_result();
        $stmt->fetch();
        $stmt->close();
      }
    }
    
    return $id;
  }


/**
 * Get the schools a member of staff with 'Admin' rights has access to.
 * @param int $admin_userid - ID of the member of staff user
 * @param object $db        - Link to mysqli
 * @return array            - List of schools the member of staff has access to.
 */
  static function get_admin_schools($admin_userid, $db) {
    $school_list = array();

    $stmt = $db->prepare("SELECT schools_id FROM admin_access WHERE userID = ?");
    $stmt->bind_param('i', $admin_userid);
    $stmt->execute();
    $stmt->bind_result($school);
    while ($stmt->fetch()) {
      $school_list[] = $school;
    }
    $stmt->close();

    return $school_list;
  }

/**
 * Check if a school name exists in a given Faculty
 * @param int $facultyID  - ID of faculty to check
 * @param string $school  - School name to check
 * @param object $db      - Link to mysqli
 * @return bool           - True if school name already exists for the faculty
 */
  static function school_exists_in_faculty($facultyID, $school, $db) {
    $row_no = 0;
  
    $query = 'SELECT id FROM schools WHERE school = ? AND facultyID = ?';
    $stmt = $db->prepare($query);
    $stmt->bind_param('si', $school, $facultyID);
    $stmt->execute();
    $stmt->store_result();
    $row_no = $stmt->num_rows;
    $stmt->close();

    return $row_no > 0;
  }
  
/**
 * Check if a school ID exists
 * @param int $schoolID - ID of the school to check
 * @param object $db    - Link to mysqli
 * @return bool         - True if the school ID is found
 */
 static function schoolid_exists($schoolID, $db) {
    $row_no = 0;
    
    $query = 'SELECT id FROM schools WHERE id = ?';
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $schoolID);
    $stmt->execute();
    $stmt->store_result();
    $row_no = $stmt->num_rows;
    $stmt->close();

    return $row_no > 0;
  }

/**
 * Delete a school by setting a flag
 * @param int $schoolID - ID of the school to delete
 * @param object $db    - Link to mysqli
 */
 static function delete_school($schoolID, $db) {
    if ($schoolID == '') {
      return false;
    }
    
    $result = $db->prepare("UPDATE schools SET deleted = NOW() WHERE id = ?");
    $result->bind_param('i', $schoolID);
    $result->execute();  
    $result->close();
  }
}