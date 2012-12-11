<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 05/11/12
 * Time: 11:32
 *
 *
 * UserObject Class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

/**
 *
 * class for the currently logged in user and any functions related to this
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */
require_once $cfg_web_root . 'classes/schoolutils.class.php';

class UserObject {

  // include old variables as private ones in this class
  /**
   * @var
   */
  private $password, $userID, $userroles, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $record_no, $split_username;

  private $roles, $staffModules, $studentModules, $db, $configObj;

  /**
   * constructor
   *
   * @param $db is a mysqli link to db
   * @param $configObject a Rogo config object populated from config.inc
   * @return none
   */
  function __construct($configObject, &$db) {
    $this->db = $db;
    $this->configObj = $configObject;
  }

  /**
   * TEMP Function loads old style data in - a temp translation function
   *
   * @param $array array of data in old format
   * @return array
   */
  function old_load($array) {
    list($this->password, $this->userID, $this->userroles, $this->title, $this->initials, $this->surname, $this->username, $this->email, $this->grade, $this->year, $this->special_needs, $this->record_no, $this->split_username) = $array;

    if (strpos($this->userroles, 'SysAdmin') !== false) {
      $this->roles['SysAdmin'] = 1;
    }
    if (strpos($this->userroles, 'Admin') !== false and strpos($this->userroles, 'SysAdmin') === false) {
      $this->roles['Admin'] = 1;
    }
    if (strpos($this->userroles, 'Staff') !== false or strpos($this->userroles, 'Admin') !== false) { // Process staff first to get higher priority than students --no need
      $this->roles['Staff'] = 1;
    }
    if (strpos($this->userroles, 'Student') !== false) {
      $this->roles['Student'] = 1;
    }
    if (strpos($this->userroles, 'External Examiner') !== false) {
      $this->roles['ExternalExaminer'] = 1;
    }
    if (strpos($this->userroles, 'Invigilator') !== false) {
      $this->roles['Invigilator'] = 1;
    }
  }

  /**
   * TEMP Function exports user roles in old style
   *
   * @param $array array of data in old format
   * @return list of roles
   */
  function old_getuserroles() {
    return $this->userroles;
  }

  /**
   * checks if user has role(s) specified
   *
   * @param $roles either a string or an array of strings
   * @param $exclusive if this should only have this role
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
   * @param string $moduleID an array of modules keyed on idMod
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
      default:
        return false;
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

    $result = $this->db->prepare("SELECT idMod, moduleID FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND memberID=? AND modules.moduleID IS NOT NULL ORDER BY modules.moduleID");
    if ($this->db->error) {
      try {
        throw new Exception("0MySQL error $mysqli->error <br> Query:<br> $query", $msqli->errno);
      } catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br >";
        echo nl2br($e->getTraceAsString());
      }
    }
    $result->bind_param('i', $this->userID);
    $result->execute();
    $result->bind_result($idMod, $moduleID);
    while ($result->fetch()) {
      $this->staffModules[$idMod] = $moduleID;
    }
    $result->close();

    return $this->staffModules;
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
   * @return the grade
   */
  function get_grade() {
    return $this->grade;
  }

  /**
   * Return the user's title
   * @return string Title
   */
  function get_title() {
    return $this->title;
  }

  /**
   * Return the user's initials
   * @return string Initials
   */
  function get_initials() {
    return $this->initials;
  }

  /**
   * Return the user's surname
   * @return string Surname
   */
  function get_surname() {
    return $this->surname;
  }

  /**
   * Return the user's username
   * @return string username
   */
  function get_username() {
    return $this->username;
  }

  /**
   * Get a list of modules the current user has access to.
   * @return array of staff module that this user has access to.
   */
  function get_staff_accessable_modules($additional_mods = array()) {
    $staff_modules_list = array();

    $staff_modules_sql = implode(',', array_keys($this->get_staff_modules()));
    $default_modules = array_keys($this->get_staff_modules());
    
    $new_array = array_merge($default_modules, $additional_mods);
    $staff_modules_sql = implode(',', array_unique($new_array));

    if ($staff_modules_sql != '' or $this->has_role('Admin')) {
      if ($this->has_role('SysAdmin')) {
        $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id ORDER BY school, moduleID";
      } elseif ($this->has_role('Admin')) {
        $schoolIDs = implode(',', SchoolUtils::get_admin_schools($this->userID, $this->db));
        if ($schoolIDs != '') {
          $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND schoolid IN ($schoolIDs) ORDER BY school, moduleID";
        } elseif ($staff_modules_sql != '') {
          $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND modules.id IN ($staff_modules_sql) ORDER BY school, moduleID";
        }
      } else {
        $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND modules.id IN ($staff_modules_sql) ORDER BY school, moduleID";
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

  /** loads the student modules
   *
   * @return array the student module list //TODO probably dont need the return
   */
  function load_student_modules() {
    $this->studentModules = array();

    // studentmodule year -> module ->decode
    $result = $this->db->prepare("SELECT idMod,moduleID,calendar_year FROM modules_student,modules WHERE modules_student.idMod = modules.id AND userID = ? AND modules.moduleID IS NOT NULL ORDER BY modules.moduleID"); //SELECT userID FROM modules_student WHERE userID=? AND idMod=? AND calendar_year=?");
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
   * @param $moduleID an integer or string of a module
   * @param $calendar_year the calendar year being looked for
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
   * @param $idMod
   */
  function add_staff_to_module($idMod) {
    $return = UserUtils::add_staff_to_module($this->get_user_ID(), $idMod, $this->db);
    $this->load_staff_modules();
    
    return $return;
  }

  /**
   * remove current user to module as staff //not implimented
   * @param $idMod
   */
  function remove_staff_from_module($idMod) {
    // not implimented
    trigger_error('remove_staff_from_module not yet implimented', E_USER_WARNING);
  }

  function load($userID) {
    $stmt = $this->db->prepare("SELECT roles, title, initials, surname, username, email, grade, yearofstudy, special_needs FROM users WHERE user_deleted IS NULL AND id=?");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($this->userroles, $this->title, $this->initials, $this->surname, $this->username, $this->email, $this->grade, $this->year, $this->special_needs);
    $stmt->fetch();
    $record_no = $stmt->num_rows();
    $stmt->close();
    if($record_no==0) {
      return false;
    }

    if (strpos($this->userroles, 'SysAdmin') !== false) {
      $this->roles['SysAdmin'] = 1;
    }
    if (strpos($this->userroles, 'Admin') !== false and strpos($this->userroles, 'SysAdmin') === false) {
      $this->roles['Admin'] = 1;
    }
    if (strpos($this->userroles, 'Staff') !== false or strpos($this->userroles, 'Admin') !== false) { // Process staff first to get higher priority than students --no need
      $this->roles['Staff'] = 1;
    }
    if (strpos($this->userroles, 'Student') !== false) {
      $this->roles['Student'] = 1;
    }
    if (strpos($this->userroles, 'External Examiner') !== false) {
      $this->roles['ExternalExaminer'] = 1;
    }
    if (strpos($this->userroles, 'Invigilator') !== false) {
      $this->roles['Invigilator'] = 1;
    }

  }

  function db_user_change() {
    global $db_errors, $string;

    $getback=array('cfg_db_sysadmin_user', 'cfg_db_sysadmin_passwd','cfg_db_admin_user', 'cfg_db_admin_passwd','cfg_db_staff_user', 'cfg_db_staff_passwd','cfg_db_student_user', 'cfg_db_student_passwd','cfg_db_external_user', 'cfg_db_external_passwd','cfg_db_inv_user', 'cfg_db_inv_passwd', 'cfg_db_database');

    $arr=$this->configObj->get($getback);
    foreach($arr as $k=>$v) {
      ${$k}=$v;
    }

    $userroles = $this->old_getuserroles();
    //select the aproprate database user
    if ($this->has_role('SysAdmin')) {
      $result = $this->db->change_user($cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd, $cfg_db_database);
    } else if ($this->has_role(array('Staff','Admin'))) { // Process staff first to get higher priority than students
      $result = $this->db->change_user($cfg_db_staff_user, $cfg_db_staff_passwd, $cfg_db_database);
    } else if ($this->has_role('Student')) {
      $result = $this->db->change_user($cfg_db_student_user, $cfg_db_student_passwd, $cfg_db_database);
    } else if ($this->has_role('External Examiner')) {
      $result = $this->db->change_user($cfg_db_external_user, $cfg_db_external_passwd, $cfg_db_database);
    } else if ($this->has_role('Invigilator')) {
      $result = $this->db->change_user($cfg_db_inv_user, $cfg_db_inv_passwd, $cfg_db_database);
    } else {
      $result = false;
    }
  }
}
