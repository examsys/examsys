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
 * UserObject Class
 *
 * class for the currently logged in user and any functions related to this
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'classes/schoolutils.class.php';
require_once $cfg_web_root . 'classes/rogostaticsingleton.class.php';
require_once $cfg_web_root . 'classes/usernotices.class.php';

class UserObject extends RogoStaticSingleton {

  protected static $inst = NULL;
  protected static $class_name = 'UserObject';
  protected static $dont_construct = true;
  private $password, $userID, $userroles, $title, $initials, $first_names, $surname, $username, $email, $grade, $year, $special_needs, $special_needs_percentage, $record_no, $split_username;
  private $demomode = false;
  private $roles, $staffModules, $staffTeamModules, $studentModules, $db, $configObj;

  // Special needs variables
  private $background, $foreground, $textsize, $extra_time, $marks_color, $themecolor, $labelcolor, $font, $unanswered;

  private $impersonateduser;

  /**
   * constructor
   *
   * @param $db is a mysqli link to db
   * @param $configObject a Rogo config object populated from config.inc
   *
   * @return none
   */
  function __construct($configObject, $db) {
    if (is_object(self::$inst)) {
      throw new Exception("Highlander:: there can be only one UserObject");
    }
    $this->db = & $db;
    $this->configObj = & $configObject;
    self::$inst = $this;
  }

  /**
   * TEMP Function exports user roles in old style
   *
   * @param $array array of data in old format
   *
   * @return list of roles
   */
  function old_getuserroles() {
    return $this->userroles;
  }

  function get_bgcolor($default = '') {
    if (!isset($this->background) && $default != '') {
      $this->background = $default;
    }

    return $this->background;
  }

  function get_fgcolor($default = '') {
    if (!isset($this->foreground) && $default != '') {
      $this->foreground = $default;
    }

    return $this->foreground;
  }

  function get_textsize($default = '') {
    if ($this->textsize == 0 && $default != '') {
      $this->textsize = $default;
    }

    return $this->textsize;
  }

  function get_marks_color($default = '') {
    if (!isset($this->marks_color) && $default != '') {
      $this->marks_color = $default;
    }

    return $this->marks_color;
  }

  function get_themecolor($default = '') {
    if (!isset($this->themecolor) && $default != '') {
      $this->themecolor = $default;
    }

    return $this->themecolor;
  }

  function get_labelcolor($default = '') {
    if (!isset($this->labelcolor) && $default != '') {
      $this->labelcolor = $default;
    }

    return $this->labelcolor;
  }

  function get_font($default = '') {
    if (!isset($this->font) && $default != '') {
      $this->font = $default;
    }

    return $this->font;
  }

  function get_unanswered_color($default = '') {
    if (!isset($this->unanswered_color) && $default != '') {
      $this->unanswered_color = $default;
    }

    return $this->unanswered_color;
  }

  /**
   * checks if user has role(s) specified
   *
   * @param $roles either a string or an array of strings
   * @param $exclusive if this should only have this role
   *
   * @return true if has role(s)
   */
  function has_role($roles, $exclusive = 0) {
    if (is_string($roles)) {
      if ($exclusive == 0  or ($exclusive == 1 and count($this->roles) == 1)) {
        if (isset($this->roles[$roles])) {
          return true;
        }
      }
    } else {
      // assume array
      if ($exclusive == 0 or ($exclusive == 1 and count($this->roles) == count($roles))) {
        foreach ($roles as $role) {
          if (isset($this->roles[$role])) {
            return true;
          }
        }
      }
    }

    return false;
  }

  function is_temporary_account() {
    // Look for 'user' followed by one or more digits.
    return preg_match('/^user[0-9]+/', $this->username);
  }

  function is_demo() {
    if ($this->demomode or $this->has_role('Demo')) {
      return true;
    }

    return false;
  }

  function set_demo() {
    $this->demomode = true;
    $this->configObj->append('cfg_install_type', " (DEMO mode)");
    $this->roles['Demo'] = 1;
  }

  /**
   * list the users roles
   *
   * @return array of the users roles
   */
  function list_user_roles() {
    return array_keys($this->roles);
  }

  /**
   * returns the year of the user
   *
   * @return the year of the user
   */
  function get_year() {
    return $this->year;
  }

  /**
   * returns the userID
   *
   * @return userID
   */
  function &get_user_ID() {
    return $this->userID;
  }

  /**
   * @param string userID
   *
   * @return UserObject
   */
  function set_user_ID($user_id) {
    $this->userID = $user_id;

    return $this;
  }

  /**
   * get the staff modules
   *
   * @return false if not staff else an array of the modules by id & CODE
   */
  function get_staff_modules() {

    if (!$this->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
      //this is not a staff user so it cant be on any modules
      return false;
    }

    if (count($this->staffModules) < 1) {
      $this->load_staff_modules();
    }

    return $this->staffModules;
  }
  
  /**
   * get the staff members teams only (not a list of all modules thay can access 
   * just their temas) used in /staff/index.php
   * 
   * @return false if not staff else an array of the modules by id with idMod 
   *         and fullName
   */
  function get_staff_team_modules() {

    if (!$this->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
      //this is not a staff user so it cant be on any modules
      return false;
    }

    if (count($this->staffTeamModules) < 1) {
      $this->load_staff_team_modules();
    }

    return $this->staffTeamModules;
  }

  function has_metadata($modIDs, $security_type, $security_value) {
    if (count($modIDs) == 0) return false;
    $has_data = TRUE;

    $result = $this->db->prepare("SELECT users_metadata.userID FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN (" . implode(',', $modIDs) . ") AND userID = ? AND type = ? AND value = ?");
    $result->bind_param('iss', $this->get_user_ID(), $security_type, $security_value);
    $result->execute();
    $result->store_result();
    if ($result->num_rows == 0) {
      $has_data = false;
    }
    $result->close();

    return $has_data;
  }

  /**
   * @param string $moduleID an array of modules keyed on idMod
   *
   * @return bool true if staff member is on a module
   */
  function is_staff_user_on_module($moduleID) {

    if (!$this->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
      //this is not a staff user so it cant be on any modules
      return false;
    }

    if (count($this->staffModules) < 1) {
      $this->load_staff_modules();
    }

    switch (gettype($moduleID)) {
      case 'array':
        if (count($moduleID) > 1) {
          throw new Exception("is_staff_user_on_module:: only accepts one module at a time.");
        }
        foreach ($moduleID as $idMod => $full_moduleID) {
          if (isset($this->staffModules[$idMod])) {
            return true;
          }
        }
        break;
      case 'string':
        if (in_array($moduleID, $this->staffModules)) {
          return true;
        }
        break;
      case 'integer':
        if (isset($this->staffModules[$moduleID])) {
          return true;
        }
        break;
    }

    return false;
  }

  /**
   * loads the staff modules
   *
   * @return the staff module list //TODO probably dont need the return
   */
  function load_staff_modules() {
    $this->staffModules = array();

    if ($this->has_role('Admin')) {
      $result = $this->db->prepare("(SELECT idMod, moduleID FROM modules_staff, modules WHERE modules_staff.idMod = modules.id and memberID = ? AND modules.moduleID IS NOT NULL and mod_deleted IS NULL) UNION (SELECT id, moduleID FROM modules, admin_access WHERE admin_access.schools_id=modules.schoolid AND userID = ? AND modules.moduleID IS NOT NULL and mod_deleted IS NULL)");
      $result->bind_param('ii', $this->userID, $this->userID);
    } else {
      $result = $this->db->prepare("SELECT idMod, moduleID FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND memberID = ? AND modules.moduleID IS NOT NULL AND mod_deleted IS NULL ORDER BY modules.moduleID");
      $result->bind_param('i', $this->userID);
    }    
    $result->execute();
    $result->bind_result($idMod, $moduleID);
    while ($result->fetch()) {
      $this->staffModules[$idMod] = $moduleID;
    }
    $result->close();
    
    return $this->staffModules;
  }
  
  /**
   * loads the modules a staff member is explicitly on the team for
   * used in /staff/index.php
   * 
   * @return array the staff module list
   */
  function load_staff_team_modules() {
    $this->staffTeamModules = array();

    $result = $this->db->prepare("SELECT idMod, moduleID, fullname FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND memberID = ? AND modules.moduleID IS NOT NULL AND mod_deleted IS NULL ORDER BY modules.moduleID");
    $result->bind_param('i', $this->userID);
    $result->execute();
    
    $result->bind_result($idMod, $moduleID, $fullName);
    while ($result->fetch()) {
      $this->staffTeamModules[$idMod]['code'] = $moduleID;
      $this->staffTeamModules[$idMod]['fullName'] = $fullName;
    }
    $result->close();
    
    return $this->staffTeamModules;
  }

  /**
   * checks if user has special needs
   *
   * @return true if has special needs
   */
  function is_special_needs() {
    if ($this->special_needs != 0) {
      return true;
    }

    return false;
  }

  /**
   * returns the grade of the user
   *
   * @return string grade
   */
  function get_grade() {
    return $this->grade;
  }

  /**
   * Return the user's title
   *
   * @return string Title
   */
  function get_title() {
    return $this->title;
  }

  function get_temp_title() {
    return $this->temp_title;
  }

  /**
   * Return the user's initials
   *
   * @return string Initials
   */
  function get_initials() {
    return $this->initials;
  }

  /**
   *  Return the user's first names
   *
   * @return string first_names
   */
  public function get_first_names() {
    return $this->first_names;
  }

  /**
   * Return the user's surname
   *
   * @return string Surname
   */
  function get_surname() {
    return $this->surname;
  }
  
  function get_temp_surname() {
    return $this->temp_surname;
  }

  /**
   * Return the user's username
   *
   * @return string username
   */
  function &get_username() {
    return $this->username;
  }

  /**
   * Return the user's password
   *
   * @return string password
   */
  function get_password() {
    return $this->password;
  }

  /**
   * Return the user's special needs
   *
   * @return string password
   */
  public function get_special_needs() {
    return $this->special_needs;
  }

  /**
   * Return the user's special needs percentage
   *
   * @return string password
   */
  public function get_special_needs_percentage() {
    return $this->extra_time;
  }

  /**
   * Get a list of modules the current user has access to.
   *
   * @return array of staff module that this user has access to.
   */
  function get_staff_accessable_modules($additional_mods = array()) {
    $staff_modules_list = array();

    $staff_modules_sql = implode(',', array_keys($this->get_staff_modules()));
    $default_modules = array_keys($this->get_staff_modules());

    $new_array = array_merge($default_modules, $additional_mods);
    $staff_modules_sql = implode(',', array_unique($new_array));

    if ($staff_modules_sql != '' or $this->has_role(array('SysAdmin', 'Admin'))) {
      if ($this->has_role('SysAdmin')) {
        $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid = schools.id AND mod_deleted IS NULL ORDER BY school, moduleID";
      } elseif ($this->has_role('Admin')) {
        $schoolIDs = implode(',', SchoolUtils::get_admin_schools($this->userID, $this->db));
        if ($schoolIDs != '') {
          $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid = schools.id AND schoolid IN ($schoolIDs) AND mod_deleted IS NULL ORDER BY school, moduleID";
        } elseif ($staff_modules_sql != '') {
          $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid = schools.id AND modules.id IN ($staff_modules_sql) AND mod_deleted IS NULL ORDER BY school, moduleID";
        }
      } else {
        $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid = schools.id AND modules.id IN ($staff_modules_sql) AND mod_deleted IS NULL ORDER BY school, moduleID";
      }

      if (isset($sql)) {
        $result = $this->db->prepare($sql);
        $result->execute();
        $result->bind_result($idMod, $moduleid, $fullname, $school);
        while ($result->fetch()) {
          $staff_modules_list[$idMod]['school'] = $school;
          $staff_modules_list[$idMod]['id'] = $moduleid;
          $staff_modules_list[$idMod]['idMod'] = $idMod;
          $staff_modules_list[$idMod]['fullname'] = $fullname;
        }
        $result->close();
      }
    }

    return $staff_modules_list;
  }

  /** 
   * loads the student modules
   *
   * @return array the student module list //TODO probably dont need the return
   */
  function load_student_modules() {
    $this->studentModules = array();

    // studentmodule year -> module ->decode
    $result = $this->db->prepare("SELECT idMod,moduleID,calendar_year FROM modules_student, modules WHERE modules_student.idMod = modules.id AND userID = ? AND modules.moduleID IS NOT NULL AND mod_deleted IS NULL ORDER BY modules.moduleID"); //SELECT userID FROM modules_student WHERE userID=? AND idMod=? AND calendar_year=?");
    $result->bind_param('i', $this->get_user_ID());
    $result->execute();

    $result->bind_result($idMod, $moduleID, $calyear);
    while ($result->fetch()) {
      $this->studentModules[$calyear][$idMod] = $moduleID;
    }
    $result->close();

    return $this->studentModules;
  }

  /**
   * checks to see is user is on a student module
   *
   * @param $moduleID an integer or string of a module
   * @param $calendar_year the calendar year being looked for
   *
   * @return bool true if student member is on a module
   */
  function is_student_user_on_module($moduleID, $calendar_year) {

    if (!$this->has_role('Student')) {
      //this is not a staff user so it cant be on any modules
      return false;
    }

    if (count($this->studentModules) < 1) {
      $this->load_student_modules();
    }

    switch (gettype($moduleID)) {
      case 'array':
        if (count($moduleID) > 1) {
          throw new Exception("is_student_user_on_module:: only accepts one module at a time.");
        }
        foreach ($moduleID as $idMod => $full_moduleID) {
          if (isset($this->studentModules[$calendar_year][$idMod])) {
            return true;
          }
        }
        break;
      case 'string':
        if (in_array($moduleID, $this->studentModules[$calendar_year])) {
          return true;
        }
        break;
      case 'integer':
        if (isset($this->studentModules[$calendar_year][$moduleID])) {
          return true;
        }
        break;
      default:
        return false;
    }

    return false;
  }

  /**
   * Enrole the student on a module.
   *
   * @param $idMod moduleID of module
   * @param $attempt
   * @param $session session of module
   * @param int $auto_update if system add
   *
   * @return bool return true if successful.
   */
  function add_student_to_module($idMod, $attempt, $session, $auto_update = 0) {
    // need to check its a self reg module

    if (module_utils::get_full_details_by_ID($idMod, $this->db) === false) {
      return false;
    }
    if (UserUtils::is_user_on_module($this, $idMod, $session, $this->db)) {
      //don't add a user to a module multiple times
      return true;
    }
    $return = UserUtils::add_student_to_module($this->get_user_ID(), $idMod, $attempt, $session, $auto_update);

    $this->load_student_modules();

    return $return;
  }


  /**
   * add current user to module as staff
   *
   * @param $idMod
   */
  function add_staff_to_module($idMod) {
    $return = UserUtils::add_staff_to_module($this->get_user_ID(), $idMod, $this->db);
    $this->load_staff_modules();

    return $return;
  }

  /**
   * remove current user to module as staff //not implimented
   *
   * @param $idMod
   */
  function remove_staff_from_module($idMod) {
    // not implimented
    trigger_error('remove_staff_from_module not yet implimented', E_USER_WARNING);
  }

  function store_original_user() {
    $data = new stdClass();
    
    $data->title            = $this->title;
    $data->initials         = $this->initials;
    $data->username         = $this->username;
    $data->email            = $this->email;
    $data->roles            = $this->roles;
    
    $this->impersonatedfrom = $data;
  }

  function impersonate($userid) {
    global $string;

    if ($this->has_role('SysAdmin')) {
      $this->store_original_user();
      $this->roles          = array();
      $this->staffModules   = array();
      $this->studentModules = array();
      $this->load($userid);
      $this->impersonate    = true;
      $this->configObj->append('cfg_install_type', " as $this->title $this->surname");
    } else {
      $notice = UserNotices::get_instance();
      $notice->access_denied($this->db, $string, $string['impersonatepriv'], true, true);
    }
  }
  
  public function is_impersonated() {
    return $this->impersonate;
  }

  function load($userID) {
    $this->userID = $userID;
    $this->impersonate = false;

    $stmt = $this->db->prepare('SELECT roles, title, initials, surname, username, email, grade, yearofstudy, special_needs FROM users WHERE user_deleted IS NULL AND id = ?');
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($this->userroles, $this->title, $this->initials, $this->surname, $this->username, $this->email, $this->grade, $this->year, $this->special_needs);
    $stmt->fetch();
    $record_no = $stmt->num_rows();
    $stmt->close();
    if ($record_no == 0) {
      return false;
    }

    // Add additional special needs data.
    if ($this->special_needs == 1) {
      $stmt = $this->db->prepare('SELECT background, foreground, textsize, extra_time, marks_color, themecolor, labelcolor, font, unanswered FROM special_needs WHERE userID = ?');
      $stmt->bind_param('i', $userID);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($this->background, $this->foreground, $this->textsize, $this->extra_time, $this->marks_color, $this->themecolor, $this->labelcolor, $this->font, $this->unanswered);
      $stmt->fetch();
      $stmt->close();
    }
    
    // Add temporary account data.
    if ($this->is_temporary_account()) {
      $stmt = $this->db->prepare('SELECT title, first_names, surname FROM temp_users WHERE assigned_account = ?');
      $stmt->bind_param('s', $this->get_username());
      $stmt->execute();
      $stmt->bind_result($this->temp_title, $this->temp_first_names, $this->temp_surname);
      $stmt->fetch();
      $stmt->close();    
    }

    $temp = explode(',', $this->userroles);

    foreach ($temp as $value) {
      $this->roles[$value] = 1;
    }
    unset($this->userroles);
  }

  function db_user_change() {
    global $db_errors, $string;

    $getback = array('cfg_db_sysadmin_user', 'cfg_db_sysadmin_passwd', 'cfg_db_admin_user', 'cfg_db_admin_passwd', 'cfg_db_staff_user', 'cfg_db_staff_passwd', 'cfg_db_student_user', 'cfg_db_student_passwd', 'cfg_db_external_user', 'cfg_db_external_passwd', 'cfg_db_inv_user', 'cfg_db_inv_passwd', 'cfg_db_database');

    $arr = $this->configObj->get($getback);
    foreach ($arr as $k => $v) {
      ${$k} = $v;
    }

    //select the aproprate database user
    if ($this->has_role('SysAdmin')) {
      $result = $this->db->change_user($cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd, $cfg_db_database);
    } elseif ($this->has_role(array('Staff', 'Admin'))) { // Process staff first to get higher priority than students
      $result = $this->db->change_user($cfg_db_staff_user, $cfg_db_staff_passwd, $cfg_db_database);
    } elseif ($this->has_role('Student')) {
      $result = $this->db->change_user($cfg_db_student_user, $cfg_db_student_passwd, $cfg_db_database);
    } elseif ($this->has_role('External Examiner')) {
      $result = $this->db->change_user($cfg_db_external_user, $cfg_db_external_passwd, $cfg_db_database);
    } elseif ($this->has_role('Invigilator')) {
      $result = $this->db->change_user($cfg_db_inv_user, $cfg_db_inv_passwd, $cfg_db_database);
    } else {
      $result = false;
      // new security routine

      $notice=UserNotices::get_instance();
      $notice->access_denied($this->db, $string, sprintf($string['denied_role'], implode(',',array_keys($this->roles))), true, true);
    }
    if($result==false) {
      $notice=UserNotices::get_instance();
      $notice->display_notice('CHANGE USER FAILED','THIS SHOULDN\'T EVER APPEAR CONTACT SUPPORT','../artwork/software_64.png');
      if ($this->db->error) {
        try {
          throw new Exception("MySQL error ".$this->db->error ."<br> ", $this->db->errno);
        } catch (Exception $e) {
          echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
          echo nl2br($e->getTraceAsString());
          exit();
        }
      }
    }
  }
  
}
