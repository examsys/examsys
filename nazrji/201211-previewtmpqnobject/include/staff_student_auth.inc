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
 * Authentication routine which permits staff and student access to a page.
 *
 * @author Simon Wilkinson, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

/**
*  This function is used to catch early errors and convert them to exeptions 
*  so we can catch them. I is later replaced by the more complex customErrorHandler
*  in include/custom_error_handler.inc
*/




function handle_early_errors($errno, $errstr, $errfile, $errline) {
 if(strpos($errstr,'config.inc.php')) {
    //turn a missing config file working into an exception so we can catch it
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    return true;
  }
  return false;
}
set_error_handler("handle_early_errors");

$root = str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));
require_once $root . 'include/auth.inc';
try {
  include_once $root . 'config/config.inc.php';
} catch (Exception $e) {
   display_notice('Install Required', 'Rogō needs installing before use. The Installer is avalable <a href="./install/index.php">here</a>.', '../artwork/software_64.png', $title_color = '#C00000');
  echo "\n</body>\n</html>\n";
  exit;
}
require_once $cfg_web_root . 'classes/lang.class.php';
require_once $cfg_web_root . 'lang/' . $language . '/include/common.inc'; // Include common language file that all scripts need
require_once $cfg_web_root . 'include/custom_error_handler.inc';
require_once $cfg_web_root . 'classes/dbutils.class.php';
require_once $cfg_web_root . 'classes/networkutils.class.php';
require_once $cfg_web_root . 'LTI/ims-lti/UoN_LTI.php';
require_once $cfg_web_root . 'include/lti_func.php';
require_once $cfg_web_root . 'classes/lti_integration.class.php';

require_once $cfg_web_root . 'classes/userobject.class.php';





// Check to see if the update script has been run.
if ($rogo_version != '4.4') {
  display_notice('Update Required', 'System needs configuring before use. Please contact your local system administrator.', '../artwork/software_64.png', $title_color = '#C00000');
  echo "\n</body>\n</html>\n";
  exit;
}

/*
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('xdebug.remote_autostart', 1);
*/

$fp_link = "<p style=\"margin-left:60px\"><a href=\"{$cfg_root_path}/users/forgotten_password.php\">" . $string['forgottenpassword'] . "</a></p>\n";

if ($protocol == 'https://' and $_SERVER['SERVER_PORT'] != 443 and $_SERVER['SERVER_PORT'] != 8080) {
  $msg = $string['secureconnectionmsg'] . ' <a href="https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '">https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '</a>.';
  display_notice($string['secureconnection'], $msg, '../artwork/secure_connection.png', $title_color = '#C00000');
  echo "\n</body>\n</html>\n";
  exit;
}
if (preg_match('|MSIE ([7-9].[0-9]{1,2})|', $_SERVER['HTTP_USER_AGENT'], $log_version) or preg_match('|Netscape6|', $_SERVER['HTTP_USER_AGENT'], $log_version) or preg_match('|Netscape\/7.0|', $_SERVER['HTTP_USER_AGENT'], $log_version) or preg_match('|Mozilla\/5.0|', $_SERVER['HTTP_USER_AGENT'], $log_version) or preg_match('|PHP-SOAP|', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
  $mysqli = DBUtils::get_mysqli_link($cfg_db_host, $cfg_db_username, $cfg_db_passwd, $cfg_db_database, $cfg_db_charset, $dbclass);
  if(!isset($userObject)) {
    global $userObject;
    $userObject = new UserObject($mysqli);
  }
  session_start();
  $lti = new UoN_LTI($mysqli);

  $lti->init_lti();
  if ($lti->valid) {
    //LTI Correct

    $check = usercheck($mysqli, $lti);

    if ($check > 0) {
      $datareturned=db_userlookup($check[0], $mysqli, true);
      
      if ($datareturned === false) {
        display_notice($string['UserMissing'], $string['UserMissingDesc'], '/artwork/access_denied.png', '#C00000');
        exit();
      }

      $userObject->old_load($datareturned);
      //list($password, $userID, $userroles, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $record_no, $split_username) = $datareturned;


      db_change_user($mysqli,$userObject);
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


  if (isset($lti->message) and $lti->message != 'Could not find context in session' and $lti->message != false) {
    // DO WE WANT THIS??
    $tempvar = $lti->message;
    if (strpos($tempvar, 'Expired timestamp') !== false) {
      $tempvar = 'expiredtimestamp';
    }
    if (!isset($string[$lti->message])) {
      $string[$lti->message] = $lti->message;
    }
    display_notice($string['LTIFAILURE'], $string[$tempvar], '/artwork/access_denied.png', '#C00000');
    $mysqli->close();
    exit;
  }

  if (!$lti->valid) {
    if (!isset($_SERVER['PHP_AUTH_USER']) and !($lti->valid)) {

      //replace with function for standard login failure

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
      if (($dbret = db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $mysqli,true,$userObject)) <= 0) {
        //try LDAP
        if ($cfg_use_ldap == true and ($db_errors != '<strong>' . $string['notsaccount'] . '</strong>' or $dbret == -2)) {
          $ldap_user_data = array();
          if (ldap_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $ldap_user_data) == true) {
            //Ad Account OK
            $encpw_details = encpw($cfg_encrypt_salt, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
            $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param('ss', $encpw_details, $_SERVER['PHP_AUTH_USER']);
            $stmt->execute();
            if ($stmt->affected_rows == 0) {
              //user has no Rogo account but valid LDAP
              $stmt->close();

              $message = $string['notsaccount'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>Please contact <a href=\"mailto:$support_email\">$support_email</a> if you believe you should have access.</li>\n";
              if ($_SERVER['PHP_SELF'] == '/index.php') {
                $message .= $string['ifstuckinvigilator'] . " <a href=\"guest_account.php\" style=\"color:blue\"><strong>" . $string['tempaccount'] . "</strong></a></li>";
              }
              $message .= "</ul>";

              access_denied($message, true);
            }
            $stmt->close();
            db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $mysqli);
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
            if (!array_reduce($cfg_institutional_domains, 'NetworkUtils::check_email_domain')) {
              $message .= $fp_link;
            }
            access_denied($message, true);
          }
        } else {
          // Internal DB authentication failed.
          if ($cfg_use_ldap == true and  ldap_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $ldap_user_data) == true and strpos($_SERVER['PHP_SELF'], 'self_enrol.php') !== FALSE) {
            //we have a valid LDAP account - we are in the self enrol script allow execution to continue
            //continue
            $result = $mysqli->change_user($cfg_db_staff_user, $cfg_db_staff_passwd, $cfg_db_database); // Swap to staff user for appropriate database permissions
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
  }
} else {
  display_notice('Browser Incompatibility', 'Rogo currently requires MS Internet Explorer 7 (or above) or Firefox 4 (or above).', '../artwork/browser.png', $title_color = '#C00000');
  echo "\n</body>\n</html>\n";
  exit;
}
?>