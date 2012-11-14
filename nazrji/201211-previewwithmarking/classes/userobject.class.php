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
  private $password, $userID, $userroles, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $record_no, $split_username;

  private $roles, $staffModules, $db;

  /**
   * constructor
   *
   * @param $db is a mysqli link to db
   * @return none
   */
  function __construct(&$db) {
    $this->db = $db;
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
    if (strpos($this->userroles, 'Staff') !== false or strpos($userroles, 'Admin') !== false) { // Process staff first to get higher priority than students --no need
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
   * @param $moduleID an array of modules keyed on idMod
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
        if (in_array($this->staffModules, $moduleID)) {
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
   * @return the staf module list //TODO probably dont need the return
   */
  function load_staff_modules() {
    $this->staffModules = array();

    $result = $this->db->prepare("SELECT idMod,moduleID FROM modules_staff,modules WHERE modules_staff.idMod = modules.id AND memberID=? AND modules.moduleID IS NOT NULL ORDER BY modules.moduleID");
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
   * Get a list of modules the current user has access to.
   * @return array of staff module that this user has access to.
   */
  function get_staff_accessable_modules() {
    $staff_modules_list = array();

    $staff_modules_sql = implode("','", $this->get_staff_modules());
    if ($staff_modules_sql != '') $staff_modules_sql = "'$staff_modules_sql'";

    if ($staff_modules_sql != '' or $this->has_role('Admin')) {
      if ($this->has_role('SysAdmin')) {
        $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id ORDER BY school, moduleID";
      } elseif ($this->has_role('Admin')) {
        $schoolIDs = implode(',', SchoolUtils::get_admin_schools($this->userID, $this->db));
        if ($schoolIDs != '') {
          $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND schoolid IN ($schoolIDs) ORDER BY school, moduleID";
        } elseif ($staff_modules_sql != '') {
          $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid IN ($staff_modules_sql) ORDER BY school, moduleID";
        }
      } else {
        $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid IN ($staff_modules_sql) ORDER BY school, moduleID";
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

}
