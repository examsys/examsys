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

require_once $_SERVER['DOCUMENT_ROOT'] . 'touchstone/config/config.inc';
require_once $cfg_web_root . 'touchstone/include/auth.inc';
require_once $cfg_web_root . 'touchstone/include/custom_error_handler.inc';

if ($_SERVER['SERVER_PORT'] != 443 and $_SERVER['SERVER_PORT'] != 8080) {
  echo "<html>\n<body style=\"font-family:Arial,sans-serif;font-size:100%;color:#C00000\">\n<p><strong>WARNING:</strong> TouchStone can only be accessed through a secure web connection.</p>\n<p>Instead use: <a href=\"https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\">https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</a>.\n</body>\n</html>\n";
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
    Header("WWW-authenticate: basic realm=\"TouchStone\"");
    Header("HTTP/1.0 401 Unauthorised");
    echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Authentication with TouchStone has failed, please remember:</p>\n<ul style=\"margin-left:80px\">\n<li>Usernames/passwords are case-sensitive</li>\n<li>TouchStone now uses the LDAP passwords</li>\n<li>Press 'F5' on the keyboard to try logging in again</li>";
    if ($_SERVER['PHP_SELF'] == '/index.php') {
      echo "<li>If you are still stuck ask an invigilator for a <a href=\"guest_account.php\" style=\"color:blue\"><strong>Temporary Account</strong></a></li>";
    }
    echo "</ul>\n</body>\n</html>\n";
    $mysqli->close();
    exit;
  } else {
    //
    //  MAIN AUTHENTICATION ROUTINES
    //
    if (db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'],$mysqli) == false) {
      //try LDAP
      if ($cfg_use_ldap == true and $db_errors != '<strong>No TouchStone account found!</strong>') {
        $ldap_user_data = Array();
        if (ldap_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $ldap_user_data) == true) {
          //Ad Account OK
          $encpw_details = encpw($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
          $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE username = ?");
          $stmt->bind_param('ss', $encpw_details, $_SERVER['PHP_AUTH_USER']);
          $stmt->execute();
          if($stmt->affected_rows == 0) {
            //user has no touchstone account but valid LDAP
            echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
            echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
            echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
            echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">No TouchStone account found</p>\n<ul style=\"margin-left:80px\">\n<li>Please contact <a href=\"mailto:$support_email\">$support_email</a> if you believe you should have access.</li>\n";
            if ($_SERVER['PHP_SELF'] == '/index.php') {
              echo "<li>If you are still stuck ask an invigilator for a <a href=\"guest_account.php\" style=\"color:blue\"><strong>Temporary Account</strong></a></li>";
            }
            echo "</ul>\n</body>\n</html>";
            $stmt->close();
            $mysqli->close();
            exit;
          }
          $stmt->close();
          db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $mysqli, $ldap_user_data);
        } else {
          //Ad Account NOT OK
          Header("WWW-authenticate: basic realm=\"TouchStone\"");
          Header("HTTP/1.0 401 Unauthorised");
          echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
          echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
          echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
          echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Authentication with TouchStone has failed, please remember:</p>\n<ul style=\"margin-left:80px\">\n<li>Usernames/passwords are case-sensitive</li>\n<li>TouchStone now uses the LDAP passwords</li>\n<li>Press 'F5' on the keyboard to try logging in again</li>";
          if ($_SERVER['PHP_SELF'] == '/index.php') {
            echo "<li>If you are still stuck ask an invigilator for a <a href=\"guest_account.php\" style=\"color:blue\"><strong>Temporary Account</strong></a></li>";
          }
          echo "<li>" . $ldap_user_data['error'] . "</li>";
          echo "</ul>\n</body>\n</html>\n";
          exit;
        }
      } else {
        // Internal DB authentication failed and not to use LDAP.
        Header("WWW-authenticate: basic realm=\"TouchStone\"");
        Header("HTTP/1.0 401 Unauthorised");
        echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
        echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
        echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
        echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Authentication with TouchStone has failed, please remember:</p>\n<ul style=\"margin-left:80px\">\n<li>Usernames/passwords are case-sensitive</li>\n<li>Press 'F5' on the keyboard to try logging in again</li>";
        if ($db_errors != '') {
          echo "<li>$db_errors</li>\n";
        }
        if ($_SERVER['PHP_SELF'] == '/index.php') {
          echo "<li>If you are still stuck as an invigilator for a <a href=\"guest_account.php\" style=\"color:blue\"><strong>Temporary Account</strong></a></li>";
        }
        echo "</ul>\n</body>\n</html>\n";
        exit;
      }
    } 
    //pasword strength check could be added here!!
    
  }
} else {
  echo "<html>\n<body style=\"font-family:Arial,sans-serif\">\n<p style=\"font-size:150%;color:#C00000\"><strong>WARNING:</strong> Incompatible Browser</p>\n<p><strong>TouchStone currently requires MS Internet Explorer 5 (or above) or Firefox 2 (or above).</strong></p>\n<p><strong>You are running </strong>" . $_SERVER['HTTP_USER_AGENT'] . "</p>\n</body>\n</html>\n";
  $mysqli->close();
  exit;
}
?>