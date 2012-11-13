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
 * Authentication functions.
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */


function display_notice($title, $msg, $icon, $title_color = 'black') {
  global $cfg_root_path, $cfg_page_charset;

  echo "<html>\n<head>\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n<meta http-equiv=\"content-type\" content=\"text/html;charset=$cfg_page_charset\" />\n<title>$title</title>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$cfg_root_path}/css/body.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$cfg_root_path}/css/notice.css\" />\n</head>\n<body>\n";
  echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"" . $cfg_root_path . $icon . "\" width=\"48\" height=\"48\" /></div>\n";
  echo "<h1 style=\"color:$title_color\">$title</h1>\n";
  echo "<hr />\n<p>$msg</p>";
}

/**
 * This function will output an access denied warning and terminate script execution
 *
 * @param string $message string message to display
 * @param string $output_header if true output 401 headers
 *
 */
function access_denied($message, $output_header = false, $output_footer = true) {
  global $mysqli, $string, $cfg_root_path;

  if ($output_header == true) {
    Header("WWW-authenticate: basic realm=\"Rogo\"");
    Header("HTTP/1.0 401 Unauthorised");
  }
  display_notice($string['accessdenied'], $message, '/artwork/access_denied.png', '#C00000');

  if ($output_footer) {
    echo "\n</body>\n</html>";

    $mysqli->close();
    exit;
  }
}


/**
 * This is function ad_auth authenticat a user agenat LDAP or AD
 *
 * @param string $u username
 * @param string $p password
 * @param string $data used to pass userdata back from the LDAP for updates
 * @return Bool TRUE on sucsess FALSE on failure
 *
 */
require_once 'ldap.php';


/**
 * This is function db_userlookup looks up a user in the local database and populates:
 *
 *  $userID, $userroles, $faculty, $title, $surname, $username, $grade, $year
 *
 * @param string $u username
 * @param mixed $db msqli object
 * @return array
 *
 */

function db_userlookup($u, $db, $useridlookup = false) {
  //global $REPLACEMEuserIDold, $userroles, $faculty, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $db_errors, $cfg_install_type, $cfg_db_database, $string;
  global $cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd;
  global $cfg_db_admin_user, $cfg_db_admin_passwd;
  global $cfg_db_staff_user, $cfg_db_staff_passwd;
  global $cfg_db_student_user, $cfg_db_student_passwd;
  global $cfg_db_external_user, $cfg_db_external_passwd;
  global $cfg_db_inv_user, $cfg_db_inv_passwd;

  global $db_errors, $cfg_install_type, $cfg_db_database, $string;

  if ($u == '') {
    return false;
  }

  // Check for '_staff' or '_username' in the username and set user to _username if found and user is SysAdmin.
  $split_username = explode('_', $u);
  $tmp_u = $split_username[0];

  if ($useridlookup == false) {
    $stmt = $db->prepare("SELECT password, id, roles, title, initials, surname, username, email, grade, yearofstudy, special_needs FROM users WHERE user_deleted IS NULL AND username=?");
  } else {
    $stmt = $db->prepare("SELECT password, id, roles, title, initials, surname, username, email, grade, yearofstudy, special_needs FROM users WHERE user_deleted IS NULL AND id=?");
  }

  $stmt->bind_param('s', $tmp_u);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($password, $userID, $userroles, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs);
  $stmt->fetch();
  $record_no = $stmt->num_rows();
  $stmt->close();

  if($record_no == 0) {
    return false;
  }

  return array($password, $userID, $userroles, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $record_no, $split_username);
}

function db_change_user($db,$userObject) {


//  global $REPLACEMEuserIDold, $userroles, $faculty, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $db_errors, $cfg_install_type, $cfg_db_database, $string;
  global $cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd;
  global $cfg_db_admin_user, $cfg_db_admin_passwd;
  global $cfg_db_staff_user, $cfg_db_staff_passwd;
  global $cfg_db_student_user, $cfg_db_student_passwd;
  global $cfg_db_external_user, $cfg_db_external_passwd;
  global $cfg_db_inv_user, $cfg_db_inv_passwd;

  global $db_errors, $cfg_install_type, $cfg_db_database, $string;

  $userroles = $userObject->old_getuserroles();
  //select the aproprate database user
  if ($userObject->has_role('SysAdmin')) {
    $result = $db->change_user($cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd, $cfg_db_database);
  } else if ($userObject->has_role(array('Staff','Admin'))) { // Process staff first to get higher priority than students
    $result = $db->change_user($cfg_db_staff_user, $cfg_db_staff_passwd, $cfg_db_database);
  } else if ($userObject->has_role('Student')) {
    $result = $db->change_user($cfg_db_student_user, $cfg_db_student_passwd, $cfg_db_database);
  } else if ($userObject->has_role('External Examiner')) {
    $result = $db->change_user($cfg_db_external_user, $cfg_db_external_passwd, $cfg_db_database);
  } else if ($userObject->has_role('Invigilator')) {
    $result = $db->change_user($cfg_db_inv_user, $cfg_db_inv_passwd, $cfg_db_database);
  } else {
    $result = false;
  }

  if ($result === false) {
    $db_errors = '<strong>' . $string['nodatabaseconnection'] . '</strong>';
    return false;
  }
  return true;
}

function db_auth($u, $p, $db, $changeuser = true, $userObject) {
  // return 0 if password or user missing, -1 if no user in table, -2 if password doesnt match, -3 if cant change user


  //global $REPLACEMEuserIDold, $userroles, $faculty, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $db_errors, $cfg_install_type, $cfg_db_database, $string, $cfg_encrypt_salt;
  global $cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd;
  global $cfg_db_admin_user, $cfg_db_admin_passwd;
  global $cfg_db_staff_user, $cfg_db_staff_passwd;
  global $cfg_db_student_user, $cfg_db_student_passwd;
  global $cfg_db_external_user, $cfg_db_external_passwd;
  global $cfg_db_inv_user, $cfg_db_inv_passwd;

  global $db_errors, $cfg_install_type, $cfg_db_database, $string,$cfg_encrypt_salt,$cfg_db_database;


  if ($u == '') {
    return 0;
  }
  if ($p == '') {
    return 0;
  }

  $record_no = 0;

  //look up user based on username and set globals
  $array = db_userlookup($u, $db);
  list($password, $userID, $userroles, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $record_no, $split_username) = $array;

  $userObject->old_load($array);

  if ($record_no > 0) {

    if (substr($password, 0, 3) == '$1$') {
      $encrypt_type = 'MD5';
    } else {
      $encrypt_type = 'SHA-512';
    }

    $encrypt_password = encpw($cfg_encrypt_salt, $u, $p, $encrypt_type);

    if ($encrypt_password == $password) {
      if ($encrypt_type == 'MD5') { // Re-encrypt MD5 passwords using SHA-512.
        $encpw_details = encpw($cfg_encrypt_salt, $u, $p);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->bind_param('ss', $encpw_details, $u);
        $stmt->execute();
        $stmt->close();
      }

      if ($userObject->has_role('Staff') and count($split_username) > 1) {
        //for Sysadmin only 
        if ($split_username[1] == 'staff') {
          //set the role to staff
          $userroles = 'Staff';
        } elseif ($split_username[1] == 'demo') {
          //set the role to demo: this will anonomise any user data for public demos
          $userroles .= ',Demo';
        } elseif ($userObject->has_role('SysAdmin')) {
          // Look up all the authentication details of the user you are pretending to log in as.
          list($password, $userID, $userroles, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $record_no, $split_username) = db_userlookup($split_username[1], $db);
          $cfg_install_type .= " " . $string['as'] . " $title $surname";
        }
      }

      if ($changeuser) {
        $ret2 = db_change_user($db,$userObject);
        if ($ret2 === false) {
          $ret = -3;
        } else {
          $ret = 1;
        }
      } else {
        $ret = 1;
      }

      return $ret;
    } else {
      $db_errors = '<strong>' . $string['localtsauthfailed'] . '</strong>';
      return -2;
    }
  } else {
    $password = $userID = $userroles = $title = $surname = $username = $email = $grade = $year = '';
    if ($record_no == 0) {
      $db_errors = '<strong>' . $string['notsaccount'] . '</strong>';
    }
    return -1;
  }
}

/**
 * This is function encpw encrpts a password using SHA-512 for storage in the DB.
 * MD5 encryption is kept for backwards compatibility.
 *
 * @param string $salt the salt as set in the config.inc.php file
 * @param string $u username
 * @param string $p password
 * @param string $type the level of encryption to use
 * @return string encrypted password
 *
 */
function encpw($salt, $u, $p, $type = 'SHA-512') {
  $supportsha = false;
  if (version_compare(PHP_VERSION, '5.3.2') >= 0) {
    $supportsha = true;
  }
  if ($type == 'SHA-512' and $supportsha == true) {
    $full_salt = '$6$' . $salt . '$'; // SHA-512
    $new_password = crypt($p, $full_salt);
    $new_password = '$6$' . substr($new_password, strlen($full_salt));
  } else {
    $full_salt = '$1$' . substr(md5($u), 0, 8) . '$'; // Simple MD5, for barckwards compatibility
    $new_password = crypt($p, $full_salt);
  }
  
  return $new_password;
}

/**
 * This is function gen_password makes a secure password
 *
 * @param int $len Length og generated password
 * @return string password length $len including upper lower case and other chars
 *
 */
function gen_password($len = 8) {
  $lower = 'abcdefghijklmnoprrstuvwxyzabcdefghijklmnoprrstuvwxyz';
  $upper = 'ABCDEFGHIJKLMN0PQRSTUVWXYZABCDEFGHIJKLMN0PQRSTUVWXYZ';
  $num = '0123456789012345678901234501234567890123456789012345';
  $special = '!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-';

  $pass = '';
  $chars = array($lower, $lower, $lower, $special, $num, $num, $upper, $upper);
  for ($i = 0; $i < $len; $i++) {
    if ($i < 7) {
      $pass .= substr($chars[$i], rand(0, 51), 1);
    } else {
      $pass .= substr($chars[rand(2, 6)], rand(0, 51), 1);
    }
  }
  return $pass;
}

//TODO REMOVE ME
/*function getUserTeams($userID, $db) {
  $teams = array();

  $result = $db->prepare("SELECT name FROM teams WHERE memberID=? AND name IS NOT NULL ORDER BY name");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($team_name);
  while ($result->fetch()) {
    $team_name = strtoupper($team_name);
    $teams[$team_name] = $team_name;
  }
  $result->close();

  return $teams;
}*/

/***
*
*
*
*/
function get_staff_modules($userID, $db,$userObject) {
  error_disp("GET_STAFF_MODULE called");
  $staff_modules = array();

  $result = $db->prepare("SELECT idMod,moduleID FROM modules_staff,modules WHERE modules_staff.idMod = modules.id AND memberID=? AND modules.moduleID IS NOT NULL ORDER BY modules.moduleID");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($idMod,$moduleID);
  while ($result->fetch()) {
    $staff_modules[$idMod] = $moduleID;
  }
  $result->close();

  return $staff_modules;
}


?>