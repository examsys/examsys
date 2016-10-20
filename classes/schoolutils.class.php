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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/


Class SchoolUtils {

    /**
     * Adds a new school to the 'schools' table and returns its new ID.
     * @param int $facultyID    - ID of the faculty to which the new school belongs.
     * @param string $school    - Name of the new school
     * @param object $db        - Link to mysqli
     * @param string $code      - code of the new school
     * @param string $external id - external system id of the new school
     * @param string $externalsys - external system source
     *
     * @return int              - The ID of the school.
     */
    static function add_school($facultyID, $school, $db, $code = null, $externalid = null, $externalsys = null) {
        if ($facultyID === '' or $school === '') {
          return false;
        }
        $result = $db->prepare("INSERT INTO schools(school, facultyID, code, externalid, externalsys) VALUES (?, ?, ?, ?, ?)");
        $result->bind_param('sisss', $school, $facultyID, $code, $externalid, $externalsys);
        $result->execute();
        $result->close();
        if ($db->errno != 0) {
          return false;
        }

        return $db->insert_id;
    }

    /**
     * Returns an array of schools (that are not deleted).
     * @param object $db        - Link to mysqli
     *
     * @return array            - An array of schools keyed by ID and holding school name and faculty ID.
     */
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

    /**
     * Returns the ID of a school from a provided name.
     * @param int $school_name  - Name of the school to be looked up.
     * @param object $db        - Link to mysqli
     *
     * @return int|bool              - ID of the school, or false if non-existant.
     */
    static function get_school_id_by_name($school_name, $db) {
        if ($school_name == '') {
          return false;
        }

        $stmt = $db->prepare("SELECT id FROM schools WHERE deleted IS NULL and school = ?");
        $stmt->bind_param('s', $school_name);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id);
        $stmt->fetch();
        if ($stmt->num_rows == 0) {
          $stmt->close();
          return false;
        }
        $stmt->close();
        return $id;
    }

    /**
     * Get the school id given external id
     *
     * @param string $externalid externalid of the school rogo id
     * @param object $db database connection
     *
     * @return int|bool id of school or false
    */
    static function get_schoolid_from_externalid($externalid, $db) {
        $result = $db->prepare("SELECT id FROM schools WHERE externalid = ? AND deleted IS NULL");
        $result->bind_param('s', $externalid);
        $result->execute();
        $result->store_result();
        $result->bind_result($id);
        $result->fetch();
        if ($result->num_rows == 0) {
            $result->close();
            return false;
        }
        $result->close();
        return $id;
    }
  
    /**
     * Get the schools a member of staff with 'Admin' rights has access to.
     * @param int $admin_userid - ID of the member of staff user
     * @param object $db        - Link to mysqli
     *
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
     *
     * @return bool           - True if school name already exists for the faculty
     */
    static function school_exists_in_faculty($facultyID, $school, $db) {
        $row_no = 0;

        $query = 'SELECT id FROM schools WHERE school = ? AND facultyID = ? AND deleted IS NULL';
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
     *
     * @return bool         - True if the school ID is found
     */
    static function schoolid_exists($schoolID, $db) {
        $row_no = 0;

        $query = 'SELECT id FROM schools WHERE id = ? AND deleted IS NULL';
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $schoolID);
        $stmt->execute();
        $stmt->store_result();
        $row_no = $stmt->num_rows;
        $stmt->close();

        return $row_no > 0;
    }

    /**
     * Check if a school name already exists
     * @param int $school   - Name of the school to check
     * @param object $db    - Link to mysqli
     *
     * @return bool         - True if the school name is found
     */
    static function school_name_exists($school, $db) {
        $schoolID = 0;
        $row_no = 0;

        $stmt = $db->prepare('SELECT id FROM schools WHERE school = ? AND deleted IS NULL');
        $stmt->bind_param('s', $school);
        $stmt->execute();
        $stmt->bind_result($schoolID);
        $stmt->store_result();
        $stmt->fetch();
        $row_no = $stmt->num_rows;
        $stmt->close();

        if ($row_no > 0) {
            return $schoolID;
        } else {
            return false;
        }
    }
    
    /**
     * Get school id by code
     * @param string $code   - Code of the school to check
     * @param object $db    - Link to mysqli
     *
     * @return int|bool         - id of school or false if not found
     */
    static function get_schoolid_by_code($code, $db) {
        $stmt = $db->prepare('SELECT id FROM schools WHERE code = ?');
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->store_result();
        $stmt->fetch();
        if ($stmt->num_rows == 0) {
            $schoolid = false;
        } else {
            $schoolid = $id;
        }
        $stmt->close();
        return $schoolid;
    }

    static function get_school_faculty($schoolID, $db) {
        $school_name = false;

        $stmt = $db->prepare('SELECT school FROM schools WHERE id = ? AND deleted IS NULL');
        $stmt->bind_param('i', $schoolID);
        $stmt->execute();
        $stmt->bind_result($school_name);
        $stmt->store_result();
        $stmt->fetch();
        $stmt->close();

        return $school_name;
    }

    /**
     * Delete a school by setting a flag
     * @param int $schoolID - ID of the school to delete
     * @param object $db    - Link to mysqli
     *
     * @return bool         - Return false if no schoolID is passed.
     */
     static function delete_school($schoolID, $db) {
        if ($schoolID == '') {
          return false;
        }

        $result = $db->prepare("UPDATE schools SET deleted = NOW() WHERE id = ?");
        $result->bind_param('i', $schoolID);
        $result->execute();
        $result->close();
        if ($db->errno != 0) {
            return false;
        }
        return true;
      }
      
    /**
     * Updates a school
     * @param integer $id  - School id in rogo.
     * @param int $facultyID - ID of the faculty to which the new school belongs.
     * @param string $school - Name of the new school
     * @param string $externalid - External system id for the faculty
     * @param string $externalsys - External system source
     * @param object $db - Link to mysqli
     * @param string $code      - code of the new school
     *
     * @return bool - true on success
     */
    static function update_school($id, $facultyID, $school, $code, $externalid, $externalsys, $db) {
        if ($facultyID === '' or $school === '') {
          return false;
        }
        if (is_null($code)) {
          $schoolID = SchoolUtils::school_name_exists($school, $db);
          // Do not update if school name is in use, unless we are updating that school.
          if ($schoolID !== false and $schoolID != $id) {
            return false;
          }
        }

        $result = $db->prepare("UPDATE schools set school = ?, facultyID = ?, code = ?, externalid = ?, externalsys= ? where id = ?");
        $result->bind_param('sisssi', $school, $facultyID, $code, $externalid, $externalsys, $id);
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
  static function get_school_details_by_id($id, $db) {
    $result = $db->prepare("SELECT school, code, facultyID, externalid, externalsys FROM schools WHERE id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->store_result();
    $result->bind_result($name, $code, $faculty, $externalid, $externalsys);
    $result->fetch();
    $result->close();

    return array('name' => $name, 'faculty' => $faculty, 'code' => $code, 'externalid' => $externalid, 'externalsys' => $externalsys);
  }
  
  /**
   * Check if school contains modules or courses
   * @param integer $id school id
   * @param mysqli $db 
   * @return bool true if school is in use
   */
  static function school_in_use($id, $db) {
    $result = $db->prepare("SELECT NULL FROM courses WHERE schoolid = ? AND deleted is NULL
        UNION SELECT NULL FROM modules WHERE schoolid = ? AND mod_deleted is NULL");
    $result->bind_param('ii', $id, $id);
    $result->execute();
    $result->store_result();
    if ($result->num_rows > 0) {
        $result->close();
        return true;
    }
    $result->close();
    return false;
  }
  
  /**
   * Generate a school id based on name and faculty.
   * @param string $school - school name
   * @param string $faculty - faculty name
   * @param mysqli $db
   * @return integer|bool - new school id or false on error
  */
  static function generate_school_id($school, $faculty, $db) {
    $facultyid = FacultyUtils::facultyid_by_name($faculty, $db);
    if (!$facultyid) {
        // Add new faculty.
        $facultyid = FacultyUtils::add_faculty($faculty, $db);
    }
    // Add new school to faculty.
    if ($facultyid) {
        $schoolid = SchoolUtils::add_school($facultyid, $school, $db);
    } else {
        return false;
    }
    return $schoolid;
  }
  
  /**
   * Compare the schools in the external system and rogo
   * @param array $external list of external system schools
   * @param string $sms list external system syncing schools
   * @param mysqli $db db connection
   * @return array list of schools in rogo but not in external system
   */
  static function diff_external_schools_to_internal_schools($external, $sms, $db) {
    $result = $db->prepare("SELECT id, externalid, deleted FROM schools WHERE externalid IS NOT NULL and externalsys = ?");
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
          self::restore_school($db, $id);
        }
      }
    }
    $result->close();
    return $diff;
  }
  /**
   * Restore school from recycle bin
   * @param mysqli $db db connection
   * @param integer $id rogo id of school
   * @return boolean true on success, false otherwise
   */
  static function restore_school($db, $id) {
    $result = $db->prepare("UPDATE schools set deleted = NULL where id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    return true;
  }
}