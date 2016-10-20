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
* User related functions
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
ini_set("auto_detect_line_endings", true);

$header = array();
$needed_cols1 = array('student_id','first names','surname','title','course code','year of course','local email');
$needed_cols2 = array('id','forenames','family name','title','course','year of study','email');
$optional_cols = array('type','modules','session');

function add_users_from_file($fileName) {
  global $header, $needed_cols1, $needed_cols2, $optional_cols, $mysqli, $string;

  $courses = array();
  $users[] = array();

  $users['html'] = "<table style=\"width:100%\">\n";

  $lines = file($fileName);
  foreach ($lines as $separate_line) {
    if (strpos($separate_line,'","') !== false) {
      $separate_line = '","' . $separate_line . ',"';
      $split_on = '","';
    } else {
      $split_on = ',';
    }
    $separate_line = str_replace("\n","",$separate_line);
    $separate_line = str_replace("\r","",$separate_line);

    if (count($header) == 0) {
      //build the header array
      $tmp_orig_header = explode($split_on, $separate_line);
      $separate_line = strtolower($separate_line);
      $error = array();
      $tmp_header = explode($split_on, $separate_line);
      foreach ($tmp_header as $tmp_string) {
        $tmp_string = str_replace('"', '', $tmp_string);
        $header[] = $tmp_string;
      }
      $header = array_flip($header);
      for ($i=0; $i<count($needed_cols1); $i++) {
        if (!isset($header[$needed_cols1[$i]]) and !isset($header[$needed_cols2[$i]])) {
          $error['error'][] = "<li>" . sprintf($string['missingcolumn'], $needed_cols1[$i]) . "</li>";
        }
      }

      $i = 0;
      $col_errors = array();
      $users['html'] .= '<tr>';
      $users['html'] .= "<th></th>";
      foreach ($tmp_orig_header as $field) {
        $tmp_field = strtolower(str_replace('"', '', $field));
        if (in_array($tmp_field, $needed_cols1) or in_array($tmp_field, $needed_cols2) or in_array($tmp_field, $optional_cols)) {
          $users['html'] .= "<th>$field</th>";
        } else {
          $users['html'] .= "<th style=\"background-color:#FFC0C0\">$field</th>";
          $col_errors[$i] = true;
        }
        $i++;
      }
      $users['html'] .= '</tr>';

      if (count($error) > 0) {
        return $error;
      } else {
        continue;
      }
    }

    $fields = explode($split_on, $separate_line);
    if (isset($header['type'])) {
      $role = str_replace('"','',trim($fields[$header['type']]));
    } else {
      $role = 'Student';
    }
    if (isset($header['student_id'])) {
      $sid = str_replace('"','',trim($fields[$header['student_id']]));
    } else {
      $sid = str_replace('"','',trim($fields[$header['id']]));
    }
    if (isset($header['first names'])) {
      $forname = UserUtils::my_ucwords(trim($fields[$header['first names']]));
    } else {
      $forname = UserUtils::my_ucwords(trim($fields[$header['forenames']]));
    }
    $initial = explode(' ',$forname);
    $initials = '';
    foreach ($initial as $name) {
      $initials .= substr($name,0,1);
    }
    $initials = strtoupper($initials);
    if (isset($header['surname'])) {
      $surname = UserUtils::my_ucwords(trim($fields[$header['surname']]));
    } else {
      $surname = UserUtils::my_ucwords(trim($fields[$header['family name']]));
    }
    $title = UserUtils::my_ucwords(trim($fields[$header['title']]));
    if (isset($header['course code'])) {
      $course = trim($fields[$header['course code']]);
    } else {
      $course = trim($fields[$header['course']]);
    }
    if (isset($header['year of course'])) {
      $year = trim($fields[$header['year of course']]);
    } else {
      $year = trim($fields[$header['year of study']]);
    }
    if (isset($header['local email'])) {
      $email = trim($fields[$header['local email']]);
    } else {
      $email = trim($fields[$header['email']]);
    }
    if (isset($header['username'])) {
      // If we have a username, use it.
      $username = $fields[$header['username']];
    } else {
      // If we don't have a username, use the part before the @ in the email address.
      $username = explode("@",$email);
      $username = $username[0];
    }

    if ($title == 'Mr') {
      $gender = 'Male';
    } elseif ($title == 'Miss' or $title == 'Mrs' or $title == 'Ms') {
      $gender = 'Female';
    } elseif ($title == 'Mx') {
      $gender = 'Other';
    } else {
      $gender = 'NULL';
    }

    if (isset($header['password']) and $fields[$header['password']] != "") {
      //Check that we have a password header, and the password is not blank for this user
      $password = $fields[$header['password']];
    } else {
      // No password was provided, set it to '' and one will be generated by UserUtils::create_user
      $password = '';
    }

    // Override some fields if user type is Staff.
    if (strtolower($role) == 'staff') {
      $role = 'Staff';
      $course = 'University Lecturer';
      $year = 1;
    } elseif (strtolower($role) == 'external examiner') {
      $role = 'External Examiner';
      $course = 'Staff External Examiner';
      $year = 1;
    } elseif (strtolower($role) == 'internal reviewer') {
      $role = 'Internal Reviewer';
      $course = 'Staff Internal Reviewer';
      $year = 1;
    }

    $invalid_user = false;
    $tmp_userID = UserUtils::username_exists($username, $mysqli);

    $users['html'] .= '<tr>';
    if ($tmp_userID === false) {
      $users['html'] .= '<td><img src="../artwork/green_plus_16.png" width="16" height="16" alt="Add" /></td>';
    } else {
      $users['html'] .= '<td><img src="../artwork/arrow_circle_double.png" width="16" height="16" alt="Update" /></td>';
    }
    $i = 0;
    foreach ($fields as $field) {
      if (isset($col_errors[$i])) {
        $users['html'] .= "<td style=\"background-color:#FFC0C0\">$field</td>";
      } else {
        $users['html'] .= "<td>$field</td>";
      }
      $i++;
    }
    $users['html'] .= '</tr>';

    if ($tmp_userID === false) {
      //add new users

      $tmp_userID = UserUtils::create_user( $username,
                                           $password,
                                           $title,
                                           $forname,
                                           $surname,
                                           $email,
                                           $course,
                                           $gender,
                                           $year,
                                           $role,
                                           $sid,
                                           $mysqli
                                          );

      if ($tmp_userID !== false) {
        $users['added'][] = $username;
      }
      $users['courses'][$course] = $course;

      //send Welcome Email if ticked
      if (isset($_POST['welcome']) and $_POST['welcome'] != '') {
        send_welcome_email($title, $surname, $username, $password, $email);
      }
    } elseif ($username != '' and stristr('ps_', $username) === false) {
      //update existing student records

      if ($password != '') {					// We are updating the password from the CSV.
				$salt = UserUtils::get_salt();
        $encpw_password = encpw($salt, $username, $password);

        $result = $mysqli->prepare("UPDATE users SET yearofstudy = ?, title = ?, first_names = ?, surname = ?, grade = ?, password = ?, roles = ? WHERE username = ?");
        $result->bind_param('ssssssss', $year, $title, $forname, $surname, $course, $encpw_password, $role, $username);
        $result->execute();
        $result->close();
      } else {
        // No new password was provided in the CSV.
        $result = $mysqli->prepare("UPDATE users SET yearofstudy = ?, title = ?, first_names = ?, surname = ?, grade = ?, roles = ? WHERE username = ?");
        $result->bind_param('sssssss', $year, $title, $forname, $surname, $course, $role, $username);
        $result->execute();
        $result->close();
      }

      $users['updated'][] = $username;
      $users['courses'][$course] = $course;
    } else {
      $invalid_user = true;
    }

    if (isset($header['modules'])) {
			$module = trim($fields[$header['modules']]);
    } else {
		  $module = '';
		}
		if (isset($header['session'])) {
			$session = trim($fields[$header['session']]);
		} else {
			$session = '';
		}
    $idMod = module_utils::get_idMod($module, $mysqli);
    if ($idMod !== false) {
      if ($role != 'Staff' and $role != 'External Examiner' and $role != 'Internal Reviewer' and $invalid_user != true and isset($header['modules']) and isset($header['session'])) {
        //add students to modules
        UserUtils::add_student_to_module($tmp_userID, $idMod, 1, $session, $mysqli);
      }
      if ($role == 'Staff') {
        UserUtils::add_staff_to_module($tmp_userID, $idMod, $mysqli);
      }
    }
  }
  $users['html'] .= "</table>\n";

  return $users;
}

function send_welcome_email($title, $surname, $username, $password, $user_email) {
global $string;
  $subject = "New Rogo account";
  $headers = "From: $support_email\n";
  $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
  $message = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">
<html>
<head>
<title>" . $string['emailmsg1'] . "</title>
<style>
body, td, p, div {font-family:Arial,sans-serif; background-color:white; color:#003366; font-size:10pt}
</style>
</head>
<body>
<p>" . $string['emailmsg2'] . " $title $surname,</p>
<p>" . $string['emailmsg3'] . "</p>
<p><strong>" . $string['emailmsg4'] . "</strong></p>
<ul>
<li>" . $string['emailmsg5'] . "</li>
<li>" . $string['emailmsg6'] . "</li>
</ul>\n
</body>\n</html>";

 mail($user_email, $subject, $message, $headers) or print "<p>" . $string['emailmsg7'] . " <strong>$user_email</strong>.</p>";
}
?>