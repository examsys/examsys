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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

Class UserUtils {

  static function create_extended_user($username, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db, $school, $coursedesc, $initials = null, $password = '') {
    $courseok = CourseUtils::add_course($school, $course, $coursedesc, null, null, $db);

    if (($courseok !== true and $course != '') or $username == '' or $surname == '' or $email == '') {
      return false;
    }

    if (!in_array($role, array('Staff', 'Student', 'SysAdmin', 'Admin', 'graduate', 'left', 'External Examiner', 'Standards Setter', 'Internal Reviewer'))) {
      // not a valid role
      return false;
    }

    $userid = self::create_user($username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db, $initials);

    return $userid;
  }

  static function create_user($username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db, $initials = null) {
    $username = trim($username);
    $surname = trim($surname);
    if (empty($username) or empty($surname) or empty($role)) {
      return false;
    }

    if (!self::username_exists($username, $db) and $username != '' and stristr('ps_', $username) === false) {
      // Force re-build of initials off forenames.
      if ($initials == '') {
          $initial = explode(' ', $forname);
          $initials = '';
          foreach ($initial as $name) {
            $initials .= substr($name, 0, 1);
          }
          $initials = strtoupper($initials);
      }

      $surname = self::my_ucwords($surname);
      $title = self::my_ucwords(trim($title));

      $enc = new encryp();

      // If there is no password generate a default one.
      if ($password == '') {
        $password = $enc->gen_password();
      }

      // Force valid value for gender or default to NULL
      $genders = array('male', 'female', 'other');
      if (!in_array(strtolower($gender), $genders)) {
        $gender = null;
      }

      $salt = UserUtils::get_salt();
      $encrypt_password = $enc->encpw($salt, $username, $password);  // One way encrypt the password.

      // Make sure empty string year is stored as null not 0.
      if ($year == '') {
        $year = null;
      }

      // Add new record into users table.
      $result = $db->prepare("INSERT INTO users VALUES(?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, 0, ?, NULL, NULL)");
      $result->bind_param('ssssssssssi', $encrypt_password, $course, $surname, $initials, $title, $username, $email, $role, $forname, $gender, $year);
      $result->execute();
      $result->close();
      $tmp_userID = $db->insert_id;
      if (isset($sid) and $sid != '') {
        self::insert_student_id($db, $sid, $tmp_userID);
      }

      return $tmp_userID;
    }

    return false;
  }

  /**
   * Insert a new student id in the sid table
   * @param mysqli $db
   * @param string $studentid
   * @param int $userid
   * @throws Exception
   */
  static function insert_student_id($db, $studentid, $userid) {
    $langcomponent = 'classes/userutils';
    $langpack = new \langpack();
    $langstrings = $langpack->get_all_strings($langcomponent);
    $result = $db->prepare("INSERT INTO sid VALUES(?, ?)");
    if ($db->error) {
      echo $langstrings['showerror'] . "<br >";
    }
    $result->bind_param('si', $studentid, $userid);
    $result->execute();
    $result->close();
  }

  /**
   * Update existing user.
   * @param integer $id - user id
   * @param string $username - user username
   * @param string $password - user password
   * @param string $title - user title
   * @param string $forname - user first names
   * @param string $surname - user surname
   * @param string $email - user email address
   * @param string $course - user course
   * @param string $gender - user gender
   * @param integer $year - year of study
   * @param string $role - user role
   * @param string $sid - student id
   * @param mysqli $db - db connection
   * @param string $initials - user initials
   * @return bool - true on success
  */
  static function update_user($id, $username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $db, $initials = null) {

    $current = self::get_full_details_by_ID($id, $db);

    if (empty($username) or  empty($surname) or empty($role) or empty($current['username'])) {
      return false;
    }

    // If updating the username check if it exists.
    if ($current['username'] != $username) {
        if (self::username_exists($username, $db)) {
            return false;
        }
    }

    // If updating the forename Force re-build of initials off forenames.
    if ($current['forename'] != $forname) {
        if ($initials == '') {
          $initial = explode(' ', $forname);
          $initials = '';
          foreach ($initial as $name) {
            $initials .= substr($name, 0, 1);
          }
          $initials = strtoupper($initials);
        }
    }

    // If updating the surname check case.
    if ($current['surname'] != $surname) {
        $surname = self::my_ucwords($surname);
    }
    // If updating the title check case.
    $title = trim($title);
    if ($current['title'] != $title) {
        $title = self::my_ucwords($title);
    }

    if ($password == '' or $password == $current['password']) {
        // Keep current password if the same as the encrypted one supplied or password if not supplied.
        $encrypt_password = $current['password'];
    } else {
        // If updating the password - encrypt.
        $enc = new encryp();
        $salt = UserUtils::get_salt();
        $encrypt_password = $enc->encpw($salt, $username, $password);  // One way encrypt the password.
    }

    // If updating the gender. Force valid value for gender or default to NULL
    if ($current['gender'] != $gender) {
        $genders = array('male', 'female', 'other');
        if (!in_array(strtolower($gender), $genders)) {
            $gender = null;
        }
    }

    // Make sure empty string year is stored as null not 0.
    if ($year == '') {
        $year = null;
    }

    // Update record into users table.
    $result = $db->prepare("UPDATE users SET password = ?, grade = ?, surname = ?, initials = ?, title = ?, username = ?,
    email = ?, roles = ?, first_names = ?, gender = ?, yearofstudy = ? WHERE id = ?");
    $result->bind_param('ssssssssssii', $encrypt_password, $course, $surname, $initials, $title, $username, $email, $role, $forname, $gender, $year, $id);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
        return false;
    }
    if (isset($sid) and $sid != '') {
        $result = $db->prepare("UPDATE sid SET student_id = ? WHERE userID = ?");
        $result->bind_param('si', $sid, $id);
        $result->execute();
        $result->close();
    }
    if ($db->errno != 0) {
        return false;
    }
    return true;

  }

  static function get_salt() {
    $configObj = Config::get_instance();

    $auth_settings = $configObj->get('authentication');
    for ($i = 0; $i < count($auth_settings); $i++) {
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

    $stmt = $db->prepare("UPDATE users SET password = ?, password_expire = NULL WHERE id = ?");
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
    $username = substr($username, 0, 60);
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
   * Add a new role to a user.
   *
   * @param string $new_role - The role to be added.
   * @param string $userid   - The ID of the user we are dealing with.
   * @param object $db       - Database connection.
   */
  static function add_role($new_role, $userid, $db) {
    if ($new_role == '') {
      return false;
    }

    $has_role = UserUtils::has_user_role($userid, $new_role, $db);

    if (!$has_role) {    // If new roles does not exist, add.
      $stmt = $db->prepare("UPDATE users SET roles = CONCAT(roles, ',', '$new_role') WHERE id = ?");
      $stmt->bind_param('i', $userid);
      $stmt->execute();
      $stmt->close();
    }
  }

  /**
   * Check if userID exists.
   *
   * @param string $userid  - User ID
   * @param object $db      - Database connection
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

  /**
   * Get the username for a given user ID (if not deleted).
   *
   * @param string $userid  - User ID
   * @param object $db      - Database connection
   *
   * @return string username of the user
   *
   */
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

  /**
   * Get all the details of a user account.
   *
   * @param integer $userID - UserID of the user we wish to look up.
   * @param object $db      - Database connection
   *
   * @return mixed - False if not found, otherwise an array with the details.
   */
  static function get_user_details($userID, $db) {
    $stmt = $db->prepare("SELECT username, title, surname, initials, first_names, email, roles, gender, grade, yearofstudy, user_deleted FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $title, $surname, $initials, $first_names, $email, $roles, $gender, $grade, $yearofstudy, $user_deleted);
    $exists = ($stmt->num_rows > 0);
    $stmt->fetch();
    $stmt->close();

    if (!$exists) {  // Return false if no record found for passed ID.
      return false;
    }

    $parts = explode(' ', $first_names);
    $first_name = $parts[0];

    if (stripos($roles, 'Student') !== false or stripos($roles, 'Graduate') !== false) {
      $stmt = $db->prepare("SELECT student_id FROM sid WHERE userID = ? LIMIT 1");
      $stmt->bind_param('i', $userID);
      $stmt->execute();
      $stmt->bind_result($student_id);
      $stmt->fetch();
      $stmt->close();

      return array('username' => $username, 'title' => $title, 'surname' => $surname, 'initials' => $initials, 'first_names' => $first_names, 'first_name' => $first_name, 'email' => $email, 'roles' => $roles, 'student_id' => $student_id, 'gender' => $gender, 'grade' => $grade, 'yearofstudy' => $yearofstudy, 'user_deleted' => $user_deleted);
    } else {
      return array('username' => $username, 'title' => $title, 'surname' => $surname, 'initials' => $initials, 'first_names' => $first_names, 'first_name' => $first_name, 'email' => $email, 'roles' => $roles, 'student_id' => '', 'gender' => $gender, 'grade' => $grade, 'yearofstudy' => $yearofstudy, 'user_deleted' => $user_deleted);
    }
  }

  /**
   * Add a member of staff onto a team.
   *
   * @param integer $tmp_userID - UserID of the member of staff.
   * @param int $idmod          - The id of the team (module).
   * @param object $db          - Database connection.
   * @return bool
   */
  static function add_staff_to_module($tmp_userID, $idMod, $db) {
    if (UserUtils::has_user_role($tmp_userID, 'Staff', $db)) {
      $stmt = $db->prepare("INSERT INTO modules_staff VALUES (NULL, ?, ?, NOW())");
      $stmt->bind_param('ii', $idMod, $tmp_userID);
      $stmt->execute();
      $stmt->close();
      if ($db->errno != 0) {
        return false;
      }
      return true;
    }
    return false;
  }

  /**
   * Remove a member of staff from a team.
   *
   * @param integer $tmp_userID - UserID of the member of staff.
   * @param int $idMod          - The id of the team (module).
   * @param object $db          - Database connection.
   * @return bool true on success, false otherwise
   */
  static function remove_staff_from_module($tmp_userID, $idMod, $db) {
    if (UserUtils::has_user_role($tmp_userID, 'Staff', $db)) {
      $stmt = $db->prepare("DELETE FROM modules_staff WHERE memberID = ? AND idMod = ?");
      $stmt->bind_param('ii', $tmp_userID, $idMod);
      $stmt->execute();
      $stmt->close();
      if ($db->errno != 0) {
        return false;
      }
      return true;
    }
  }

  /**
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
   * Lists the teams a user ID is on (uses the user object for the curent users
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
                                mod_deleted IS NULL AND
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
    $result = $db->prepare("SELECT memberID FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND moduleid = ? AND mod_deleted IS NULL");
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
   * @return int|bool enrolement id 0 if already enrolled, false on error
   *
   */
  static function add_student_to_module_by_name($tmp_userID, $idMod, $attempt, $session, $db, $auto_update = 0) {

    $yearutils = new yearutils($db);
    if (!array_key_exists($session, $yearutils->get_supported_years()) or $idMod == '' or $tmp_userID == '') {
      return false;
    }

    $moduleid = module_utils::get_idMod($idMod, $db);
    if ($moduleid !== false) {
      return self::add_student_to_module($tmp_userID, $moduleid, $attempt, $session, $db, $auto_update);
    }
  }
  
  /**
   * Un-enrole a student on a module.
   * @param int $tmp_userID ID of the student to be un-enroled.
   * @param string $modulename Module name for the enrolement.
   * @param int $attempt Attempt number
   * @param mysqli $db database connection.
   * @param bool $auto_update
   * @return bool|int false / enrolment id
   */
  public static function remove_student_from_module_by_modulecode($tmp_userID, $modulename, $session, $db) {
    $moduleid = module_utils::get_idMod($modulename, $db);
    if ($moduleid !== false) {
      return self::remove_student_from_module($tmp_userID, $moduleid, $session, $db);
    }
    return false;
  }

  /**
   * Enrole a student on a module.
   *
   * @param int $userID ID of the student to be enroled.
   * @param int $idMod Module ID for the enrolement.
   * @param object $db $mysqli database connection.
   *
   * @return int|bool enrolement id, 0 if already enrolled, false on error
   */
  static function add_student_to_module($tmp_userID, $idMod, $attempt, $session, $db, $auto_update = 0) {

    $yearutils = new yearutils($db);
    if (!array_key_exists($session, $yearutils->get_supported_years()) or $idMod == '' or $tmp_userID == '') {
      return false;
    }

    // Check is module exists.
    if(!module_utils::get_moduleid_from_id($idMod, $db)) {
      return false;
    }
    
    $userObject = UserObject::get_instance();

    if (self::is_user_on_module($tmp_userID, $idMod, $session, $db)) {
      // Don't add a user to a module multiple times.
      return 0;
    } else {
      $result = $db->prepare("INSERT INTO modules_student VALUES (NULL, ?, ?, ?, ?, ?)");
      $result->bind_param('iisii', $tmp_userID, $idMod, $session, $attempt, $auto_update);
      $result->execute();
      $result->close();
      if ($db->errno != 0) {
        return false;
      }
      if (!is_null($userObject) and $tmp_userID === $userObject->get_user_ID()) {
        $userObject->load_student_modules();
      }

      return $db->insert_id;
    }
  }

  /**
   * Clear a user (student) from all modules for that session and attempt.
   *
   * @param integer $tmp_userID UserID of the member of student to remove
   * @param integer $session session year to be removed from
   * @param integer $attempt attempt to be removed from
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
    if ($modid === false) {
      return false;
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
      $sql = "SELECT userID FROM modules_student WHERE userID = ? AND idMod IN ($idMod) AND calendar_year = ?";
      $result = $db->prepare($sql);
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

  static function load_student_modules($userID, $db) {
    $studentModules = array();

    // studentmodule year -> module ->decode
    $result = $db->prepare("SELECT idMod, moduleID, calendar_year FROM modules_student, modules WHERE modules_student.idMod = modules.id AND userID = ? AND modules.moduleID IS NOT NULL AND mod_deleted IS NULL ORDER BY modules.moduleID"); //SELECT userID FROM modules_student WHERE userID=? AND idMod=? AND calendar_year=?");
    $result->bind_param('i', $userID);
    $result->execute();

    $result->bind_result($idMod, $moduleID, $calyear);
    while ($result->fetch()) {
      $studentModules[$calyear][$idMod] = $moduleID;
    }
    $result->close();

    return $studentModules;
  }

  /**
   * Set a single user to be deleted. Also appends the primary key ID
   * to the end of username so that username is still unique if
   * another user with the same username is added later.
   *
   * @param int $userID - ID of the student.
   * @param object $db  - database connection.
   *
   */
  static function delete_userID($userID, $db) {
    $result = $db->prepare("UPDATE users SET username = CONCAT(username, '_', id), user_deleted = NOW() WHERE id = ?");
    $result->bind_param('i', $userID);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    return true;
  }

  /**
   * Does a search for a student photo in the /users/photos/ directory.
   * A search is performed against JPEG, GIF and PNG file types.
   *
   * @param string $username  - Username of the student we wish to search for.
   * @return bool|string      - Returns false if file not found, otherwise will return
   *                            the username and extention (file) is has matched.
   *
   */
  static function student_photo_exist($username) {
    $found = false;
    $photodirectory = rogo_directory::get_directory('user_photo');
    $location = $photodirectory->location();

    if (file_exists($location . "$username.jpg")) {
      $found = $username . '.jpg';
    } elseif (file_exists($location . "$username.jpeg")) {
      $found = $username . '.jpeg';
    } elseif (file_exists($location . "$username.gif")) {
      $found = $username . '.gif';
    } elseif (file_exists($location . "$username.png")) {
      $found = $username . '.png';
    }

    return $found;
  }

 /**
  * Check if the user has started a paper
  * @param integer $id - user id
  * @param mysqli $db
  * @return bool
  */
  static function user_paper_started($id, $db) {
    $result = $db->prepare("SELECT NULL FROM log_metadata WHERE userID = ?
        UNION SELECT NULL FROM log4_overall WHERE userID = ?");
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
   * Get user details
   * @param integer $id user id
   * @param mysqli $db
   * @return array user details
   */
  static function get_full_details_by_ID($id, $db) {
      $sql = $db->prepare("SELECT username, password, title, first_names, surname, email, grade, gender,
        yearofstudy, roles, initials, student_id FROM users LEFT JOIN sid ON id = userID WHERE id = ?");
      $sql->bind_param('i', $id);
      $sql->execute();
      $sql->bind_result($username, $password, $title, $first_names, $surname, $email, $grade, $gender,
        $yearofstudy, $roles, $initials, $student_id);
      $sql->fetch();
      $sql->close();

      $details = array(
        'username' => $username,
        'password' => $password,
        'title' => $title,
        'forename' => $first_names,
        'surname' => $surname,
        'email' => $email,
        'course' => $grade,
        'gender' => $gender,
        'year' => $yearofstudy,
        'role' => $roles,
        'studentid' => $student_id,
        'initials' => $initials);

      return $details;
  }

  /**
   * Remove the student from the module
   * 
   * @param int $userid 
   * @param int $moduleid
   * @param string $session 
   * @param mysqli $db
   * @return bool|int false / enrolment id
   */
  static function remove_student_from_module($userid, $moduleid, $session, $db) {
    if ($userid == '' or $moduleid == '' or $session == '') {
      return false;
    }
    $sql = $db->prepare("SELECT id from modules_student WHERE userID = ? AND idMod = ? AND calendar_year = ?");
    $sql->bind_param('iii', $userid, $moduleid, $session);
    $sql->execute();
    $sql->bind_result($id);
    $sql->fetch();
    $sql->close();
    if ($id) {
      $result = $db->prepare("DELETE FROM modules_student WHERE userID = ? AND idMod = ? AND calendar_year = ?");
      $result->bind_param('iii', $userid, $moduleid, $session);
      $result->execute();
      $result->close();
      if ($db->errno != 0) {
        return false;
      }
      return $id;
    }
    return false;
  }

  /**
   * Get the enrolment id of the user on the module
   *
   * Only the attempt and auto_update values can be updated.
   * @param integer $userid - user id
   * @param integer $moduleid - module id
   * @param integer $session - academic session
   * @param mysqli $db - db connection
   * @return integer|bool - enrolement id, false on error
   */
  static function get_enrolement_id($userid, $moduleid, $session, $db) {
      $result = $db->prepare("SELECT id FROM modules_student WHERE userID = ? AND idMod = ? AND calendar_year = ?");
      $result->bind_param('iii', $userid, $moduleid, $session);
      $result->execute();
      $result->bind_result($id);
      $result->store_result();
      $result->fetch();
      if ($result->num_rows == 0) {
        $result->close();
        return false;
      }
      $result->close();
      return $id;
  }

  /**
   * Update module enrolement
   *
   * Only the attempt and auto_update values can be updated.
   * @param integer $id - enrolement id
   * @param integer $attempt - attempt at module
   * @param integer $auto_update - is enrolement remotely updatable
   * @param mysqli $db - db connection
   */
  static function update_module_enrolement($id, $attempt, $auto_update, $db) {
      $result = $db->prepare("UPDATE modules_student SET auto_update = ?, attempt = ? WHERE id = ?");
      $result->bind_param('iii', $auto_update, $attempt, $id);
      $result->execute();
      $result->close();
  }

}
