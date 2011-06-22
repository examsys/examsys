<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Authentication functions.
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/


/**
 * This is function ad_auth authenticat a user agenat LDAP or AD
 *
 * @param string $u username
 * @param string $p password
 8 @param string $data used to pass userdata back from the LDAP for updates
 * @return Bool TRUE on sucsess FALSE on failure
 *
 */
function ldap_auth($u, $p, &$data){
  global $cfg_ldap_server, $cfg_ldap_search_dn, $cfg_ldap_bind_rdn, $cfg_ldap_bind_password;
  if ($u == '') {
    $data['error'] = 'No LDAP username or password supplied';
    return false;
  }
  if ($p == '') {
    $data['error'] = 'No LDAP username or password supplied';
    return false;
  }

  $ldap = ldap_connect($cfg_ldap_server);
  ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
  if (ldap_bind($ldap, $cfg_ldap_bind_rdn, $cfg_ldap_bind_password)) {
    if (!($search=@ldap_search($ldap, $cfg_ldap_search_dn, 'sAMAccountName=' . $u))) {
      $data['error'] = 'LDAP Server Unavalable: unable to search';
      return false;
    } else {
      $info = ldap_get_entries($ldap, $search);
      if ($info['count'] == 1) {
        $dn = $info[0]['dn'];
        if(isset($info[0]['uonprimaryemailalias'])) $data['email'] =  $info[0]['uonprimaryemailalias'][0];
        if(isset($info[0]['edupersonentitlement'])) $data['roles'] = $info[0]['edupersonentitlement'][0];
        if(isset( $info[0]['title'])) $data['title'] = $info[0]['title'][0];
        if(isset($info[0]['givenname'])) $data['first_name'] = $info[0]['givenname'][0];
        if(isset($info[0]['sn'])) $data['last_name'] = $info[0]['sn'][0];
      } else {
        $data['error'] = '<strong>No LDAP account found!</strong>';
        return false;
      }
    }

    if (@ldap_bind( $ldap, $dn, $p)) {
      ldap_unbind( $ldap );
      return true;
    } else {
      $data['error'] = 'Incorrect password provided';
      return false;	
    }
  } else {
    echo "Cannot connect to LDAP server";
    exit;
    $data['error'] = 'LDAP Server Unavalable';
    return false;	
  }
}

/**
 * This is function db_auth looks up a user in the local database and populates: 
 * 
 *  $userID, $userroles, $faculty, $title, $surname, $username, $grade, $year
 * 
 *
 * @param string $u username
 * @param string $p password
 * @param mixed $db msqli object
 * @return Bool TRUE on sucsess FALSE on failure
 *
 */
function db_auth($u, $p, $db) {
  global $userID, $userroles, $faculty, $title, $initials, $surname, $username, $grade, $year, $special_needs, $db_errors, $cfg_install_type;
  if ($u == '') {
    return false;
  }
  if ($p == '') {
    return false;
  }
  
  // Check for '_staff' or '_username' in the username and set user to _username if found and user is SysAdmin.
  $split_username = explode('_', $u);
  $tmp_u = $split_username[0];
  
  // Check for additional password on the end.
  //$split_password = explode('_', $p);
  //$tmp_p = $split_password[0];

  $stmt = $db->prepare("SELECT password, id, roles, faculty, title, initials, surname, username, grade, yearofstudy, special_needs FROM users WHERE username=?");
  $stmt->bind_param('s', $tmp_u);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($password,$userID, $userroles, $faculty, $title, $initials, $surname, $username, $grade, $year, $special_needs);
  $stmt->fetch();
  if ($stmt->num_rows > 0) {
    if ($p != '' AND crypt($p,$password) == $password) {
      $stmt->close();
      if (strpos($userroles,'SysAdmin') !== false and count($split_username) > 1) {
        //for Sysadmin only 
        if ($split_username[1] == 'staff') {
          //set the role to staff
          $userroles = 'Staff';
        } elseif ($split_username[1] == 'demo') {
          //set the role to demmo: this will anonomise any user data for public demos
          $userroles .= 'Demo';
        } else {
          // Look up all the authentication details of the user you are pretending to log in as.
          $result = $db->prepare("SELECT password, id, roles, faculty, title, surname, username, grade, yearofstudy FROM users WHERE username=?");
          $result->bind_param('s', $split_username[1]);
          $result->execute();
          $result->bind_result($password, $userID, $userroles, $faculty, $title, $surname, $username, $grade, $year);
          $result->fetch();
          $result->close();
          $cfg_install_type .= " as $title $surname";
        }
      }
      return true;
    } else {
      $db_errors = '<strong>Local TouchStone authentication failed.</strong>';
      return false;
    }
  } else {
    $password = $userID = $userroles = $faculty = $title = $surname = $username = $grade = $year = '';
    if ($stmt->num_rows == 0) {
      $db_errors = '<strong>No TouchStone account found!</strong>';
    }
    $stmt->close();
    return false;
  } 
}

/**
 * This is function encpw encrpts a password using md5 and a different salt per user. For 
 * storage in the DB
 *
 * @param string $u username
 * @param string $p password
 * @return string encrypted password
 *
 */
function encpw($u,$p) {
  $salt = '$1$' . substr(md5($u),0,12) . '$';
  return crypt($p,$salt);
}

/**
 * This is function gen_password makes a secure password
 *
 * @param string $u username
 * @param string $p password
 * @return string password length 8 including uper lower case and other chars
 *
 */
function gen_password() {
  $lower = 'abcdefghijklmnoprrstuvwxyzabcdefghijklmnoprrstuvwxyz';
  $upper = 'ABCDEFGHIJKLMN0PQRSTUVWXYZabcdefghijklmnoprrstuvwxyz';
  $num =   '0123456789012345678901234501234567890123456789012345';
  $special ='!$%?#@!.%?#@!$%?#@!$%?#@!.!$%?#@!.%?#@!$%?#@!.%?#@!$'; 
  $pass = '';
  $chars = array($lower,$lower,$lower,$special,$num,$num,$upper,$upper);
  for($i = 0; $i < 8; $i++) { 
    $pass .= substr($chars[$i],rand(0,51),1);
  }
  return $pass;
}

?>