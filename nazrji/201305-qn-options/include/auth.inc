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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

if (!isset($cfg_web_root)) {
  $cfg_web_root = str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));
}
require_once $cfg_web_root . 'classes/usernotices.class.php';

$notice = UserNotices::get_instance();

function db_change_user($db, $userObj) {
  global $db_errors, $string;
  
  $getback = array('cfg_db_sysadmin_user', 'cfg_db_sysadmin_passwd','cfg_db_admin_user', 'cfg_db_admin_passwd', 'cfg_db_staff_user', 'cfg_db_staff_passwd', 'cfg_db_student_user', 'cfg_db_student_passwd','cfg_db_external_user', 'cfg_db_external_passwd', 'cfg_db_inv_user', 'cfg_db_inv_passwd', 'cfg_db_database');

  $configObject = Config::get_instance();
  $arr = $configObject->get($getback);
  foreach ($arr as $k=>$v) {
    ${$k} = $v;
  }

  $userroles = $userObj->old_getuserroles();

  //select the aproprate database user
  if ($userObj->has_role('SysAdmin')) {
    $result = $db->change_user($cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd, $cfg_db_database);
  } else if ($userObj->has_role(array('Staff', 'Admin'))) { // Process staff first to get higher priority than students
    $result = $db->change_user($cfg_db_staff_user, $cfg_db_staff_passwd, $cfg_db_database);
  } else if ($userObj->has_role('Student')) {
    $result = $db->change_user($cfg_db_student_user, $cfg_db_student_passwd, $cfg_db_database);
  } else if ($userObj->has_role('External Examiner')) {
    $result = $db->change_user($cfg_db_external_user, $cfg_db_external_passwd, $cfg_db_database);
  } else if ($userObj->has_role('Invigilator')) {
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
  $lower    = 'abcdefghijklmnoprrstuvwxyzabcdefghijklmnoprrstuvwxyz';
  $upper    = 'ABCDEFGHIJKLMN0PQRSTUVWXYZABCDEFGHIJKLMN0PQRSTUVWXYZ';
  $num      = '0123456789012345678901234501234567890123456789012345';
  $special  = '!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-';

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
?>