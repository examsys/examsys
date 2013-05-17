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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . '/classes/courseutils.class.php';

Class UserUtils {

  static function create_extended_user($username, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db, $school, $coursedesc, $initials = null, $password = '') {
    $courseok = CourseUtils::add_course($school, $course, $coursedesc, $db);

    if (($courseok !== true and $course!='') or $username == '' or $surname == '' or $email == '') {
      return false;
    }

    if (!in_array($role, array('Staff', 'Student', 'SysAdmin', 'Admin', 'graduate', 'left', 'External Examiner'))) {
      // not a valid role
      return false;
    }

    $userid = self::create_user($username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db, $initials);

    return $userid;
  }

  static function create_user($username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db, $initials = null) {
    if ($username == '' or  $surname == '' or $role == '') {
      return false;
    }

    if (!self::username_exists($username, $db) and $username != '' and stristr('ps_', $username) === false) {
      if (is_null($initials)) {
        $initial = explode(' ', $forname);
        $initials = '';
        foreach ($initial as $name) {
          $initials .= substr($name, 0, 1);
        }
        $initials = strtoupper($initials);
      }

      $surname = self::my_ucwords(trim($surname));
      $title = self::my_ucwords(trim($title));

      //if there is no password generate one
      if ($password == '') {
        $password = gen_password();
      }

      //force valid value for gender or default to NULL
      if (strtolower($gender) != 'male' and strtolower($gender) != 'female') {
        $gender = null;
      }

      $salt = UserUtils::get_salt();
      $encrypt_password = encpw($salt, $username, $password);  // One way encrypt the password.

      //add new users
      $result = $db->prepare("INSERT INTO users VALUES(?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, NULL, 0, ?, NULL, NULL)");
      $result->bind_param('ssssssssssi', $encrypt_password, $course, $surname, $initials, $title, $username, $email, $role, $forname, $gender, $year);
      $result->execute();
      $result->close();
      $tmp_userID = $db->insert_id;
      if (isset($sid) and $sid != '') {
        $result = $db->prepare("INSERT INTO sid VALUES(?, ?)");
        if ($db->error) {
          try {
            throw new Exception("MySQL error $db->error <br /> Query:<br /> ", $db->errno);
          } catch (Exception $e) {
            echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
            echo nl2br($e->getTraceAsString());
          }
        }
        $result->bind_param('si', $sid, $tmp_userID);
        $result->execute();
        $result->close();
      }

      return $tmp_userID;
    }

    return false;
  }

  static function get_salt() {
    $configObj = Config::get_instance();
  
    $auth_settings = $configObj->get('authentication');
    for ($i=0; $i<count($auth_settings); $i++) {
      if ($auth_settings[$i][0] == 'internaldb') {
        $cfg_encrypt_salt = $auth_settings[$i][1]['encrypt_salt'];
      }
    }
    
    return $cfg_encrypt_salt;
  }

  static function update_password($username, $password, $userID, $db) {
    if ($userID == '' or $password == '') {
      return false;
    }
    
    $salt = UserUtils::get_salt();
    $encrypt_password = encpw($salt, $username, $password);

    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $encrypt_password, $userID);
    if (!$stmt->execute()) {
      $success = false;
    } else {
      $success = true;
    }
    $stmt->close();
    
    return $success;
  }
  
  /**
   * Check if username exists and if so return ID.
   *
   * @param string $username username
   * @param object $db mysqli database connection
   *
   * @return mixed user ID if exists, otherwise false
   *
   */
  static function username_exists($username, $db) {
    if ($username == '') {
      return false;
    }
  
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND user_deleted IS NULL");
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
   * Check if userID exists.
   *
   * @param string $userid user ID
   * @param object $db mysqli database connection
   *
   * @return true if exists else false
   *
   */
  static function userid_exists($userid, $db) {
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND user_deleted IS NULL");
    $stmt->bind_param('i', $userid);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($tmp_userID);
    $stmt->fetch();
    $exists = ($stmt->num_rows == 0) ? false : true;
    $stmt->close();

    return $exists;
  }

  static function get_username($userid, $db) {
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ? AND user_deleted IS NULL");
    $stmt->bind_param('i', $userid);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username);
    $stmt->fetch();
    $exists = ($stmt->num_rows == 0) ? false : $username;
    $stmt->close();

    return $exists;
  }

  /**
   * Check if Student ID exists and if so return ID.
   *
   * @param string $sid Student ID
   * @param object $db mysqli database connection
   *
   * @return mixed user ID if exists, otherwise false
   *
   */
  static function studentid_exists($sid, $db) {
    $stmt = $db->prepare("SELECT userID FROM sid WHERE student_id = ?");
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
   * Check if a user has a particular role.
   *
   * @param integer $tmp_userID UserID of the user to be checked
   * @param string $test_role the role to be checked
   * @param object $db mysqli database connection
   *
   * @return bool whether role was found or not
   *
   */
  static function has_user_role($tmp_userID, $test_role, $db) {
    $stmt = $db->prepare("SELECT roles FROM users WHERE id = ? AND user_deleted IS NULL LIMIT 1");
    $stmt->bind_param('i', $tmp_userID);
    $stmt->execute();
    $stmt->bind_result($roles);
    $stmt->fetch();
    $stmt->close();

    $roles_list = explode(',', $roles);
    $match = false;
    foreach ($roles_list as $individual_role) {
      if ($individual_role == $test_role) {
        $match = true;
      }
    }

    return $match;
  }
  
  static function get_user_details($tmp_userID, $db) {
    $stmt = $db->prepare("SELECT title, surname, initials, first_names, email, roles FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $tmp_userID);
    $stmt->execute();
    $stmt->bind_result($title, $surname, $initials, $first_names, $email, $roles);
    $stmt->fetch();
    $stmt->close();
    
    return array('title'=>$title, 'surname'=>$surname, 'initials'=>$initials, 'first_names'=>$first_names, 'email'=>$email, 'roles'=>$roles);
  }

  /**
   * Add a member of staff onto a team.
   *
   * @param integer $tmp_userID UserID of the member of staff
   * @param int $idmod the id of the team (module)
   * @param object $db mysqli database connection
   *
   */
  static function add_staff_to_module($tmp_userID, $idMod, $db) {
    if (UserUtils::has_user_role($tmp_userID, 'Staff', $db)) {
      $stmt = $db->prepare("INSERT INTO modules_staff VALUES (NULL, ?, ?, NULL, 'System')");
      $stmt->bind_param('ii', $idMod, $tmp_userID);
      $stmt->execute();
      $stmt->close();
    }

  }  /**
   * Add a member of staff onto a team by modulecode.
   *
   * @param integer $tmp_userID UserID of the member of staff
   * @param string $module_code the name of the team (module)
   * @param object $db mysqli database connection
   *
   */
  static function add_staff_to_module_by_modulecode($tmp_userID, $module_code, $db) {

    if (!UserUtils::has_user_role($tmp_userID, 'Staff', $db)) {
      return;
    }
    $idMod = module_utils::get_idMod($module_code, $db);
    if ($idMod !== false) {
      self::add_staff_to_module($tmp_userID, $idMod, $db);
    }
  }

  /**
   * Clear all users (staff) from a team.
   *
   * @param string $team_name the name of the team (module)
   * @param object $db mysqli database connection
   *
   */
  static function clear_staff_modules_by_moduleID($idMod, $db) {
    $stmt = $db->prepare("DELETE FROM modules_staff WHERE idMod = ?");
    $stmt->bind_param('i', $idMod);
    $stmt->execute();
    $stmt->close();
  }
  
  /**
   * Lists the team a user id is on (uses the user object for the curent users
   * use this if we are not dealing with the logged in user)
   * 
   * @param string $userID the id of the user
   * @param object $db mysqli database connection
   *
   */
  static function list_staff_modules_by_userID($userID, $db) {
    $user_modules = array();
    $result = $db->prepare("SELECT 
                                moduleID, idMod 
                            FROM 
                                modules_staff, modules 
                            WHERE 
                                modules_staff.idMod = modules.id AND 
                                type = 'System' AND 
                                memberID = ?");
    $result->bind_param('i', $userID);
    $result->execute();
    $result->bind_result($moduleID, $idMod);
    while ($result->fetch()) {
      $user_modules[$idMod] = $moduleID;
    }
    $result->close();
    return $user_modules;
  }

  /**
   * Clear a user (staff) from all teams.
   *
   * @param integer $tmp_userID UserID of the member of staff to remove
   * @param object $db mysqli database connection
   *
   */
  static function clear_staff_modules_by_userID($tmp_userID, $db) {
    $userObject = UserObject::get_instance();

    $result = $db->prepare("DELETE FROM modules_staff WHERE memberID = ?");
    $result->bind_param('i', $tmp_userID);
    $result->execute();
    $result->close();

    if ($userObject->get_user_ID() == $tmp_userID) {
      $userObject->load_staff_modules();     // Re-cache modules if the user is the currently logged in person.
    }
  }

  /**
   * Clear a user (admin) from all admin schools.
   *
   * @param integer $tmp_userID UserID of the member of staff to remove
   * @param object $db mysqli database connection
   *
   */
  static function clear_admin_access($tmp_userID, $db) {
    $result = $db->prepare("DELETE FROM admin_access WHERE userID = ?");
    $result->bind_param('i', $tmp_userID);
    $result->execute();
    $result->close();
  }

  /**
   * Get a list of members of a team.
   *
   * @param integer $modID The ID of the team to query
   * @param object $db mysqli database connection
   *
   * @return array list of UserIDs for member of the team
   *
   */
  static function get_staff_modules_list_by_modID($modID, $db) {
    $team_members = array();
    $result = $db->prepare("SELECT memberID FROM modules_staff WHERE idMod = ?");
    $result->bind_param('i', $modID);
    $result->execute();
    $result->bind_result($memberID);
    while ($result->fetch()) {
      $team_members[] = $memberID;
    }
    $result->close();

    return $team_members;
  }

  /**
   * Get a list of members of a team.
   *
   * @param string $team_name The name of the team to query
   * @param object $db mysqli database connection
   *
   * @return array list of UserIDs for member of the team
   *
   */
  static function get_staff_modules_list_by_name($team_name, $db) {
    $team_members = array();
    $result = $db->prepare("SELECT memberID FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND moduleid = ?");
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
   * @param string $idMod Module code for the enrolement.
   * @param object $db $mysqli database connection.
   *
   * @return bool return true if successful.
   *
   */
  static function add_student_to_module_by_name($tmp_userID, $idMod, $attempt, $session, $db, $auto_update = 0) {

    $moduleid = module_utils::get_idMod($idMod, $db);
    if ($moduleid !== false) {
      return self::add_student_to_module($tmp_userID, $moduleid, $attempt, $session, $db, $auto_update);
    }
  }

  /**
   * Enrole a student on a module.
   *
   * @param int $userID ID of the student to be enroled.
   * @param int $idMod Module ID for the enrolement.
   * @param object $db $mysqli database connection.
   *
   * @return bool return true if successful.
   *
   */
  static function add_student_to_module($tmp_userID, $idMod, $attempt, $session, $db, $auto_update = 0) {

    $userObject = UserObject::get_instance();

    if (self::is_user_on_module($tmp_userID, $idMod, $session, $db)) {
      //don't add a user to a module multiple times
      return true;
    } else {
      $result = $db->prepare("INSERT INTO modules_student VALUES (NULL, ?, ?, ?, ?, ?)");
      $result->bind_param('iisii', $tmp_userID, $idMod, $session, $attempt, $auto_update);
      $result->execute();
      $result->close();
      if ($db->errno != 0) {
        return false;
      }
      if ($tmp_userID === $userObject->get_user_ID()) {
        $userObject->load_student_modules();
      }

      return true;
    }
  }

  /**
   * Clear a user (student) from all modules for that session and attempt.
   *
   * @param integer $tmp_userID UserID of the member of student to remove
   * @param integer $session session year to be removed from
   * @param integer $attemp attempt to be removed from
   * @param object $db mysqli database connection
   *
   */
  static function clear_student_modules_by_userID($tmp_userID, $session, $attempt, $db) {
    $userObject = UserObject::get_instance();

    $result = $db->prepare("DELETE FROM modules_student WHERE userID = ? AND calendar_year = ? AND attempt = ?");
    $result->bind_param('isi', $tmp_userID, $session, $attempt);
    $result->execute();
    $result->close();

    if ($userObject->get_user_ID() == $tmp_userID) {
      $userObject->load_student_modules();     // Re-cache modules if the user is the currently logged in person.
    }
  }

  /**
   * Test to see if a student is on a module by name.
   *
   * @param int $tmp_userID ID of the student.
   * @param int $idMod Module ID for the enrolement.
   * @param string $session The academic year.
   * @param object $db $mysqli database connection.
   *
   * @return bool return true if successful.
   *
   */
  static function is_user_on_module_by_name($tmp_userID, $idMod, $session, $db) {
    if (is_array($idMod)) {
      foreach ($idMod as $idmods) {
        $modid[] = module_utils::get_idMod($idmods, $db);
      }
    } else {
      $modid = module_utils::get_idMod($idMod, $db);
    }

    return self::is_user_on_module($tmp_userID, $modid, $session, $db);
  }

  /**
   * Test to see if a student is on a module.
   *
   * @param int $tmp_userID ID of the student.
   * @param int $idMod Module ID for the enrolement.
   * @param string $session The academic year.
   * @param object $db $mysqli database connection.
   *
   * @return bool return true if successful.
   *
   */
  static function is_user_on_module($tmp_userID, $idMod, $session, $db) {
    if (is_array($idMod)) {
      $idMod = implode(',', $idMod);
    }

    if ($session == '') {
      $result = $db->prepare("SELECT userID FROM modules_student WHERE userID = ? AND idMod IN ($idMod)");
      $result->bind_param('i', $tmp_userID);
    } else {
      $result = $db->prepare("SELECT userID FROM modules_student WHERE userID = ? AND idMod IN ($idMod) AND calendar_year = ?");
      $result->bind_param('is', $tmp_userID, $session);
    }
    
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_userID);
    $exists = ($result->num_rows > 0);
    $result->close();

    return $exists;
  }

  static function fixcase_callback($word) {
    $word = $word[1];
    $word = mb_strtolower($word);

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
    if (mb_check_encoding($s, "UTF-8")) {
      //do nothing 
    } else {
      $s = preg_replace_callback("/(\b[\w|']+\b)/s", array('UserUtils', 'fixcase_callback'), $s);
    }
    return $s;
  }

}

?>