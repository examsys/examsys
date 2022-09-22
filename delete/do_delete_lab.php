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
 * Delete a lab and all the client identifiers in it - Admin only.
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/admin_auth.inc';
require '../include/errors.php';

$labID = check_var('labID', 'POST', true, false, true);

$lab_no = 0;

$result = $mysqli->prepare('SELECT name FROM labs WHERE id = ?');
$result->bind_param('i', $labID);
$result->execute();
$result->store_result();
$result->bind_result($lab_name);
$result->fetch();
$lab_no = $result->num_rows;
$result->close();

if ($lab_no == 0) {
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $contactemail = support::get_email();
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$result = $mysqli->prepare('DELETE FROM client_identifiers WHERE lab = ?');
$result->bind_param('i', $labID);
$result->execute();
$result->close();

$result = $mysqli->prepare('DELETE FROM labs WHERE id = ?');
$result->bind_param('i', $labID);
$result->execute();
$result->close();

$render = new render($configObject);
$lang['title'] = $string['title'];
$lang['success'] = $string['msg'];
$data = array();
$render->render($data, $lang, 'admin/do_delete.html');

$mysqli->close();
