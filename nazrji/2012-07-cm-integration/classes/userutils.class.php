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
* Utility class for user related functions
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

Class UserUtils {

  static function createUser($username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db) {
    global $cfg_encrypt_salt;
    
    if (!self::usernameExists($username, $db) and $username != '' and stristr('ps_',$username) === false) {
      $initial = explode(' ',$forname);
      $initials = '';
      foreach ($initial as $name) {
        $initials .= substr($name,0,1);
      }
      $initials = strtoupper($initials);
      $surname = self::my_ucwords(trim($surname));
      $title = self::my_ucwords(trim($title));  

      //if there is no password generate one
      if ($password == '') {
        $password =  gen_password();
      }

      //force valid value for gender or default to NULL
      if (strtolower($gender) != 'male' and strtolower($gender) != 'female') {
        $gender = NULL;
      }
      
      //add new users
      $result = $db->prepare("INSERT INTO users VALUES(?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, NULL, 0, ?, NULL)");
      $encrypt_password = encpw($cfg_encrypt_salt, $username, $password);
      $result->bind_param('ssssssssssi', $encrypt_password, $course, $surname, $initials, $title, $username, $email, $role, $forname, $gender, $year);
      $result->execute();
      $result->close();
      $userID = $db->insert_id;
      if (isset($sid) and $sid != '') {
        $result = $db->prepare("INSERT INTO sid VALUES(?, ?)");
        $result->bind_param('si', $sid, $userID);
        $result->execute();
        $result->close();
      }
      return $userID;
    }
    
    return false;
  }
  
  /**
   * Check if username exists and if so return ID.
   *
   * @param string $username username
   * @param object $db mysqli database connection
   * @return mixed user ID if exists, otherwise false
   *
   */
  static function usernameExists($username, $db) {
    $stmt = $db->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($tmp_userID);
    $stmt->fetch();
    $exists = ($stmt->num_rows == 0) ? false : $tmp_userID;
    $stmt->close();
    
    return $exists;
  }

  /**
   * Check if Student ID exists and if so return ID.
   *
   * @param string $sid Student ID
   * @param object $db mysqli database connection
   * @return mixed user ID if exists, otherwise false
   *
   */
  static function studentidExists($sid, $db) {
    $stmt = $db->prepare("SELECT userID FROM sid WHERE student_id=?");
    $stmt->bind_param('s', $sid);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($tmp_userID);
    $stmt->fetch();
    $exists = ($stmt->num_rows == 0) ? false : $tmp_userID;
    $stmt->close();
    
    return $exists;
  }
  
  static function add_staff_to_team($tmp_userID, $module, $db) {
    $stmt = $db->prepare("INSERT INTO teams VALUES (NULL, ?, ?, NULL, 'System')");
    $stmt->bind_param('si', $module, $tmp_userID);
    $stmt->execute();  
    $stmt->close();
  }

  static function clear_team_by_team_name($team_name, $db) {
    $result = $db->prepare("DELETE FROM teams WHERE name=?");
    $result->bind_param('s', $team_name);
    $result->execute();  
    $result->close();
  }
  
  static function clear_team_by_userID($tmp_userID, $db) {
    $result = $db->prepare("DELETE FROM teams WHERE memberID=?");
    $result->bind_param('i', $tmp_userID);
    $result->execute();  
    $result->close();
  }

  static function get_team_list_by_name($team_name, $db) {
    $team_members = array();
    $result = $db->prepare("SELECT memberID FROM teams WHERE name=?");
    $result->bind_param('s', $team_name);
    $result->execute();
    $result->bind_result($memberID);
    while ($result->fetch()) {
      $team_members[] = $memberID;
    }
    $result->close();

    return $team_members;
  }

  /**
   * Enrole a student on a module.
   *
   * @param int $userID ID of the student to be enroled.
   * @param string $module Module ID for the enrolement.
   * @param object $db $mysqli database connection.
   * @return bool return true if successful.
   *
   */
  static function add_student_to_module($tmp_userID, $module, $attempt, $session, $db) {
    if (UserUtils::isUserOnModule($tmp_userID, $module,$session, $db)) {
      //dont add a user to a module multiple times
      return true;
    } else {
      $result = $db->prepare("INSERT INTO student_modules VALUES(NULL, ?, ?, ?, ?, 0)");
      $result->bind_param('issi', $tmp_userID, $module, $session, $attempt);
      $result->execute();
      $result->close();
      if ($db->errno != 0) {
        return false;
      }
      return true;
    }
  } 

  static function removeUserFromModule($tmp_userID, $module, $session, $db) {
    $result = $db->prepare("DELETE FROM student_modules WHERE userID=? AND moduleid=?");
    $result->bind_param('is', $tmp_userID, $module);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    return true;
  }   
 
  static function isUserOnModule($userID, $module, $session, $db) {
    $result = $db->prepare("SELECT userID FROM student_modules WHERE userID=? AND moduleid=? AND calendar_year=?");
    $result->bind_param('iss', $userID, $module, $session);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_userID);
    $exists = ($result->num_rows > 0);
    $result->close();
    
    return $exists;
  }   
 
  static function fixcase_callback($word) { 
    $word = $word[1]; 
    $word = strtolower($word); 
          
    if ($word == "de") return $word; 
     
    $word = ucfirst($word); 
         
    if (substr($word,1,1) == "'") { 
      if (substr($word,0,1) == "D") { 
        $word = strtolower($word); 
      } 
      $next = substr($word,2,1); 
      $next = strtoupper($next); 
      $word = substr_replace($word, $next, 2, 1); 
    }
    return $word; 
  } 
  
  static function my_ucwords($s) { 
    $s = preg_replace_callback("/(\b[\w|']+\b)/s", array('UserUtils','fixcase_callback'), $s); 
    return $s;         
  }
}
?>