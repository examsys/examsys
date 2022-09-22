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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';
$userid = check_var('userID', 'GET', true, false, true);
if (!UserUtils::userid_exists($userid, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$username     = UserUtils::get_username($userid, $mysqli);
$enc = new encryp();
$generated_password = $enc->gen_password(true);
$new_password = $generated_password['password'];
$display_password = $generated_password['display_password'];
$success = UserUtils::update_password($username, $new_password, $userid, $mysqli);
if (!$success) {
    display_error($string['resetfailed'], $string['failuremsg'], $configObject->get('cfg_root_path') . '/artwork/exclamation_red_bg.png', '#C00000', true, true, true);
}
$mysqli->close();
$render = new render($configObject);
$headerdata = array(
  'css' => array(
    '/css/screen.css',
    '/css/password.css',
  ),
  'scripts' => array(),
);
$lang['title'] = $string['passwordreset'];
$lang['pwdreset'] = $string['passwordreset'];
if ($enc->is_readable()) {
    $lang['pwdinfo'] = $string['passwordreadable'];
} else {
    $lang['pwdinfo'] = $string['passwordnonreadable'];
}
$lang['msg'] = $string['msg'];
$lang['ok'] = $string['ok'];
$data['dispwd'] = $display_password;
$render->render($headerdata, $string, 'header.html');
$render->render($data, $lang, 'users/password.html');
$render->render_admin_footer();
