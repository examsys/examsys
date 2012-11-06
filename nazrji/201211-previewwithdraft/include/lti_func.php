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
 * function used by the add question scripts
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

/**
 * Function to support LTI capability
 * @param object $db database object
 * @param object $lti lti object
 * @return false if issue else array of userid and last updated data
 */

function usercheck($db, $lti) {
  global $string, $userID, $userroles, $faculty, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $db_errors, $cfg_root_path, $cfg_install_type, $cfg_db_database, $cfg_use_ldap, $fp_link, $cfg_encrypt_salt;
  
  $lti_i = lti_integration::load();
  if (!isset($_SESSION['lti']['track'])) $_SESSION['lti']['track'] = '';
  
  if ($_SESSION['lti']['track'] == 'reauth2' and isset($_SERVER['PHP_AUTH_USER'])) {
    $returned2 = db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $db, false);
    if ($returned2 > 0) {
      $lti->update_lti_user();
      $_SESSION['lti']['track'] = 'reauth3';
    } else {
      $_SESSION['lti']['track'] = 'reauth';
    }
  } elseif ($_SESSION['lti']['track'] == 'reauth') {
    Header("WWW-authenticate: basic realm=\"Rogo\"");
    Header("HTTP/1.0 401 Unauthorised");
    $message = $string['authenticationfailed'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['usernamecasesensitive'] . "</li>\n";
    if ($cfg_use_ldap == true) $message .= "<li>" . $string['tsonldap'] . "</li>\n";
    $message .= '<li>' . $string['pressf5'] . '</li>';
    $message .= "</ul>";
    if ($cfg_use_ldap != true) $message .= $fp_link;
    $_SESSION['lti']['track'] = 'reauth2';
    access_denied($message, true);

    exit();
  }
  $returned = $lti->lookup_lti_user();
  if ($returned === false) {
    if (!isset($_SERVER['PHP_AUTH_USER']) and $_SESSION['lti']['track']=='') {
      display_notice($string['ltifirstlogin'], $string['ltifirstlogindesc'], '/artwork/user_info_48.png', $title_color = '#C00000');
      $_SESSION['lti']['track'] = 'logon';
      $db->close();
      exit;
    }
    if (isset($_SERVER['PHP_AUTH_USER'])) {
      $returned2 = db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $db, false);
      if ($cfg_use_ldap == true and $returned2 <= 0 and ($db_errors != '<strong>' . $string['notsaccount'] . '</strong>' or $returned2 == -2)) {
        $ldap_user_data = array();
        if (ldap_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $ldap_user_data) == true) {
          //Ad Account OK
          $encpw_details = encpw($cfg_encrypt_salt, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
          $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
          $stmt->bind_param('ss', $encpw_details, $_SERVER['PHP_AUTH_USER']);
          $stmt->execute();
        }
      }
      if ($returned2 == -1 ) {
        // create user
        $lti_i::user_add($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        $returned2 = db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $db, false);


      }
      if ($returned2 > 0) {
        //insert ID into table
        $returned3 = $lti->add_lti_user($userID);
        $returned4 = $lti->lookup_lti_user();
        $ret = db_change_user($db);
        return $returned4;
      }
      return $returned2;
    }
    if ($_SESSION['lti']['track'] == 'logon') {
      Header("WWW-authenticate: basic realm=\"Rogo\"");
      Header("HTTP/1.0 401 Unauthorised");
      $message = $string['authenticationfailed'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['usernamecasesensitive'] . "</li>\n";
      if ($cfg_use_ldap == true) $message .= "<li>" . $string['tsonldap'] . "</li>\n";
      $message .= '<li>' . $string['pressf5'] . '</li>';
      $message .= "</ul>";
      if ($cfg_use_ldap != true) $message .= $fp_link;
      access_denied($message, true);
      $_SESSION['lti']['track'] == 'logon1';
      exit();
    }

  } else {
    $authneeded = $lti_i->user_time_check($returned[1], $username);
    if ($authneeded === true) {
      display_notice($string['ltifirstlogin'], $string['ltifirstlogindesc'], '/artwork/user_info_48.png', $title_color = '#C00000');

      $_SESSION['lti']['track'] = 'reauth';
      //TODO as all the rest of the reauth needs finishing
      $db->close();
      exit();
    }
    return ($returned);

  }
}
