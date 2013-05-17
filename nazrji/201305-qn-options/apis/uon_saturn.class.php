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

require_once $configObject->get('cfg_web_root') . '/classes/dateutils.class.php';

Class UON_SATURN extends SmsUtils {

  public $campus;
  public $url;

  function getUserData($username) {
    $user = array();
    $sources = $this->getStudentSources();
    foreach ($sources as $name => $source) {
      if ($source != '') {
        $returned_data = file_get_contents($source . "&username=$username");
        $xml = new SimpleXMLElement($returned_data);
        if ($xml->AttendStatus == 'Studying at the University') {
          $user['StudentID'] = trim($xml->StudentID);
          $user['Title'] = trim($xml->Title);
          $user['Surname'] = trim($xml->Surname);
          $user['Forename'] = trim($xml->Forename);
          $user['CourseCode'] = trim($xml->CourseCode);
          $user['Username'] = '';
          $user['Email'] = trim($xml->Email);
          $user['Gender'] = trim($xml->Gender);
          $user['YearofStudy'] = trim($xml->YearofStudy);
          $user['School'] = trim($xml->School);
          $user['Degree'] = trim($xml->Degree);
          $user['CourseCode'] = trim($xml->CourseCode);
          $user['CourseTitle'] = trim($xml->CourseTitle);
          $user['AttendStatus'] = trim($xml->AttendStatus);
          break; //we have found the student so stop looking
        }
      }
    }

    if (count($user) > 0) {
      return $user;
    } else {
      //no user found return false
      return false;
    }
  }

  function get_module($moduleID) {
    $users = array();

    // Calculate what the current academic session is.
    $session = (isset($_GET['session']) and $_GET['session'] != '') ? $_GET['session'] : date_utils::get_current_academic_year();
    $session_parts = explode('/', $session);
    $replaced_module = str_replace('_UNMC', '', $moduleID);
    $replaced_module = str_replace('_UNNC', '', $replaced_module);


    if ($this->url !== '') {
      $returned_data = @file_get_contents($this->url . "&code=$replaced_module&year=" . $session_parts[0]);
    } else {
      $returned_data = false;
    }
    if ($returned_data !== false) {

      $xml = @new SimpleXMLElement($returned_data);
      if (is_object($xml) and !isset($xml->ErrorMessage)) {
        return $xml;
      } else {
        return false;
      }
    } else {
      return false;

    }
  }

  function get_module_info($moduleID) {
    $xml = $this->get_module($moduleID);

    if (is_object($xml) and !(isset($xml->ErrorMessage) or isset($xml->Module->Error) or isset($xml->Module->ModuleError))) {
      $moduletitle = (string)$xml->Module->ModuleTitle;
      $school = 'SchoolMissing';
      if (isset($xml->Module->Schools)) {

        foreach ($xml->Module->Schools->children() as $v) {

          if (isset($v->AdministeredBy)) {
            $school = (string)$v->AdministeredBy;
            break;
          }
          if (isset($v->ContributedToBy)) {
            $school = (string)$v->ContributedToBy;
          }

        }


      }
      return array($moduleID, $moduletitle, $school);
    } else {
      return false;
    }
  }

  function getModuleEnrolements($moduleID) {
    $xml = $this->get_module($moduleID);
    foreach ($xml->Module->Membership->Student as $sms) {
      $sms->Title = trim($sms->Title);
      $sms->Surname = trim($sms->Surname);
      $sms->Forename = trim($sms->Forename);
      $sms->CourseCode = trim($sms->CourseCode);
      $sms->Username = trim($sms->Username);
      $sms->Email = trim($sms->Email);
      $sms->Gender = trim($sms->Gender);
      $sms->YearofStudy = trim($sms->YearofStudy);
      $sms->StudentID = trim($sms->StudentID);

      $lookup_username = trim($sms->Username);

      // Make sure we have a proper username - it can sometimes be blank in SATURN data
      if ($sms->Email != '') {
        // Try to extract from email address
        $un_parts = explode('@', $sms->Email);
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

  function getStudentSources() {
    return array('&lt;No lookup&gt;' => '', 'UK' => 'http://saturn-exports.nottingham.ac.uk/touchstonestudent.ashx?campus=uk', 'Malaysia' => 'http://saturn-exports.nottingham.ac.uk/touchstonestudent.ashx?campus=malaysia', 'China' => 'http://saturn-exports.nottingham.ac.uk/touchstonestudent.ashx?campus=china');
  }

  function getModuleSources() {
    return array('&lt;No lookup&gt;' => '', 'UK' => 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=uk', 'Malaysia' => 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia', 'China' => 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=china');
  }


  function set_module($location) {
    if ($location == 'MY') {
      $location = 'Malaysia';
    } elseif ($location == 'CN') {
      $location = 'China';
    } elseif ($location == 'UK') {
      $location = 'UK';
    }
    $arr = $this->getModuleSources();
    if (!isset($arr[$location])) {
      $this->url = '';
      $this->campus = $location;
      return;
    }
    $this->url = $arr[$location];
    $this->campus = $location;
  }

  function get_module_name($modulecode) {
    $dat = $this->getModuleEnrolements($modulecode);
  }

  function update_module_enrolement($module, $idMod, $sms_api, $mysqli = 'NOTSET', $session = 'NOTSET') {

    // run module enrolement for select code
    if ($mysqli == 'NOTSET') {
      global $mysqli;
    }

    if ($session == 'NOTSET') {
      $session = date_utils::get_current_academic_year();
    }
    $session_parts = explode('/', $session);

    $enrolements = 0;
    $deletions = 0;
    $enrolement_details = '';
    $deletion_details = '';

    // UoN code to strip off prefix codes.
    //------------------------------------
    $replaced_module = str_replace('_UNMC', '', $module);
    $replaced_module = str_replace('_UNNC', '', $replaced_module);
    //------------------------------------

    // Get the currently enrolled students in Rogo for the module.
    $current_users = array();
    $student_data = $mysqli->prepare("SELECT modules_student.id, users.id, username, grade, title, surname, first_names, initials, roles, yearofstudy, auto_update, sid.student_id FROM (modules_student, users) LEFT JOIN sid ON users.id = sid.userID WHERE modules_student.userID = users.id AND calendar_year = ? AND idMod = ? AND auto_update = 1");
    $student_data->bind_param('si', $session, $idMod);
    $student_data->execute();
    $student_data->store_result();
    $student_data->bind_result($sm_id, $uid, $username, $grade, $title, $surname, $first_names, $initials, $roles, $year, $auto_update, $student_id);
    while ($student_data->fetch()) {
      $current_users[$username]['delete'] = 1; // Set all users to be deleted, set otherwise lower down after checking with SMS
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

    // Look up SMS
    $returned_data = @file_get_contents($sms_api . "&code=$replaced_module&year=" . $session_parts[0]);
    $xml = false;
    if ($returned_data !== false) {
      $xml = new SimpleXMLElement($returned_data);
    }

    if (is_object($xml) and !isset($xml->ErrorMessage) and !isset($xml->Module->ModuleError)) {
      foreach ($xml->Module->Membership->Student as $sms) {
        $sms->Title = trim($sms->Title);
        $sms->Surname = trim($sms->Surname);
        $sms->Forename = trim($sms->Forename);
        $sms->CourseCode = trim($sms->CourseCode);
        $sms->Username = trim($sms->Username);
        $sms->Email = trim($sms->Email);
        $sms->Gender = trim($sms->Gender);
        $sms->YearofStudy = trim($sms->YearofStudy);
        $sms->StudentID = trim($sms->StudentID);

        $lookup_username = trim($sms->Username);

        // Make sure we have a proper username - it can sometimes be blank in SATURN data
        if ($sms->Email != '') {
          // Try to extract from email address
          $un_parts = explode('@', $sms->Email);
          $lookup_username = $un_parts[0];
        }

        if ($lookup_username != '') {
          if (isset($current_users[$lookup_username]['delete'])) {
            $current_users[$lookup_username]['delete'] = 0; // Mark as being legitimate
          } else {
            // Student missing from Rogo module
            $student_data = $mysqli->prepare("SELECT id, yearofstudy, initials, grade, title, surname, first_names, roles, COALESCE(sid.student_id,'SID_ERROR') FROM users LEFT JOIN sid ON users.id = sid.userID WHERE username = ? LIMIT 1"); // Do they have a Rogo user record?
            $student_data->bind_param('s', $lookup_username);
            $student_data->execute();
            $student_data->store_result();
            $student_data->bind_result($tmp_userID, $tmp_yearofstudy, $tmp_initials, $tmp_grade, $tmp_title, $tmp_surname, $tmp_first_names, $tmp_roles, $tmp_student_id);
            $student_data->fetch();

            if ($student_data->num_rows == 0) {
              // Going to have to create a whole new account for the user
              $names = explode(' ', $sms->Forename);
              $initials = '';
              foreach ($names as $tmp_name) {
                $initials .= $tmp_name[0];
              }

              $tmp_userID = UserUtils::create_user($lookup_username, '', $sms->Title, $sms->Forename, $sms->Surname, $sms->Email, $sms->CourseCode, $sms->Gender, $sms->YearofStudy, 'Student', $sms->StudentID, $mysqli);

              $current_users[$lookup_username]['userID'] = $tmp_userID;
              $current_users[$lookup_username]['grade'] = $sms->CourseCode;
              $current_users[$lookup_username]['title'] = $sms->Title;
              $current_users[$lookup_username]['surname'] = $sms->Surname;
              $current_users[$lookup_username]['first_names'] = $tmp_first_names;
              $current_users[$lookup_username]['initials'] = $initials;
              $current_users[$lookup_username]['roles'] = 'Student';
              $current_users[$lookup_username]['year'] = $sms->YearofStudy;
              $current_users[$lookup_username]['student_id'] = $sms->StudentID;
              $current_users[$lookup_username]['delete'] = 0;
            } else {
              $current_users[$lookup_username]['userID'] = $tmp_userID;
              $current_users[$lookup_username]['grade'] = $tmp_grade;
              $current_users[$lookup_username]['title'] = $tmp_title;
              $current_users[$lookup_username]['surname'] = $tmp_surname;
              $current_users[$lookup_username]['first_names'] = $tmp_first_names;
              $current_users[$lookup_username]['initials'] = $tmp_initials;
              $current_users[$lookup_username]['roles'] = $tmp_roles;
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
          if (strtoupper(substr($sms->ReasonForLeaving,0,3)) == 'W/D') {
            $new_roles = 'left';
          } elseif ($sms->ReasonForLeaving == 'Successfully completed course') {
            $new_roles = 'graduate';
          } else {
            $new_roles = 'Student';
          }

          $names = explode(' ', $sms->Forename);
          $tmp_initials = '';
          foreach ($names as $tmp_name) {
            if (isset($tmp_name[0])) {
              $tmp_initials .= $tmp_name[0];
            }
          }
          if ($current_users[$lookup_username]['year'] != $sms->YearofStudy or $tmp_initials != $current_users[$lookup_username]['initials'] or $current_users[$lookup_username]['grade'] != $sms->CourseCode or $current_users[$lookup_username]['title'] != $sms->Title or $current_users[$lookup_username]['surname'] != $sms->Surname  or $current_users[$lookup_username]['first_names'] != $sms->Forename or $current_users[$lookup_username]['roles'] != $new_roles) {
            $result = $mysqli->prepare("UPDATE users SET yearofstudy = ?, roles = ?, grade = ?, title = ?, surname = ?, first_names = ?, initials = ? WHERE username = ?");
            $result->bind_param('isssssss', $sms->YearofStudy, $new_roles, $sms->CourseCode, $sms->Title, $sms->Surname, $sms->Forename, $tmp_initials, $lookup_username);
            $result->execute();
            $result->close();
          }

          // Check if SID needs updating - rare but could happen
          if ($current_users[$lookup_username]['student_id'] != $sms->StudentID) {
            if ($current_users[$lookup_username]['student_id'] == 'SID_ERROR') {
              $result = $mysqli->prepare("INSERT INTO sid VALUES (?, ?)");
              $result->bind_param('si', $sms->StudentID, $current_users[$lookup_username]['userID']);
              $result->execute();
              $result->close();
            } else {
              $result = $mysqli->prepare("UPDATE sid SET student_id=? WHERE userID=?");
              $result->bind_param('si', $sms->StudentID, $current_users[$lookup_username]['userID']);
              $result->execute();
              $result->close();
            }
          }
        } else {
          echo 'ERROR: unable to establish username for ' . $sms->Forename . ' ' . $sms->Surname . ' (' . $sms->Email . ')<br />';
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
      if ($sms_api == 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia') {
        $import_type = 'SATURN Malaysia';
      } elseif ($sms_api == 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=china') {
        $import_type = 'SATURN China';
      } else {
        $import_type = 'SATURN UK';
      }

      $result = $mysqli->prepare("INSERT INTO sms_imports VALUES (NULL, NOW(), ?, ?, ?, ?, ?, ?)");
      $result->bind_param('sisiss', $idMod, $enrolements, $enrolement_details, $deletions, $deletion_details, $import_type);
      $result->execute();
      $result->close();

      @ob_flush();
      @flush();
    }
  }
}

?>