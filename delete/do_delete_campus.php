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
 * Delete a campus - SysAdmin only.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$campus = check_var('campus', 'POST', true, false, true);

$result = $mysqli->prepare('DELETE FROM campus WHERE id = ?');
$result->bind_param('i', $campus);
$result->execute();
$result->close();
if ($mysqli->errno != 0) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$render = new render($configObject);
$lang['title'] = $string['campusclientdel'];
$lang['success'] = $string['campusdelsuccess'];
$data = array();
$render->render($data, $lang, 'admin/do_delete.html');
