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
* Utility class for installer related functionality
* 
* @author Anthony Brown, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/


Class SchoolUtils {
 
  static function addSchool($facultyID, $school, $db) {
   
    $result = $db->prepare("INSERT INTO schools(school, facultyID) VALUES (?, ?)");
    $result->bind_param('si', $school, $facultyID);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    
    return $db->insert_id;
  }  
  
  static function getAdminSchools($admin_userid, $db) {
    $school_list = array();
    
    $stmt = $db->prepare("SELECT schools_id FROM admin_access WHERE userID=?");
    $stmt->bind_param('i', $admin_userid);
    $stmt->execute();
    $stmt->bind_result($school);
    while ($stmt->fetch()) {
      $school_list[] = $school;
    }
    $stmt->close();
  
    return $school_list;
  }
}