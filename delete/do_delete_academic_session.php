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
 * Delete an academic session.
 *
 * @author Dr Joseph Baxter
 * @copyright Copyright (c) 2015 The University of Nottingham
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$year = check_var('year', 'REQUEST', true, false, true);
$yearutils = new yearutils($mysqli);

if (!$yearutils->check_calendar_year($year)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$success = $yearutils->delete_year($year, $userObject->get_user_ID());

$render = new render($configObject);
$lang = $string;
if ($success == true) {
    $lang['success'] =  $string['success'];
} else {
    $lang['success'] =  $string['failure'];
}
$data = array();
$render->render($data, $lang, 'admin/do_delete.html');

$mysqli->close();
