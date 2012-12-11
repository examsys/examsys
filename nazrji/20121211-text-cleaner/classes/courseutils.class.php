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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/


Class CourseUtils {

  /**
   * Check for already existing and then add new course data into the database.
   * @param integer $schoolid ID of the school the course belongs to
   * @param string $name code of the course e.g. B140
   * @param string $description a title for the course e.g. Neuroscience BSc
   * @param object $db database connection
   * @return bool depending on insert success
   */
  static function add_course($schoolid, $name, $description, $db) {
    
    if (CourseUtils::course_exists($name, $db) === true) {
      return false;
    }
    
    $result = $db->prepare("INSERT INTO courses VALUES (NULL, ?, ?, NULL, ?)");
    $result->bind_param('ssi', $name, $description, $schoolid);
    $result->execute();  
    $result->close();
    
    if ($db->errno != 0) {
      return false;
    }
    
    return true;
  }

  /**
   * Deletes an existing course.
   * @param string $name code of the course e.g. B140
   * @param object $db database connection
   * @return bool depending on  success
   */
  static function delete_course($name, $db) {
    
    $result = $db->prepare("DELETE FROM courses WHERE name = ? limit 1");
    $result->bind_param('s', $name);
    $result->execute();  
    $result->close();
    
    if ($db->errno != 0) {
      return false;
    }
    
    return true;
  }
  
  /**
   * Check to see if a course already exists.
   * @param string $name name of the course to check
   * @param object $db database connection
   * @return bool false=course does not exists, true=course exist
   */
  static function course_exists($name, $db) {
    // Check for unique course
    $unique_courseid = false;
    
    $result = $db->prepare("SELECT id FROM courses WHERE name=?");
    $result->bind_param('s', $name);
    $result->execute();
    $result->store_result();
    if ($result->num_rows > 0) {
      $unique_courseid = true;
    }
    $result->free_result();
    $result->close();
    
    return $unique_courseid;
  }
  
}
?>