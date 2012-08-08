<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 31/07/12
 * Time: 12:00
 * To change this template use File | Settings | File Templates.
 */

require_once $cfg_web_root . 'classes/userutils.class.php';

class lti_integration_extended extends lti_integration {

  static function user_add($username, $password) {
    // take user and password and add user to system

    // UoN version looks up data via ldap and correctly gets the correct fields and adds them into the system

    global $mysqli;

    $data = array();

    $returned = ldap_lookup($username, $password, $data, 1);
    if ($returned === false) {
      // no ldap user found
      return false;
    }

    $title = $returned[0]['title'][0];
    $forname = $returned[0]['givenname'][0];
    $surname = $returned[0]['sn'][0];
    $email = $returned[0]['uonprimaryemailalias'][0];
    $initials = $returned[0]['initials'][0];
    $employeetype = $returned[0]['employeetype'][0];

    if ($employeetype == 'S') {
      //staff
      $course = 'University Lecturer';
      $role = 'Staff';
      $year = 1;
      $gender = '';
      $id = UserUtils::createUser($username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $mysqli);
    }
    else {
      $user_data = $SMS->getUserData($username);
      if (count($user_data) > 0) {
        //valid acount found create user
        UserUtils::createUser(
          $username,
          $password,
          $user_data['Title'],
          $user_data['Forename'],
          $user_data['Surname'],
          $user_data['Email'],
          $user_data['CourseCode'],
          $user_data['Gender'],
          $user_data['YearofStudy'],
          'Student',
          $user_data['StudentID'],
          $mysqli
        );
      }
      else {
        //error looking up student
        display_error($string['noaccountfound'], '', false, true);
      }
    }


  }


  static function user_time_check($time) {
    $time1 = strtotime($time);
    $time2 = time();
    $timediff = $time2 - $time1;
    //  if($timediff>(60*60*24*7*10)) {
    if ($timediff > (60 * 60 * 1)) {
      return true;
    }
    return false;
  }

  static function allow_staff_edit_link() {
    return false;
  }

  static function allow_module_self_reg($module_id) {
    return true;
  }

  static function module_code_translate($c_internal_id, $course_title = "") {

    // only get the shortname through  (courseID is only probably accessible via specific moodle webservices api

    // shortname for real module try XXXXXX-YY-ZZZWWWW  WHERE XXXXXX is saturn code YY is country rest we dont care about.

    // shortname for non module VV-XXXXX-XXXXX-YY-WWWW WHERE XXXXXXXXXX is the fake 'module code'  YYY is country VV is DEPT 2 letter code

    // shortname for metamodules is XXXXXX-YY-XXXXXX-YY-XXXXXXX-YYY-ZZZWWWWW where the set of XXXXXX, YY are unknown

    // convert vle module format into rogo format

    $exploded = explode('-', $c_internal_id);

    $length = strlen($exploded[0]);


    $fin = strlen($course_title);


    if (strpos($course_title, '(') !== false) $fin = strpos($course_title, '(') - 1;
    $course_title = substr($course_title, 0, $fin);
    if ($length < 6) {
      //not saturn code
      $campus = '';
      //this should mean its a fake course
      $modcode = '';
      for ($a = 1; $a < count($exploded); $a++) {
        if (in_array(strtoupper($exploded[$a]), array('UK', 'MY', 'CN'))) {
          $campus = strtoupper($exploded[$a]);
          break;
        }
        $modcode = $modcode . '-' . $exploded[$a];
      }
      $modcode = substr($modcode, 1);
      $schoolname = 'LTI';
      if (isset($lti_school_list_lookup[$exploded[0]])) {
        $schoolname = $lti_school_list_lookup[$exploded[0]];
      }
      $selfreg = 1;
      $data[] = array('Manual', $modcode, $campus, $schoolname, $selfreg, $course_title);


    }
    else {
      $a = 0;
      $b = 0;
      $data = array();
      $selfreg = 0;
      while (isset($exploded[$a])) {
        if (strlen($exploded[$a]) == 6) {
          //saturn codes are 6 chars
          // data is

          $data[$b++] = array('SMS', $exploded[$a], 'CampusTODO', 'SchoolTODO', $selfreg, "MISSING:$course_title");
        }
        elseif (strlen($exploded[$a]) == 2) {
          // probably campus check
          if (in_array(strtoupper($exploded[$a]), array('UK', 'MY', 'CN'))) {
            for ($c = 0; $c < $b; $c++) {
              if ($data[$c][2] == 'CampusTODO') {
                $data[$c][2] = strtoupper($exploded[$a]);
              }
            }
          }
        }
        $a++;
      }
    }


    // returning an array containing an array, description of inner array
    // first is 'Manual' or 'SMS' indicating if its not or it is a manual add or a live SMS based module
    // second is the module code
    // third is campus
    // fourth is School it belongs to as text
    // fifth is if its self registration module
    // sixth is the module title.  if it starts MISSING: then there is need for manual intervention to complete this correctly

    foreach ($data as $k => $v) {
 //     $returned = lookup_module_description($v);
 //      $data[$k] = $returned;
    }
    // return the data
    return $data;
  }

  static function lookup_module_description($moduledata) {
    return $moduledata;
  }

  static function determine_std_module($modulecode, $campus, $year, $semstart) {
    // return false if not, else return string with a campus.
    return false;
  }


}