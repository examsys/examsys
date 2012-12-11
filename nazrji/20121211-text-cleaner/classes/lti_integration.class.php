<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 31/07/12
 * Time: 12:00
 * To change this template use File | Settings | File Templates.
 */
global $configObject;
require_once $cfg_web_root . 'classes/userutils.class.php';

class lti_integration {

  public $description = 'Default';
  static function load() {

    // Load the appropriate  lti integration class (if new one found load that else use this)

    global $configObject;
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
    // do nothing as no standard
  }

  static function user_time_check($time, $user='') {

    // takes laast time logged in and optionally the user and decides if reauthentication should be done (true)

    return false;
  }

  static function allow_staff_edit_link() {

    // returns true if staff should be allowed to edit a link (currently not fully coded as of 2012-09)

    return false;
  }

  static function allow_module_self_reg($data) {

    //returns true if you wish to allow self registration on modules that are self reg launched via lti

    global $cfg_lti_allow_module_self_reg;
    return $cfg_lti_allow_module_self_reg;
  }

  static function allow_staff_module_register($data) {

    // if this returns true then allow adding staff onto module team if they arent on it and launch via lti says they are teacher (Instructor)

    global $cfg_lti_allow_staff_module_register;
    return $cfg_lti_allow_staff_module_register;
  }

  static function allow_module_create($data) {

    // if this returns true then allow the creation of modules via an lti launch, module_code_translate function can convert VLE module into Rogo module(s)

    global $cfg_lti_allow_module_create;
    return $cfg_lti_allow_module_create;
  }

  static function module_code_translate($c_internal_id, $course_title = '') {

    // this function translates the incoming course code and course title it returns an array (containing possibly multiple records) of an array containing string if Manual or SMS for sms ones, the module code, a campus code (text) , school as a string (gets lookedup against rogo to get id later, a 1 for self reg enable [0 for disable] and the course title

    return array(array('Manual', $c_internal_id, 'CampusTODO', 'SchoolTODO', 0, "MISSING:$course_title"));
  }

  static function sms_api($data) {

    // this returns the sms url appropriate for the item element (inner array) of the return from module_code_translate function

    if ($data[0] != 'SMS') {
      return '';
    }
    $SMS = SmsUtils::GetSmsUtils();

    $SMS->set_module($data[2]);
    return $SMS->url;
  }

  static function module_code_translated_store($data) {

    // this takes the data array from the module_code_translate function and converts it into a string that it can store in the db for the lti context info and also read through and decode using the module_code_translate function

    return $data[0][1];
  }

}