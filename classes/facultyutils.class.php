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
 * Utility class for Faculty related functionality
 *
 * @author Anthony Brown, Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */


Class FacultyUtils {

  /**
   * Returns a name for a given faculty ID.
   * @param string $facultyID - The ID of the faculty to be checked
   * @param object $db        - Link to mysqli
   * @return bool|string      - False if the faculty does not exist, otherwise returns the name.
   */
  static function faculty_name_by_id($facultyID, $db) {
    $faculty_name = false;
    
    $result = $db->prepare("SELECT name FROM faculty WHERE id = ? AND deleted IS NULL");
    $result->bind_param('i', $facultyID);
    $result->execute();
    $result->store_result();
    $result->bind_result($faculty_name);
    $result->fetch();
    $result->close();

    return $faculty_name;
  }
  
  /**
   * Get the faculty id given external id
   *
   * @param string $externalid externalid of the faculty rogo id
   * @param object $db database connection
   *
   * @return int|bool id of school or false
  */
  static function get_facultyid_from_externalid($externalid, $db) {
    $result = $db->prepare("SELECT id FROM faculty WHERE externalid = ? AND deleted IS NULL");
    $result->bind_param('s', $externalid);
    $result->execute();
    $result->store_result();
    $result->bind_result($id);
    $result->fetch();
    if ($result->num_rows == 0) {
      $facultyid = false;
    } else {
        $facultyid = $id;
    }
    $result->close();
    return $facultyid;
  }
  
  /**
   * Checks if a faculty name already exists.
   * @param string $facultyname - The ID of the faculty to be checked
   * @param object $db        - Link to mysqli
   * @return bool             - True if the faculty ID already exists and is not deleted
   */
  static function facultyname_exists($facultyname, $db) {
    $result = $db->prepare("SELECT id FROM faculty WHERE name = ? AND deleted IS NULL");
    $result->bind_param('s', $facultyname);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_paperid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $exist = false;
    } else {
      $exist = true;
    }
    $result->free_result();
    $result->close();

    return $exist;
  }

  /**
   * gets faculty id by namename already exists.
   * @param string $facultyname - The ID of the faculty to be checked
   * @param object $db        - Link to mysqli
   * @return bool             - True if the faculty ID already exists and is not deleted
   */
  static function facultyid_by_name($facultyname, $db) {
    $result = $db->prepare("SELECT id FROM faculty WHERE name = ? AND deleted IS NULL");
    $result->bind_param('s', $facultyname);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_facultyid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $exist = false;
    } else {
      $exist = $tmp_facultyid;
    }
    $result->free_result();
    $result->close();

    return $exist;
  }

  /**
   * Checks if faculty code in use
   * @param string $code - The code of the faculty to be checked
   * @param object $db   - Link to mysqli
   * @return bool        - True if the faculty code already exists
   */
  static function get_facultyid_by_code($code, $db) {
    $result = $db->prepare("SELECT id FROM faculty WHERE code = ?");
    $result->bind_param('s', $code);
    $result->execute();
    $result->store_result();
    $result->bind_result($id);
    $result->fetch();
    if ($result->num_rows == 0) {
      $exist = false;
    } else {
      $exist = $id;
    }
    $result->free_result();
    $result->close();
    return $exist;
  }
  
/**
 * Creates a new faculty.
 * @param string $faculty - The name of the faculty to be added
 * @param object $db      - Link to mysqli
 * @param string $code    - The code of the faculty to be added
 * @param string $externalid- The external system id of the faculty to be added
 * @param string $externalsys - External system source
 * @return int            - The last insert number from the database
 */
  static function add_faculty($faculty, $db, $code = null, $externalid = null, $externalsys = null) {
    if (trim($faculty) == '') {
      return false;
    }
  
    $result = $db->prepare("INSERT INTO faculty(name, code, externalid, externalsys) VALUES(?, ?, ?, ?)");
    $result->bind_param('ssss', $faculty, $code, $externalid, $externalsys);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    return $db->insert_id;
  }
  
/**
 * Deletes a faculty by setting a flag.
 * @param string $facultyID - The ID of the faculty to be deleted
 * @param object $db        - Link to mysqli
 */
  static function delete_faculty($facultyID, $db) {
    if ($facultyID == '') {
      return false;
    }
  
    $result = $db->prepare("UPDATE faculty SET deleted = NOW() WHERE id = ?");
    $result->bind_param('i', $facultyID);
    $result->execute();  
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    return true;
  }
  
  /**
   * Update a faculty.
   * @param integer $id     - Faculty id in rogo
   * @param string $faculty - The name of the faculty to be added
   * @param string $code    - The code of the faculty to be added
   * @param string $externalid - External sustem id for the faculty
   * @param string $externalsys - External system source
   * @param object $db      - Link to mysqli
   * @return bool           - True on success
  */
  static function update_faculty($id, $faculty, $code, $externalid, $externalsys, $db) {
    // Check if code already in use.
    if (is_null($code)) {
        // Check if name already in use.
        $facultyid = FacultyUtils::facultyid_by_name($faculty, $db);
        if ($facultyid !== false and $facultyid != $id) {
          return false;
        }
    }
    $result = $db->prepare("UPDATE faculty SET name = ?, code = ?, externalid = ?, externalsys = ? WHERE id = ?");
    $result->bind_param('ssssi', $faculty, $code, $externalid, $externalsys, $id);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }

    return true;
  }
  
  /**
   * Get factulty details
   * @param integer $id 
   * @param mysqli $db 
   * @return array details
   */
  static function get_faculty_details_by_id($id, $db) {
    $result = $db->prepare("SELECT name, code, externalid, externalsys FROM faculty WHERE id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->store_result();
    $result->bind_result($name, $code, $externalid, $externalsys);
    $result->fetch();
    $result->close();

    return array('name' => $name, 'code' => $code, 'externalid' => $externalid, 'externalsys' => $externalsys);
  }
  
  /**
  * Get the number of schools on a faculty.
  * @param integer $id - id of the faculty
  * @param mysqli $db 
  * @return integer - number of schools in faculty
  */
  static function count_schools_in_faculty($id, $db) {
    $result = $db->prepare("SELECT count(*) FROM schools WHERE facultyID = ? AND deleted is NULL");
    $result->bind_param('i', $id);
    $result->execute();
    $result->bind_result($count);
    $result->fetch();
    $result->close();
    return $count;
  }
  
  /**
   * Compare the faculties in the external system and rogo
   * @param array $external list of external system faculties
   * @param string $sms external system
   * @param mysqli $db db connection
   * @return array list of faculties in rogo but not in external system
   */
  static function diff_external_faculties_to_internal_faculties($external, $sms, $db) {
    $result = $db->prepare("SELECT id, externalid, deleted FROM faculty WHERE externalid IS NOT NULL and externalsys = ?");
    $result->bind_param('s', $sms);
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $externalid, $deleted);
    $diff = array();
    while ($result->fetch()) {
      // Mark for delete if not found in external list.
      if(!in_array($externalid, $external)) {
        $diff[] = $externalid;
      } else {
        // Restore if deleted in Rogo but found in external list.
        if(!is_null($deleted)) {
          self::restore_faculty($db, $id);
        }
      }
    }
    $result->close();
    return $diff;
  }
  
  /**
   * Restore faculty from recycle bin
   * @param mysqli $db db connection
   * @param integer $id rogo id of faculty
   * @return boolean true on success, false otherwise
   */
  static function restore_faculty($db, $id) {
    $result = $db->prepare("UPDATE faculty set deleted = NULL where id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    return true;
  }
}
