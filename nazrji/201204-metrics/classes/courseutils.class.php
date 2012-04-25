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

  static function addCourse($schoolid, $name, $description, $db) {
    
    if (CourseUtils::courseExists($name, $db) === false) {
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
  
  static function courseExists($name, $db) {
    // Check for unique course
    $unique_courseid = false;
    
    $result = $db->prepare("SELECT id FROM courses WHERE name=?");
    $result->bind_param('s', $name);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_courseid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $unique_courseid = true;
    }
    $result->free_result();
    $result->close();
    
    return $unique_courseid;
  }
  
}
?>