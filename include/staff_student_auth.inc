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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

/**
 *  This function is used to catch early errors and convert them to exeptions
 *  so we can catch them. I is later replaced by the more complex customErrorHandler
 *  in include/custom_error_handler.inc
 */

function handle_early_errors($errno, $errstr, $errfile, $errline) {
  if (strpos($errstr, 'config.inc.php')) {
    //turn a missing config file working into an exception so we can catch it
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);

    return true;
  }

  return false;
}

//set_error_handler("handle_early_errors");

$root = str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));
require_once $root . 'include/auth.inc';
include_once $root . 'include/load_config.php';
if (!file_exists($root . 'config/config.inc.php')) {
  $mysqli = null;
  $cfg_root_path = rtrim('/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $root), '/'), '/');
  $notice->display_notice_and_exit(null, 'Install Required', 'Rog&#333; needs installing before use. The Installer is avalable <a href="' . $cfg_root_path . '/install/index.php">here</a>.', 'Install Required', '../artwork/software_64.png', $title_color = '#C00000', true, true);
}

require_once $cfg_web_root . 'classes/configobject.class.php';

//start the session early as the lang class looks in the session 
if ($configObject->get('cfg_session_name') != '') {
  session_name($configObject->get('cfg_session_name'));
} else {
  session_name('RogoAuthentication');
}
$return = session_start();

require_once $cfg_web_root . 'classes/lang.class.php';
require_once $cfg_web_root . 'lang/' . $language . '/include/common.inc'; // Include common language file that all scripts need
require_once $cfg_web_root . 'include/custom_error_handler.inc';
require_once $cfg_web_root . 'classes/dbutils.class.php';
require_once $cfg_web_root . 'classes/networkutils.class.php';
require_once $cfg_web_root . 'classes/userobject.class.php';
require_once $cfg_web_root . 'classes/authentication.class.php';

if (is_null($configObject->get('cfg_db_port'))) {
  $configObject->set('cfg_db_port', 3306);
}
$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

// Check to see if the update script has been run.
if ($configObject->get('rogo_version') != '6.0.4') {
  $notice->display_notice_and_exit($mysqli, 'Update Required', 'System needs configuring before use. Please contact your local system administrator.', 'Update Required', '../artwork/software_64.png', $title_color = '#C00000', true, true);
}

$fp_link = "<p style=\"margin-left:60px\"><a href=\"{$configObject->get('cfg_root_path')}/users/forgotten_password.php\">" . $string['forgottenpassword'] . "</a></p>\n";

// Check for secure protocol
if ($configObject->get('cfg_secure_connection')) {
  if (! ( (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') or (isset($_SERVER['REQUEST_SCHEME']) and $_SERVER['REQUEST_SCHEME'] == 'https') ) ) {
    $msg = $string['secureconnectionmsg'] . ' <a href="https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '">https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '</a>.';
    $notice->display_notice_and_exit($mysqli, $string['secureconnection'], $msg, $string['secureconnection'], '../artwork/secure_connection.png', $title_color = '#C00000', true, true);
  }
}

$authentication = new Authentication($configObject, $mysqli, $_REQUEST, $_SESSION);
$authentication->do_authentication($string);
$getauthobj = new auth_obj();
$authentication->get_auth_obj($getauthobj);

$userObject = UserObject::get_instance();
$userObject->db_user_change();

$included_files = get_included_files();

//set string encoding for the mbstring module for interfaces
mb_internal_encoding($configObject->get('cfg_page_charset'));
?>
