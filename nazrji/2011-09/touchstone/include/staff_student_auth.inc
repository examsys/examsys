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
* Authentication routine which permits staff and student access to a page.
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root . 'touchstone/config/config.inc.php';
require_once $cfg_web_root . 'touchstone/include/auth.inc';
require_once $cfg_web_root . 'touchstone/include/custom_error_handler.inc';
require_once $cfg_web_root . 'touchstone/classes/lang.class.php';
require_once $cfg_web_root . 'touchstone/lang/' . $language . '/touchstone/include/common.inc';   // Include common language file that all scripts need

$fp_link = "<p style=\"margin-left:60px\"><a href=\"/touchstone/users/forgotten_password.php\">" . $string['forgottenpassword'] . "</p>\n";

if ($_SERVER['SERVER_PORT'] != 443 and $_SERVER['SERVER_PORT'] != 8080) {
  echo "<html>\n<body style=\"font-family:Arial,sans-serif;font-size:100%;color:#C00000\">\n<p><strong>" . $string['warning'] . "</strong> " . $string['secureconnection'] . " <a href=\"https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\">https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</a>.\n</body>\n</html>\n";
  exit;
}
if (preg_match( '|MSIE ([5-9].[0-9]{1,2})|',$_SERVER['HTTP_USER_AGENT'],$log_version) or preg_match( '|Netscape6|',$_SERVER['HTTP_USER_AGENT'],$log_version) or preg_match( '|Netscape\/7.0|',$_SERVER['HTTP_USER_AGENT'],$log_version) or preg_match('|Mozilla\/5.0|',$_SERVER['HTTP_USER_AGENT'],$log_version) or preg_match('|PHP-SOAP|',$_SERVER['HTTP_USER_AGENT'],$log_version)) {
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
  if (mysqli_connect_error()) {
    echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/orange_alert_48.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Database Connection Error</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<ul style=\"margin-left:80px\">\n<li>Error message: " . mysqli_connect_error() . ".</li>\n<li>Error number: " . mysqli_connect_errno() . "</li>";
    exit;
  }

  if (!isset($_SERVER['PHP_AUTH_USER'])) {
    $message = $string['authenticationfailed'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['usernamecasesensitive'] . "</li>\n";
    if ($cfg_use_ldap == true) $message .= "<li>" . $string['tsonldap'] . "</li>\n";
    $message .= '<li>' . $string['pressf5'] . '</li>';
     if ($_SERVER['PHP_SELF'] == '/index.php') {
      $message .= "<li>" . $string['ifstuckinvigilator'] . " <a href=\"guest_account.php\" style=\"color:blue\"><strong>" . $string['tempaccount'] . "</strong></a></li>";
    }
    $message .= "</ul>";
    if ($cfg_use_ldap != true) $message .= $fp_link;
    access_denied($message, true);
  } else {
    //
    //  MAIN AUTHENTICATION ROUTINES
    //
    if (db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'],$mysqli) == false) {
      //try LDAP
      if ($cfg_use_ldap == true and $db_errors != '<strong>' . $string['notsaccount'] . '</strong>') {
        $ldap_user_data = Array();
        if (ldap_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $ldap_user_data) == true) {
          //Ad Account OK
          $encpw_details = encpw($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
          $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE username = ?");
          $stmt->bind_param('ss', $encpw_details, $_SERVER['PHP_AUTH_USER']);
          $stmt->execute();
          if ($stmt->affected_rows == 0) {
            //user has no touchstone account but valid LDAP
            $stmt->close();
            $message = $string['notsaccount'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>Please contact <a href=\"mailto:$support_email\">$support_email</a> if you believe you should have access.</li>\n";
             if ($_SERVER['PHP_SELF'] == '/index.php') {
              $message .= $string['ifstuckinvigilator'] . " <a href=\"guest_account.php\" style=\"color:blue\"><strong>" . $string['tempaccount'] . "</strong></a></li>";
            }
            $message .= "</ul>";
            access_denied($message, true);
          }
          $stmt->close();
          db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $mysqli, $ldap_user_data);
          
        } else {
          //Ad Account NOT OK
          $message = $string['authenticationfailed'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['usernamecasesensitive'] . "</li>\n";
          if ($cfg_use_ldap == true) {
            $message .= "<li>" . $string['tsonldap'] . "</li>\n";
          }
          $message .= '<li>' . $string['pressf5'] . '</li>';
           if ($_SERVER['PHP_SELF'] == '/index.php') {
            $message .= "<li>" . $string['ifstuckinvigilator'] . " <a href=\"guest_account.php\" style=\"color:blue\"><strong>" . $string['tempaccount'] . "</strong></a></li>";
          }
          $message .= "<li>" . $ldap_user_data['error'] . "</li></ul>";
          if(strpos($userroles,'External Examiner') !== false) $message .= $fp_link;
          access_denied($message, true);
        }
      } else {
        // Internal DB authentication failed.
        if ($cfg_use_ldap == true and  ldap_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $ldap_user_data) == true and  strpos($_SERVER['SCRIPT_FILENAME'],'self_enroll.php') !== FALSE ) {
          //we have a valid LDAP account - we are in the self enrol script allow execution to continue
          //continue
        } else {
          $message = $string['authenticationfailed'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['usernamecasesensitive'] . "</li>\n<li>" . $string['pressf5'] . "</li>";
          if ($db_errors != '') {
            $message .= "<li>$db_errors</li>\n";
          }
          if ($_SERVER['PHP_SELF'] == '/index.php') {
            $message .= "<li>" . $string['ifstuckinvigilator'] . " <a href=\"guest_account.php\" style=\"color:blue\"><strong>" . $string['tempaccount'] . "</strong></a></li>\n</ul>";
          }
          $message .= $fp_link;
          access_denied($message, true);
        }
      }
    } 
  }
} else {
  echo "<html>\n<body style=\"font-family:Arial,sans-serif\">\n<p style=\"font-size:150%;color:#C00000\"><strong>WARNING:</strong> Incompatible Browser</p>\n<p><strong>TouchStone currently requires MS Internet Explorer 5 (or above) or Firefox 2 (or above).</strong></p>\n<p><strong>You are running </strong>" . $_SERVER['HTTP_USER_AGENT'] . "</p>\n</body>\n</html>\n";
  $mysqli->close();
  exit;
}
?>