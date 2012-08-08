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
 * Functions to support LTI capability
 *
 */


function usercheck($db, $lti) {
  if(!isset($_SESSION['lti']['track'])) $_SESSION['lti']['track']='';
  global $string, $userID, $userroles, $faculty, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $db_errors, $cfg_root_path, $cfg_install_type, $cfg_db_database, $cfg_use_ldap, $fp_link;
  // $info = $lti->getUserKey(1);
//  if( $_SESSION['lti']['track'] == 'reauth') {
//    display_notice($string['ltifirstlogin'], $string['ltifirstlogindesc'], '/artwork/access_denied.png', $title_color = '#C00000');
//    $_SESSION['lti']['track'] = 'reauth1';
//    $db->close();
//    exit;
//  }
  if( $_SESSION['lti']['track'] == 'reauth2' ) {
    $returned2 = db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $db, false);
    if($returned2>0) {
      $lti->update_lti_user();
      $_SESSION['lti']['track'] = 'reauth3';
    }
    else {
      $_SESSION['lti']['track'] = 'reauth';
    }


  }
  elseif ($_SESSION['lti']['track'] == 'reauth') {
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
    if (!isset($_SERVER['PHP_AUTH_USER']) AND !isset($_SESSION['lti']['track'])) {
      display_notice($string['ltifirstlogin'], $string['ltifirstlogindesc'], '/artwork/access_denied.png', $title_color = '#C00000');
      $_SESSION['lti']['track'] = 'logon';
      $db->close();
      exit;
    }
    if (isset($_SERVER['PHP_AUTH_USER'])) {
      $returned2 = db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $db, false);
     if($returned2 == -1 ) {
       // create user
       i_lti_user_add($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
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
    $authneeded=i_lti_user_time_check($returned[1]);
    if($authneeded===true) {
      display_notice($string['ltiadditionallogin'], $string['ltiadditionallogindesc'], '/artwork/access_denied.png', $title_color = '#C00000');
       display_notice($string['ltifirstlogin'], $string['ltifirstlogindesc'], '/artwork/access_denied.png', $title_color = '#C00000');
      //    $db->close();
      //    exit;
      $_SESSION['lti']['track']='reauth';
      //TODO as all the rest of the reauth needs finishing
      $db->close();
      exit();
    }
    return ($returned);

  }
}


function lookupltiuser($lti) {
  global $mysqli;
  $ret = $lti->lookup_lti_user();
  if ($ret === false) {
    return false;
  }
  $stmt = $mysqli->prepare("SELECT username FROM users WHERE users.id=?");
  $stmt->bind_param('i', $ret[0]);
  $stmt->execute();
  $stmt->store_result();
  $rows = $stmt->num_rows;
  if ($rows < 1) {
    return false;
  }
  $stmt->bind_result($username);
  $stmt->fetch();
  return (array($ret[0], $ret[1], $username));
}

/*
function lookupltiuser($db, $oauth_consumer_key, $user_id) {
  $stmt = $db->prepare("SELECT rogo_id, updated_on, username FROM lti_user,users WHERE lti_user.rogo_id=users.id AND oauth_consumer_key=? AND user_id=?");
  $stmt->bind_param('ss', $oauth_consumer_key, $user_id);
  $stmt->execute();
  $stmt->store_result();
  $rows = $stmt->num_rows;
  if ($rows < 1) {
    return false;
  }
  $stmt->bind_result($rogo_id, $updated, $username);
  $stmt->fetch();
  return (array($rogo_id, $updated, $username));
}

function addltiuser($db, $oauth_consumer_key, $user_id, $userID) {
  $result = $db->prepare("INSERT INTO lti_user (oauth_consumer_key, user_id, rogo_id,updated_on) VALUES (?,?,?,NOW()) ");
  $result->bind_param('sss', $oauth_consumer_key, $user_id, $userID);
  $result->execute();
  $ret = $db->insert_id;
  $result->close();
  return $ret;
}

function lookupltiresource($db, $oauth_consumer_key, $resource_id) {
  $stmt = $db->prepare("SELECT internal_id,itype FROM lti_resource WHERE  oauth_consumer_key=? AND lti_resource_id=?");
  $stmt->bind_param('ss', $oauth_consumer_key, $resource_id);
  $stmt->execute();
  $stmt->store_result();
  $rows = $stmt->num_rows;
  if ($rows < 1) {
    return false;
  }
  $stmt->bind_result($paperret, $otherret);
  $stmt->fetch();
  return (array($paperret, $otherret));
}


function addltiresource($db, $oauth_consumer_key, $lti_resource_id, $internal_id, $itype) {
  $result = $db->prepare("INSERT INTO lti_resource (oauth_consumer_key, lti_resource_id, internal_id, itype) VALUES (?, ?, ?, ?) ");
  $result->bind_param('ssss', $oauth_consumer_key, $lti_resource_id, $internal_id, $itype);
  $result->execute();
  $ret = $db->insert_id;
  $result->close();
  return $ret;
}

function addlticontext($db, $oauth_consumer_key, $lti_context_id, $c_internal_id) {
  $result = $db->prepare("SELECT c_internal_id FROM lti_context WHERE oauth_consumer_key = ? AND lti_context_id =?");
  $result->bind_param('ss', $oauth_consumer_key, $lti_context_id);
  $result->execute();
  $result->store_result();
  $rows = $result->num_rows();
  $result->close();
  $ret = false;
  if ($rows == 0) {
    $result = $db->prepare("INSERT INTO lti_context (oauth_consumer_key, lti_context_id, c_internal_id, updated_on) VALUES (?, ?, ?, NOW()) ");
    $result->bind_param('sss', $oauth_consumer_key, $lti_context_id, $c_internal_id);
    $result->execute();
    $ret = $db->insert_id;
    $result->close();
  }
  return $ret;
}
*/