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
 * Utility class for course related functionality.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */


Class CourseUtils {

  /**
   * Check for already existing and then add new course data into the database.
   *
   * @param integer $schoolid ID of the school the course belongs to
   * @param string $name code of the course e.g. B140
   * @param string $description a title for the course e.g. Neuroscience BSc
   * @param integer $externalid external system id for the course
   * @param integer $externalsys external system
   * @param object $db database connection
   *
   * @return integer new course id
   */
  static function add_course($schoolid, $name, $description, $externalid, $externalsys, $db) {

    if ($name == '') {
      return false;
    }
    if (CourseUtils::course_exists($name, $db) === true) {
      return false;
    }

    if (!is_int($schoolid)) {
      //school name given not school id so convert
      $schoolid = SchoolUtils::get_school_id_by_name($schoolid, $db);
      if (!$schoolid) {
        return false;
      }
    }

    $result = $db->prepare("INSERT INTO courses (name, description, schoolid, externalid, externalsys) VALUES (?, ?, ?, ?, ?)");
    $result->bind_param('ssiss', $name, $description, $schoolid, $externalid, $externalsys);
    $result->execute();
    $result->close();

    if ($db->errno != 0) {
      return false;
    }

    return $db->insert_id;
  }

  /**
   * Deletes an existing course.
   *
   * @param string $name code of the course e.g. B140
   * @param object $db database connection
   *
   * @return bool depending on  success
   */
  static function delete_course($name, $db) {
    if (trim($name) == '') {
      return false;
    }

    $result = $db->prepare("DELETE FROM courses WHERE name = ? AND deleted IS NULL LIMIT 1");
    $result->bind_param('s', $name);
    $result->execute();
    $result->close();

    if ($db->errno != 0) {
      return false;
    }

    return true;
  }

  /**
   * Deletes an existing course.
   * @param integer $id
   * @param object $db database connection
   *
   * @return bool depending on  success
   */
  static function delete_course_by_id($id, $db) {
    $result = $db->prepare("UPDATE courses SET deleted = NOW() where id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->close();

    if ($db->errno != 0) {
      return false;
    }

    return true;
  }

  /**
   * Check to see if a course already exists.
   *
   * @param string $name name of the course to check
   * @param object $db database connection
   *
   * @return bool false=course does not exists, true=course exist
   */
  static function course_exists($name, $db) {
    // Check for unique course
    $exists = true;

    $result = $db->prepare("SELECT id FROM courses WHERE name = ? AND deleted IS NULL");
    $result->bind_param('s', $name);
    $result->execute();
    $result->store_result();
    if ($result->num_rows == 0) {
      $exists = false;
    }
    $result->free_result();
    $result->close();

    return $exists;
  }

  static function courseid_exists($courseID, $db) {
    $result = $db->prepare("SELECT id FROM courses WHERE id = ? AND deleted IS NULL");
    $result->bind_param('i', $courseID);
    $result->execute();
    $result->store_result();
    if ($result->num_rows == 0) {
      $exist = false;
    } else {
      $exist = true;
    }
    $result->free_result();
    $result->close();

    return $exist;
  }

  static function get_course_details_by_name($name, $db) {
    $result = $db->prepare("SELECT description, deleted, schoolid FROM courses WHERE name = ? LIMIT 1");
    $result->bind_param('s', $name);
    $result->execute();
    $result->store_result();
    $result->bind_result($description, $deleted, $schoolid);
    if ($result->num_rows == 0) {
      $details = false;
    } else {
      $result->fetch();
      $details = array('description'=>$description, 'deleted'=>$deleted, 'schoolid'=>$schoolid);
    }
    $result->close();

    return $details;
  }

  /**
   * Returns a name for a given course ID.
   * @param string $courseID - The ID of the course to be checked
   * @param object $db        - Link to mysqli
   * @return bool|string      - False if the course does not exist, otherwise returns the name.
   */
  static function get_course_name_by_id($courseID, $db) {
    $course_name = false;

    $result = $db->prepare("SELECT name FROM courses WHERE id = ? AND deleted IS NULL");
    $result->bind_param('i', $courseID);
    $result->execute();
    $result->store_result();
    $result->bind_result($course_name);
    $result->fetch();
    $result->close();

    return $course_name;
  }


  /**
   * Get course details
   * @param integer $id
   * @param mysqli $db
   * @return array details
   */
  static function get_course_details_by_id($id, $db) {
    $result = $db->prepare("SELECT name, description, deleted, schoolid, externalid, externalsys FROM courses WHERE id = ? LIMIT 1");
    $result->bind_param('i', $id);
    $result->execute();
    $result->store_result();
    $result->bind_result($name, $description, $deleted, $schoolid, $externalid, $externalsys);
    if ($result->num_rows == 0) {
      $details = false;
    } else {
      $result->fetch();
      $details = array('name'=>$name, 'description'=>$description, 'deleted'=>$deleted, 'schoolid'=>$schoolid, 'externalid'=>$externalid, 'externalsys' => $externalsys);
    }
    $result->close();

    return $details;
  }

 /**
  * Get the number of users on a course.
  * @param string $name - name of the course
  * @param mysqli $db
  * @return
  */
  static function count_users_on_course($name, $db) {
    $result = $db->prepare("SELECT count(*) FROM users WHERE grade = ?");
    $result->bind_param('s', $name);
    $result->execute();
    $result->bind_result($count);
    $result->fetch();
    $result->close();
    return $count;
  }

  /**
   * Update new course.
   *
   * @param integer $id Course id.
   * @param integer $schoolid ID of the school the course belongs to
   * @param string $name code of the course e.g. B140
   * @param string $description a title for the course e.g. Neuroscience BSc
   * @param integer $externalid external system id for course
   * @param integer $externalsys external system source
   * @param mysqli $db
   *
   * @return bool success response
   */
  static function update_course($id, $schoolid, $name, $description, $externalid, $externalsys, $db) {
    // Check if name already in use.
    $courseid = CourseUtils::get_course_id($name, $db);
    if ($courseid !== false and $courseid != $id) {
      return false;
    }
    $result = $db->prepare("UPDATE courses set name = ?, description = ?, schoolid = ?, externalid = ?, externalsys = ? WHERE id = ?");
    $result->bind_param('ssissi', $name, $description, $schoolid, $externalid, $externalsys, $id);
    $result->execute();
    $result->close();

    if ($db->errno != 0) {
        return false;
    }

    return true;
  }

  /**
   * Get the course id if it exists
   *
   * @param string $name name of the course to check
   * @param object $db database connection
   *
   * @return int|bool id of course or false
  */
  static function get_course_id($name, $db) {
    $result = $db->prepare("SELECT id FROM courses WHERE name = ? AND deleted IS NULL");
    $result->bind_param('s', $name);
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
   * Get the course id given external id
   *
   * @param string $externalid externalid of the course rogo id
   * @param object $db database connection
   *
   * @return int|bool id of course or false
  */
  static function get_courseid_from_externalid($externalid, $db) {
    $result = $db->prepare("SELECT id FROM courses WHERE externalid = ? AND deleted IS NULL");
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
   * Compare the courses in the external system and rogo
   * @param array $external list of external system courses
   * @param string $sms the external student management system that is the source of the courses
   * @param mysqli $db db connection
   * @return array list of courses in rogo but not in external system
   */
  static function diff_external_courses_to_internal_courses($external, $sms, $db) {
    $result = $db->prepare("SELECT id, externalid, deleted FROM courses WHERE externalid IS NOT NULL AND externalsys = ?");
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
          self::restore_course($db, $id);
        }
      }
    }
    $result->close();
    return $diff;
  }
  
  /**
   * Restore course from recycle bin
   * @param mysqli $db db connection
   * @param integer $id rogo id of course
   * @return boolean true on success, false otherwise
   */
  static function restore_course($db, $id) {
    $result = $db->prepare("UPDATE courses set deleted = NULL where id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    return true;
  }
}
?>
