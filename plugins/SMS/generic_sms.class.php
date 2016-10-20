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
if (!isset($cfg_web_root)) {
  $cfg_web_root = $configObject->get('cfg_web_root');
}


//updated interface to saturn using the new lookup class plugins
Class GENERIC_SMS extends SmsUtils {

  public $campus;
  public $url;

  //used to get user data but no longer used as abstracted out
  public function getUserData($username) {
//unused function now
  }

  /**
   * Retrieve the data about the module 
   * @param string $moduleID modulecode
   * @param mysqli $mysqli db connection
   * @return bool|string false on error, otherwise module data
   */
  public function get_module($moduleID, $mysqli) {

    $configObj = Config::get_instance();
    //$lookup = Lookup::get_instance($configObj, $mysqli);
    $lookup = new Lookup($configObj, $mysqli);
    $lookup->clear_debug();

    // Calculate what the current academic session is.
    $yearutils = new yearutils($mysqli);
    $session = (isset($_GET['session']) and $_GET['session'] != '') ? $_GET['session'] : $yearutils->get_current_session();

    // If session does not exist we can quit now.
    if(!$yearutils->check_calendar_year($session)) {
        return false;
    }

    $lookupdata = new stdClass();
    $lookupdata->modulecode = $moduleID;
    $lookupdata->calendar_year = $session;

    $data = new stdClass();
    $data->lookupdata = $lookupdata;
    $returned_data = $lookup->modulelookup($data);

    if ($returned_data->success === false or $returned_data->failed === true) {
      return false;
    } else {
      return $returned_data->lookupdata;
    }
  }

  /**
   * Get info about module eg school and title
   * @param string $moduleID the modulecode 
   * @param mysqli $mysqli db connection
   * @return array $moduleID the modulecode, $moduletitle the title of the module, $school the school of the module
   */
  public function get_module_info($moduleID, $mysqli) { //previous logic included in the retreival of data
    $lookupdata = $this->get_module($moduleID, $mysqli);

    if ($lookupdata === false) {
      return false;
    } else {
      if (!isset($lookupdata->moduletitle)) {
        $moduletitle = 'UNKNOWN - Lookup did not return it';
      } else {
        $moduletitle = $lookupdata->moduletitle;
      }
      if (!isset($lookupdata->school)) {
        $school = 'UNKNOWN - Lookup did not return it';
      } else {
        $school = $lookupdata->school;
      }
      return array( $moduleID, $moduletitle, $school );
    }
  }

//gets a list of enroled users for the module listed
  public function getModuleEnrolements($moduleID, $mysqli) {
    $lookupdata = $this->get_module($moduleID, $mysqli);
    foreach ($lookupdata->students as $sms) {
      $sms->Title = trim($sms->title);
      $sms->Surname = trim($sms->surname);
      $sms->Forename = trim($sms->firstname);
      $sms->CourseCode = trim($sms->coursecode);
      $sms->Username = trim($sms->username);
      $sms->Email = trim($sms->email);
      $sms->Gender = trim($sms->gender);
      $sms->YearofStudy = trim($sms->yearofstudy);
      $sms->StudentID = trim($sms->studentID);

      $lookup_username = trim($sms->username);

      // Make sure we have a proper username - it can sometimes be blank in SATURN data
      if ($sms->email != '') {
        // Try to extract from email address
        $un_parts = explode('@', $sms->email);
        $lookup_username = $un_parts[0];
      }
      $users[$lookup_username] = array($sms->Title, $sms->Surname, $sms->Forename, $sms->CourseCode, $sms->Email, $sms->Gender, $sms->YearofStudy, $sms->StudentID);
    }


    if (count($users) > 0) {
      return $users;
    } else {
      //no user found return false
      return false;
    }
  }

 // appears unused
  public function getStudentSources() {
    return array('&lt;No lookup&gt;' => '', 'Lookup' => 'lookupclass');
  }

  // returns an array with key as display name and value as item to save back to db for use with sms module sources
  public function getModuleSources() {
    return array('Lookup' => 'lookupclass');
  }


  public function set_module($location) {
    //unused in generic
  }

  //appears pointless and unused
  public function get_module_name($modulecode, $mysqli) {
    $dat = $this->getModuleEnrolements($modulecode, $mysqli);
  }



  //updates modules enrolements

  // $module & $idMod shouldnt both be needed in some respects as its a 1 to 1 relationship and $sms_api is also a parameter of the primary key in that table.
// sms_api is the sms api used for the module
// mysqli is the mysqli object
// session in the year
  public function update_module_enrolement($module, $idMod, $sms_api, $mysqli, $session = 'NOTSET') {

    $yearutils = new yearutils($mysqli);
    if ($session == 'NOTSET') {
      $session = $yearutils->get_current_session();
    }

    $enrolements = 0;
    $deletions = 0;
    $enrolement_details = '';
    $deletion_details = '';


    // Get the currently enrolled students in Rogo for the module.
    $current_users = array();
    $student_data = $mysqli->prepare("SELECT modules_student.id, users.id, username, grade, title, surname, first_names, initials, roles, yearofstudy, auto_update, sid.student_id FROM (modules_student, users) LEFT JOIN sid ON users.id = sid.userID WHERE modules_student.userID = users.id AND calendar_year = ? AND idMod = ?");
    $student_data->bind_param('si', $session, $idMod);
    $student_data->execute();
    $student_data->store_result();
    $student_data->bind_result($sm_id, $uid, $username, $grade, $title, $surname, $first_names, $initials, $roles, $year, $auto_update, $student_id);
    while ($student_data->fetch()) {
      $current_users[$username]['delete'] = $auto_update; // Set users to be deleted if added via SATURN, set otherwise lower down after checking with SMS
      $current_users[$username]['smID'] = $sm_id;
      $current_users[$username]['userID'] = $uid;
      $current_users[$username]['grade'] = $grade;
      $current_users[$username]['title'] = $title;
      $current_users[$username]['surname'] = $surname;
      $current_users[$username]['first_names'] = $first_names;
      $current_users[$username]['initials'] = $initials;
      $current_users[$username]['roles'] = $roles;
      $current_users[$username]['year'] = $year;
      $current_users[$username]['auto_update'] = $auto_update;
      $current_users[$username]['student_id'] = $student_id;
    }
    $student_data->close();

    // The replaced_module is handled internally to the new function
    $lookupdata=$this->get_module($module, $mysqli);

    if((isset($lookupdata->error) and $lookupdata->error != '')) {
      //log the issue
      $variables = array( 'lookup' => &$lookupdata );
      $this->errorinfo['moduleerrorstate'][$lookupdata->error][] = $module;
      $this->errorinfo['moduleerrorstatedata'][$lookupdata->error][] = $variables;
      $errstr = 'The module lookup for modulecode: ' . $module . ' returned an error state of ' . $lookupdata->error;
      //log_error(0, 'CRON JOB', 'Application Warning', $errstr, 'uon_saturn2.class.php', 0, '', null, $variables, null);
      if (PHP_SAPI != 'cli') {
        echo $errstr . "\r\n";
      }
    }


    // previous //is_object($xml) and !isset($xml->ErrorMessage) and !isset($xml->Module->ModuleError))

    // un inverted  the logic around to make it easier

    if ($lookupdata === false or (isset($lookupdata->error) and $lookupdata->error != '')) {
      $variables = array( 'lookup' => &$lookupdata );
      $errstr = 'No Data returned from lookup for module: ' . $module;
      $this->errorinfo['modulenodata'][]=$module;
      $this->errorinfo['modulenodatadata'][]=$variables;
      //log_error(0, 'CRON JOB', 'Application Warning', $errstr, 'uon_saturn2.class.php', 0, '', null, $variables, null);
      if (PHP_SAPI != 'cli') {
        echo $errstr . "\r\n";
      }
    } else {
      foreach ($lookupdata->students as $sms) {
        $sms->Title = trim($sms->title);
        $sms->Surname = trim($sms->surname);
        $sms->Forename = trim($sms->firstname);
        $sms->CourseCode = trim($sms->coursecode);
        $sms->Username = trim($sms->username);
        $sms->Email = trim($sms->email);
        $sms->Gender = trim($sms->gender);
        $sms->YearofStudy = trim($sms->yearofstudy);
        $sms->StudentID = trim($sms->studentID);

        $lookup_username = trim($sms->username);

        if ($lookup_username != '') {
          if (isset($current_users[$lookup_username]['delete'])) {
            $current_users[$lookup_username]['delete'] = 0; // Mark as being legitimate
          } else {
            // Student missing from Rogo module
            $student_data = $mysqli->prepare("SELECT id, yearofstudy, initials, grade, title, surname, first_names, roles, email, COALESCE(sid.student_id,'SID_ERROR') FROM users LEFT JOIN sid ON users.id = sid.userID WHERE username = ? LIMIT 1"); // Do they have a Rogo user record?
            $student_data->bind_param('s', $lookup_username);
            $student_data->execute();
            $student_data->store_result();
            $student_data->bind_result($tmp_userID, $tmp_yearofstudy, $tmp_initials, $tmp_grade, $tmp_title, $tmp_surname, $tmp_first_names, $tmp_roles, $tmp_email, $tmp_student_id);
            $student_data->fetch();

            if ($student_data->num_rows == 0) {
              // Going to have to create a whole new account for the user
              $names = explode(' ', $sms->firstname);
              $initials = '';
              foreach ($names as $tmp_name) {
                $initials .= $tmp_name[0];
              }

              $tmp_userID = UserUtils::create_user($lookup_username, '', $sms->title, $sms->firstname, $sms->surname, $sms->email, $sms->coursecode, $sms->gender, $sms->yearofstudy, 'Student', $sms->studentID, $mysqli);

              $current_users[$lookup_username]['userID'] = $tmp_userID;
              $current_users[$lookup_username]['grade'] = $sms->coursecode;
              $current_users[$lookup_username]['title'] = $sms->title;
              $current_users[$lookup_username]['surname'] = $sms->surname;
              $current_users[$lookup_username]['first_names'] = $tmp_first_names;
              $current_users[$lookup_username]['initials'] = $initials;
              $current_users[$lookup_username]['roles'] = 'Student';
              $current_users[$lookup_username]['email'] = $sms->email;
              $current_users[$lookup_username]['year'] = $sms->yearofstudy;
              $current_users[$lookup_username]['student_id'] = $sms->studentID;
              $current_users[$lookup_username]['delete'] = 0;
            } else {
              $current_users[$lookup_username]['userID'] = $tmp_userID;
              $current_users[$lookup_username]['grade'] = $tmp_grade;
              $current_users[$lookup_username]['title'] = $tmp_title;
              $current_users[$lookup_username]['surname'] = $tmp_surname;
              $current_users[$lookup_username]['first_names'] = $tmp_first_names;
              $current_users[$lookup_username]['initials'] = $tmp_initials;
              $current_users[$lookup_username]['roles'] = $tmp_roles;
              $current_users[$lookup_username]['email'] = $tmp_email;
              $current_users[$lookup_username]['year'] = $tmp_yearofstudy;
              $current_users[$lookup_username]['student_id'] = $tmp_student_id;
              $current_users[$lookup_username]['delete'] = 0;
            }
            // Add student onto the module
            $auto_update = 1; //set auto_update to student module association
            $success = UserUtils::add_student_to_module($tmp_userID, $idMod, 1, $session, $mysqli, $auto_update);

            if ($success) {
              $enrolements++;
              if ($enrolement_details == '') {
                $enrolement_details = $lookup_username;
              } else {
                $enrolement_details .= ',' . $lookup_username;
              }
            }

            $student_data->close();
          }

          // Check to see if any details of the user account need updating.

          $new_roles=trim($sms->role);

          $names = explode(' ', $sms->firstname);
          $tmp_initials = '';
          foreach ($names as $tmp_name) {
            if (isset($tmp_name[0])) {
              $tmp_initials .= $tmp_name[0];
            }
          }

          if (  $current_users[$lookup_username]['year'] != $sms->yearofstudy or
                $tmp_initials != $current_users[$lookup_username]['initials'] or
                $current_users[$lookup_username]['grade'] != $sms->coursecode or
                $current_users[$lookup_username]['title'] != $sms->title or
                $current_users[$lookup_username]['surname'] != $sms->surname  or
                $current_users[$lookup_username]['first_names'] != $sms->firstname or
                $current_users[$lookup_username]['roles'] != $new_roles or
            (isset($current_users[$lookup_username]['email']) and $current_users[$lookup_username]['email'] != $sms->email )
             ) {
              $result = $mysqli->prepare("UPDATE users SET yearofstudy = ?, roles = ?, grade = ?, title = ?, surname = ?, first_names = ?, initials = ?, email = ? WHERE username = ?");
              $result->bind_param('issssssss', $sms->yearofstudy, $new_roles, $sms->coursecode, $sms->title, $sms->surname, $sms->firstname, $tmp_initials, $sms->email, $lookup_username);
              $result->execute();
              $result->close();
          }

          // Check if SID needs updating - rare but could happen
          if ($current_users[$lookup_username]['student_id'] != $sms->studentID) {
            if ($current_users[$lookup_username]['student_id'] == 'SID_ERROR') {
              $result = $mysqli->prepare("INSERT INTO sid VALUES (?, ?)");
              $result->bind_param('si', $sms->studentID, $current_users[$lookup_username]['userID']);
              $result->execute();
              $result->close();
            } else {
              $result = $mysqli->prepare("UPDATE sid SET student_id = ? WHERE userID = ?");
              $result->bind_param('si', $sms->studentID, $current_users[$lookup_username]['userID']);
              $result->execute();
              $result->close();
            }
          }
        } else {
          $variables = array( 'lookup' => &$sms, 'currentusers' => &$current_users );
          $errstr = 'In cron job ERROR: unable to establish username for ' . $sms->title . ' ' . $sms->surname . ', ' . $sms->forename . ' (' . $sms->studentID . ')<br />';
          $this->errorinfo['unabletodetermineusername'][] = $errstr;
          $this->errorinfo['unabletodetermineusernamedata'][] = $variables;
          //log_error(0, 'CRON JOB', 'Application Warning', $errstr, 'uon_saturn2.class.php', 0, '', null, $variables, null);
          if (PHP_SAPI != 'cli') {
            echo $errstr . "\r\n";
          }
        }
      }
      // Check for any extra students in Rogo but not in SATURN for module
      foreach ($current_users as $username => $individual_user) {
        if ($individual_user['delete'] == 1 and $individual_user['auto_update'] == 1) {
          $result = $mysqli->prepare("DELETE FROM modules_student WHERE id = ?"); // Delete using primary key of 'modules_student'
          $result->bind_param('i', $individual_user['smID']);
          $result->execute();
          $result->close();
          $deletions++;
          if ($deletion_details == '') {
            $deletion_details = $username;
          } else {
            $deletion_details .= ',' . $username;
          }
        }
      }
    }

    if ($enrolements > 0 or $deletions > 0) {

        $import_type = $sms_api;

      module_utils::log_sms_imports($idMod, $enrolements, $enrolement_details, $deletions, $deletion_details, $import_type, $session, $mysqli);
    }
  }
}

?>
