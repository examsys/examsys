<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Utility class for user related functions
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once ($_SERVER['DOCUMENT_ROOT'] . '/touchstone/classes/passwordutils.class.php');

Class UserUtils {

  static function createUser($username, $password, $title, $forname, $surname, $email, $degree, $faculty, $gender, $year, $role, $db) {
    
    if (!self::usernameExists($username, $db) and $username != '' and stristr('ps_',$username) === false) {
      $initial = explode(' ',$forname);
      $initials = '';
      foreach ($initial as $name) {
        $initials .= substr($name,0,1);
      }
      $initials = strtoupper($initials);
      $surname = self::my_ucwords(trim($surname));
      $title = self::my_ucwords(trim($title));  

      //if there is no password georate one
      if($password == '') {
        $password =  PasswordUtils::gen_password();
      }
      
      //add new users
      $result = $db->prepare("INSERT INTO users VALUES(?,?,?,?,?,?,?,?,NULL,?,?,?,NULL,0,?)");
      $result->bind_param('sssssssssssi', PasswordUtils::encpw($username, $password), $degree, $surname, $initials, $title, $username, $email, $role, $faculty,  $forname, $gender, $year);
      $result->execute();
      $result->close();
      $userID = $db->insert_id;
      if(isset($sid) and $sid != '') {
        $result = $db->prepare("INSERT INTO sid VALUES(?,?)");
        $result->bind_param('si', $sid, $userID);
        $result->execute();
        $result->close();
      }
      return $userID;
    }
    
    return false;
    
  }
  
  static function usernameExists($username, $db) {
    $stmt = $db->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userID);
    $stmt->fetch();
    if($stmt->num_rows == 0) {
      return false;
    } else {
      return true;
    }
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