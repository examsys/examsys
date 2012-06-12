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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

//TODO this function has been moved to userutils.class.inc
function my_ucwords($s) { 
  $s = preg_replace_callback("/(\b[\w|']+\b)/s", fixcase_callback, $s); 
  return $s;         
} 

//TODO this function has been moved to userutils.class.inc    
function fixcase_callback($word) { 
  $word = $word[1]; 
  $word = strtolower($word); 
        
  if ($word == "de") return $word; 
   
  $word = ucfirst($word); 
       
  if (substr($word,1,1) == "'") { 
    if (substr($word,0,1) == "D") { 
      $word = strtolower($word); 
    } 
    $next = substr($word,2,1); 
    $next = strtoupper($next); 
    $word = substr_replace($word, $next, 2, 1); 
  }
  return $word; 
} 

$header = array();
$needed_cols = array('student_id', 'full name', 'first names', 'surname', 'title', 'course code', 'year of course', 'local email');

function add_users_from_file($fileName) {
  global $header, $needed_cols, $mysqli, $cfg_encrypt_salt;
  
  $courses = array();
  $users[] = array();
  $faculties = array();
  
  // Load all the school to faculty lookup into an array.
  $stmt = $mysqli->prepare("SELECT faculty, school FROM schools");
  $stmt->execute();
  $stmt->bind_result($tmp_faculty, $tmp_school);
  while ($stmt->fetch()) {
    $faculties[$tmp_school] = $tmp_faculty;
  }
  $stmt->close();

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
      $separate_line = strtolower($separate_line);
      $error = array();
      $tmp_header = explode($split_on,$separate_line);
      foreach ($tmp_header as $tmp_string) {
        $tmp_string = str_replace('"','',$tmp_string);
        $header[] = $tmp_string;
      }
      $header = array_flip($header);
      foreach ($needed_cols as $col) {
        $col = str_replace('"','',$col);
        if (!isset($header[$col])) {
          $error['error'][] = "<li>Missing '$col' Colum from import please add it.</li>";
        }
      }
      if (count($error) > 0) {
        return $error;
      } else {
        continue;
      }
    }

    $fields = explode($split_on,$separate_line);
                     
    $sid = str_replace('"','',trim($fields[$header['student_id']]));
    $forname = my_ucwords(trim($fields[$header['first names']])); 
    $initial = explode(' ',$forname);
    $initials = '';
    foreach ($initial as $name) {
      $initials .= substr($name,0,1);
    }
    $initials = strtoupper($initials);
    $surname = my_ucwords(trim($fields[$header['surname']]));
    $title = my_ucwords(trim($fields[$header['title']]));
    $course = trim($fields[$header['course code']]); 
    $year = trim($fields[$header['year of course']]);
    $email = trim($fields[$header['local email']]);
    if (isset($header['username'])) {
      $username = $fields[$header['username']];
    } else {
      $username = explode("@",$email);
      $username = $username[0];
    }
                        
    $school = trim($fields[11]);
    
    if (isset($faculties[$school])) {
      $faculty = $faculties[$school];
    } elseif (isset($faculties['School of ' . $school])) {
      $faculty = $faculties['School of ' . $school];
    }
			
    if ($title == 'Mr') {
      $gender = 'Male'; 
    } elseif ($title == 'Miss' or $title == 'Mrs' or $title == 'Ms') {
      $gender = 'Female';
    } else {
      $gender = 'NULL';
    }
    
    //TODO this function has been moved to userutils.class.inc  
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userID);
    $stmt->fetch();
    $invalid_user = false;
    if ($stmt->num_rows == 0 and $username != '' and stristr('ps_',$username) === false) {
      //add new users
      //TODO this function has been moved to passwordutils.class.inc  
      $password =  gen_password();
      
      $encrypt_password = encpw($cfg_encrypt_salt, $username, $password);
      $result = $mysqli->prepare("INSERT INTO users VALUES(?, ?, ?, ?, ?, ?, ?, ?, 'Student', NULL, ?, ?, ?, NULL, 0)");
      $result->bind_param('ssssssssssi', $encrypt_password, $course, $surname, $initials, $title, $username, $email, $faculty, $forname, $gender, $year);
      $result->execute();
      $result->close();
        
      $userID = $mysqli->insert_id;

      $result = $mysqli->prepare("INSERT INTO sid VALUES(?,?)");
      $result->bind_param('si', $sid, $userID);
      $result->execute();
      $result->close();

      $users['added'][] = $username;
      $users['courses'][$course] = $course;
      $users['year_string'][$year_string] = $year_string;  
      
      //send Welcome Email if ticked
      if ($_POST['welcome'] != '') {
        send_welcome_email($title, $surname, $username, $password, $email);
      }
    } elseif ($username != '' and stristr('ps_',$username) === false) {
      //update existing student records
      $result = $mysqli->prepare("UPDATE users SET yearofstudy=?, title=?, first_names=?, surname=?, grade=? WHERE username=?");
      $result->bind_param('ssssss', $year, $title, $forname, $surname, $course, $username);
      $result->execute();
      $result->close();

      $users['updated'][] = $username;
      $users['courses'][$course] = $course;
      $users['year_string'][$year_string] = $year_string;
    } else {
      $invalid_user = true;
    }
	  
    if ($invalid_user != true and isset($header['modules']) and isset($header['session'])) {
      //add students to modules
      $module = trim($fields[$header['modules']]);
      $session = trim($fields[$header['session']]);
      echo "Insert " . trim($fields[$header['modules']]) . " $userID - " . trim($fields[$header['session']]) . "<br />\n";
      $result = $mysqli->prepare("INSERT INTO student_modules VALUES(NULL, ?, ?, ?, 1)");
      $result->bind_param('iss', $userID, $module, $session);
      $result->execute();
      $result->close();
    }
	  
    $stmt->free_result();
    $stmt->close();
  }

  return $users;
}

function send_welcome_email($title, $surname, $username, $password, $user_email) {
  $subject = "New Rogo account";
  $headers = "From: $support_email\n";
  $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
  $message = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">
<html>
<head>
<title>Create new user account</title>
<style>
body, td, p, div {font-family:Arial,sans-serif; background-color:white; color:#003366; font-size:10pt}
</style>
</head>
<body>
<p>Dear $title $surname,</p>
<p>A new account has been created to access the online assessment and survey system Rogo. Your personal authentication details are the same as your university log in details.</p>
<p><strong>Note:</strong></p>
<ul>
<li>Never share your university username/password with anyone.</li>
<li>Cheating in summative examinations is an academic offence and will not be tolerated.</li>
</ul>\n
</body>\n</html>";

 mail($user_email, $subject, $message, $headers) or print "<p>Could not send mail to <strong>$user_email</strong>.</p>";
}
?>