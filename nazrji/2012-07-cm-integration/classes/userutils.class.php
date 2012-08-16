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

  static function create_user($username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db) {
    global $cfg_encrypt_salt;

    if (!self::username_exists($username, $db) and $username != '' and stristr('ps_', $username) === false) {
      $initial = explode(' ', $forname);
      $initials = '';
      foreach ($initial as $name) {
        $initials .= substr($name, 0, 1);
      }
      $initials = strtoupper($initials);
      $surname = self::my_ucwords(trim($surname));
      $title = self::my_ucwords(trim($title));

      //if there is no password generate one
      if ($password == '') {
        $password = gen_password();
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
      $tmp_userID = $db->insert_id;
      if (isset($sid) and $sid != '') {
        $result = $db->prepare("INSERT INTO sid VALUES(?, ?)");
        $result->bind_param('si', $sid, $tmp_userID);
        $result->execute();
        $result->close();
      }
      
      return $tmp_userID;
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
  static function username_exists($username, $db) {
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
  static function studentid_exists($sid, $db) {
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

  /**
   * Add a member of staff onto a team.
   *
   * @param integer $tmp_userID UserID of the member of staff
   * @param string $module the name of the team (module)
   * @param object $db mysqli database connection
   *
   */
  static function add_staff_to_team($tmp_userID, $module, $db) {
    $stmt = $db->prepare("INSERT INTO teams VALUES (NULL, ?, ?, NULL, 'System')");
    $stmt->bind_param('si', $module, $tmp_userID);
    $stmt->execute();
    $stmt->close();
  }

  /**
   * Clear all users (staff) from a team.
   *
   * @param string $team_name the name of the team (module)
   * @param object $db mysqli database connection
   *
   */
  static function clear_team_by_team_name($team_name, $db) {
    $result = $db->prepare("DELETE FROM teams WHERE name=?");
    $result->bind_param('s', $team_name);
    $result->execute();
    $result->close();
  }

  /**
   * Clear a user (staff) from all teams.
   *
   * @param integer $tmp_userID UserID of the member of staff to remove
   * @param object $db mysqli database connection
   *
   */
  static function clear_team_by_userID($tmp_userID, $db) {
    $result = $db->prepare("DELETE FROM teams WHERE memberID=?");
    $result->bind_param('i', $tmp_userID);
    $result->execute();
    $result->close();
  }

  /**
   * Get a list of members of a team.
   *
   * @param string $team_name The name of the team to query
   * @param object $db mysqli database connection
   * @return array list of UserIDs for member of the team
   *
   */
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
    if (UserUtils::is_user_on_module($tmp_userID, $module, $session, $db)) {
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

  /**
   * Test to see if a student is on a module.
   *
   * @param int $tmp_userID ID of the student.
   * @param string $module Module ID for the enrolement.
   * @param string $session The academic year.
   * @param object $db $mysqli database connection.
   * @return bool return true if successful.
   *
   */
  static function is_user_on_module($tmp_userID, $module, $session, $db) {
    $result = $db->prepare("SELECT userID FROM student_modules WHERE userID=? AND moduleid=? AND calendar_year=?");
    $result->bind_param('iss', $tmp_userID, $module, $session);
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

    if ($word == 'de') return $word;

    $word = ucfirst($word);

    if (substr($word, 1, 1) == "'") {
      if (substr($word, 0, 1) == "D") {
        $word = strtolower($word);
      }
      $next = substr($word, 2, 1);
      $next = strtoupper($next);
      $word = substr_replace($word, $next, 2, 1);
    }
    return $word;
  }

  static function my_ucwords($s) {
    $s = preg_replace_callback("/(\b[\w|']+\b)/s", array('UserUtils', 'fixcase_callback'), $s);
    return $s;
  }

  static function staff_on_team($module, $db, $tmp_userID = -99) {
    global $userID;
    if ($tmp_userID == -99) {
      $tmp_userID = $userID;
    }

    $teams = array();

    $result = $db->prepare("SELECT name FROM teams WHERE memberID=? AND name IS NOT NULL ORDER BY name");
    echo $db->error;
    $result->bind_param('i', $tmp_userID);
    $result->execute();
    $result->bind_result($team_name);
    while ($result->fetch()) {
      $team_name = strtoupper($team_name);
      $teams[$team_name] = $team_name;
    }
    $result->close();


    if (isset($teams[$module])) {
      return true;

    }
    else {
      return false;
    }
  }

}

?>