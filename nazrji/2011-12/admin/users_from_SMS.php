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
* Script to obtain module enrolements from Student Management System (SMS). Run via a cron job.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  // Only run from the command line!
  if (PHP_SAPI != 'cli') {
    die("Please run this test from CLI!\n");
  }
  
  set_time_limit(0);
  $path = str_replace('/admin/users_from_SMS.php','',$_SERVER['SCRIPT_NAME']);
  if ($path == '') {
    $path = $_SERVER['DOCUMENT_ROOT'];
  }

  require_once $path . '/config/config.inc.php';
  require $path . '/classes/dateutils.class.php';
  
	//require '../classes/dateutils.class.php';
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd, $cfg_db_database);

  // Calculate what the current academic session is.
  $session = (isset($_GET['session']) and $_GET['session'] != '') ? $_GET['session'] : DateUtils::get_current_academic_year();
  $session_parts = explode('/',$session);

  $module_data = $mysqli->prepare("SELECT moduleid, sms FROM modules WHERE sms != ''");
  $module_data->execute();
  $module_data->store_result();
  $module_data->bind_result($module, $sms);
  while ($module_data->fetch()) {
    $enrolements = 0;
    $deletions = 0;
    $enrolement_details = '';
    $deletion_details = '';

    // UoN code to strip off prefix codes.
    //------------------------------------
    $replaced_module = str_replace('_UNMC','',$module);
    $replaced_module = str_replace('_UNNC','',$replaced_module);
    //------------------------------------
    
    // Get the currently enrolled students in Rogo for the module.
    $current_users = array();
    $student_data = $mysqli->prepare("SELECT student_modules.id, users.id, username, grade, title, surname, first_names, initials, roles, yearofstudy, auto_update, sid.student_id FROM (student_modules, users) LEFT JOIN sid ON users.id=sid.userID WHERE student_modules.userID=users.id AND calendar_year=? AND moduleid=? AND auto_update=1");
    $student_data->bind_param('ss', $session, $module);
    $student_data->execute();
    $student_data->store_result();
    $student_data->bind_result($sm_id, $uid, $username, $grade, $title, $surname, $first_names, $initials, $roles, $year, $auto_update, $student_id);
    while ($student_data->fetch()) {
      $current_users[$username]['delete'] = 1;   // Set all users to be deleted, set otherwise lower down after checking with SMS
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
    $returned_data = file_get_contents($sms . "&code=$replaced_module&year=" . $session_parts[0]);
    $xml = new SimpleXMLElement($returned_data);
    
    if (is_object($xml) and !isset($xml->ErrorMessage)){
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
        if ($lookup_username == '' and $sms->Email != '') {
          // Try to extract from email address
          $un_parts = explode('@', $sms->Email);
          $lookup_username = $un_parts[0];
          echo 'Extracted username from email: ' . $lookup_username . '<br />';
        }

        if ($lookup_username != '') {
          if (isset($current_users[$lookup_username]['delete'])) {
            $current_users[$lookup_username]['delete'] = 0;   // Mark as being legitimate
          } else {
            // Student missing from Rogo module
            $student_data = $mysqli->prepare("SELECT id, yearofstudy, initials, grade, title, surname, first_names, roles, COALESCE(sid.student_id,'SID_ERROR') FROM users LEFT JOIN sid ON users.id=sid.userID WHERE username=? LIMIT 1");            // Do they have a Rogo user record?
            $student_data->bind_param('s', $lookup_username);
            $student_data->execute();
            $student_data->store_result();
            $student_data->bind_result($tmp_userID, $tmp_yearofstudy, $tmp_initials, $tmp_grade, $tmp_title, $tmp_surname, $tmp_first_names, $tmp_roles, $tmp_student_id);
            $student_data->fetch();

            if ($student_data->num_rows == 0) {
              // Going to have to create a whole new account for the user
              $names = explode(' ',$sms->Forename);
              $initials = '';
              foreach ($names as $tmp_name) {
                $initials .= substr($tmp_name,0,1);
              }

              $result = $mysqli->prepare("INSERT INTO users VALUES ('',?,?,?,?,?,?,'Student',NULL,?,?,NULL,0,?)");
              $result->bind_param('ssssssssi', $sms->CourseCode, $sms->Surname, $initials, $sms->Title, $sms->Username, $sms->Email, $sms->Forename, $sms->Gender, $sms->YearofStudy);
              $result->execute();
              $result->close();

              $tmp_userID = $mysqli->insert_id;    // Get the new Rogo userID

              $result = $mysqli->prepare("INSERT INTO sid VALUES (?, ?)");
              $result->bind_param('si', $sms->StudentID, $tmp_userID);
              $result->execute();
              $result->close();

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
            $result = $mysqli->prepare("INSERT INTO student_modules VALUES (NULL, ?, ?, ?, 1, 1)");
            $result->bind_param('iss', $tmp_userID, $module, $session);
            $result->execute();
            $result->close();
            $enrolements++;
            if ($enrolement_details == '') {
              $enrolement_details = $sms->Username;
            } else {
              $enrolement_details .= ',' . $sms->Username;
            }

            $student_data->close();
          }

          // Check to see if any details of the user account need updating.
          switch ($sms->ReasonForLeaving) {
            case 'Successfully completed course':
              $new_roles = 'graduate';
              break;
            case 'Not Applicable':
              $new_roles = 'Student';
              break;
            case 'W/D (other)':
            case 'W/D (financial reasons)':
              $new_roles = 'left';
              break;
          }

          $names = explode(' ',$sms->Forename);
          $tmp_initials = '';
          foreach ($names as $tmp_name) {
            $tmp_initials .= substr($tmp_name,0,1);
          }
          if ($current_users[$lookup_username]['year'] != $sms->YearofStudy or $tmp_initials != $current_users[$lookup_username]['initials'] or $current_users[$lookup_username]['grade'] != $sms->CourseCode or $current_users[$lookup_username]['title'] != $sms->Title or $current_users[$lookup_username]['surname'] != $sms->Surname  or $current_users[$lookup_username]['first_names'] != $sms->Forename or $current_users[$lookup_username]['roles'] != $new_roles) {
            $result = $mysqli->prepare("UPDATE users SET yearofstudy=?, roles=?, grade=?, title=?, surname=?, first_names=?, initials=? WHERE username=?");
            $result->bind_param('isssssss', $sms->YearofStudy, $new_roles, $sms->CourseCode, $sms->Title, $sms->Surname, $sms->Forename, $tmp_initials, $sms->Username);
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
    }
    
    // Check for any extra students in Rogo but not in SATURN for module
    foreach ($current_users as $username=>$individual_user) {
      if ($individual_user['delete'] == 1 and $individual_user['auto_update'] == 1) {
        $result = $mysqli->prepare("DELETE FROM student_modules WHERE id=?");         // Delete using primary key of 'student_modules'
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
    
    if ($enrolements > 0 or $deletions > 0) {
      if ($sms == 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia') {
        $import_type = 'SATURN Malaysia';
      } elseif ($sms == 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=china') {
        $import_type = 'SATURN China';
      } else {
        $import_type = 'SATURN UK';
      }

      $result = $mysqli->prepare("INSERT INTO sms_imports VALUES (NULL, NOW(), ?, ?, ?, ?, ?, ?)");
      $result->bind_param('sisiss', $module, $enrolements, $enrolement_details, $deletions, $deletion_details, $import_type);
      $result->execute();
      $result->close();
    }
    
  }
  $module_data->close();
  
  $mysqli->close();
?>