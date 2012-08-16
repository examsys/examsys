<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 31/07/12
 * Time: 12:00
 * To change this template use File | Settings | File Templates.
 */
global $cfg_web_root;
require_once $cfg_web_root . 'classes/userutils.class.php';

class lti_integration {

  static function load() {
    global $cfg_web_root;
    if (file_exists($cfg_web_root . 'config/integration/lti_integration.class.php')) {
      require_once $cfg_web_root . 'config/integration/lti_integration.class.php';
      return new lti_integration_extended();
    }
    else {
      return new lti_integration();
    }
  }


  static function user_add($username, $password) {
    // take user and password and add user to system

  }

  static function user_time_check($time) {
    return false;
  }

  static function allow_staff_edit_link() {
    return false;
  }

  static function allow_module_self_reg($module_id) {
    return true;
  }

  function module_code_translate($c_internal_id, $course_title = '') {
    return array('Manual', $c_internal_id, 'CampusTODO', 'SchoolTODO', 0, "MISSING:$course_title");
  }

  static function determine_std_module($modulecode, $campus, $year, $semstart) {
    // return false if not, else return string with a campus.
  }

}