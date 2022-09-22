<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Authentication routine which permits staff and student access to a page.
 *
 * @author Simon Wilkinson
 * @author Anthony Brown
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 2.0
 * @copyright Copyright (c) 2017 The University of Nottingham
 * @package
 */

require_once __DIR__ . '/load_config.php';

if (!InstallUtils::config_exists()) {
    require_once __DIR__  . '/path_functions.inc.php';
    $root = get_relative_path_to_root();
    header('location: ' . $root . 'install/index.html', true, 303);
    exit();
}

// Cookie sessions only.
ini_set('session.use_only_cookies', 1);
// Use secure cookie if on secure conenction.
if ($configObject->get('cfg_secure_connection')) {
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
}
//start the session early as the lang class looks in the session
if ($configObject->get('cfg_session_name') != '') {
    session_name($configObject->get('cfg_session_name'));
} else {
    session_name('RogoAuthentication');
}

if (ini_get('session.save_handler') === 'memcache' or ini_get('session.save_handler') === 'memcached') {
    $sessionhandler = new memcachesessionhandler();
    session_set_save_handler($sessionhandler);
}
$return = session_start();

$language = LangUtils::getLang($cfg_web_root);
LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));

require_once $cfg_web_root . 'lang/' . $language . '/include/common.php'; // Include common language file that all scripts need
require_once $cfg_web_root . 'include/custom_error_handler.inc';

if (is_null($configObject->get('cfg_db_port'))) {
    $configObject->set('cfg_db_port', 3306);
}
$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));
$configObject->set_db_object($mysqli);

$fp_link = "<p style=\"margin-left:60px\"><a href=\"{$configObject->get('cfg_root_path')}/users/forgotten_password.php\">" . $string['forgottenpassword'] . "</a></p>\n";

// Check for secure protocol
if ($configObject->get('cfg_secure_connection')) {
    if (
        ! ( (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') or
          (isset($_SERVER['REQUEST_SCHEME']) and $_SERVER['REQUEST_SCHEME'] == 'https') or
          (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') )
    ) {
        $msg = $string['secureconnectionmsg'] . ' <a href="https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '">https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '</a>.';
        $notice->display_notice_and_exit($mysqli, $string['secureconnection'], $msg, $string['secureconnection'], '/artwork/secure_connection.png', $title_color = '#C00000', true, true);
    }
}

$authentication = new Authentication($configObject, $mysqli, $_REQUEST, $_SESSION);
$authentication->do_authentication($string);
$getauthobj = new auth_obj();
$authentication->get_auth_obj($getauthobj);

// Recover any lost POST data and re-apply if config option enabled
if ($configObject->get_setting('core', 'system_recover_postdata') && isset($_SESSION['recovered_postdata'])) {
    $_POST += unserialize($_SESSION['recovered_postdata']);
    unset($_SESSION['recovered_postdata']);
}

$userObject = UserObject::get_instance();
$userObject->db_user_change();
if (!$userObject->has_role('SysAdmin')) {
    if (version::is_version_higher($configObject->getxml('version'), $configObject->get_setting('core', 'rogo_version')) or $configObject->get_setting('core', 'system_maintenance_mode')) {
        header('location: ' . $configObject->get('cfg_root_path') . '/maintenance/index.php?from=' . urlencode($_SERVER['REQUEST_URI']), true, 303);
        exit();
    }
}
$included_files = get_included_files();

//set string encoding for the mbstring module for interfaces
mb_internal_encoding($configObject->get('cfg_page_charset'));
