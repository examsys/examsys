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
 * Update the system errors.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require '../include/errors.php';

$errorID = check_var('errorID', 'POST', true, false, true);
$found = false;

$result = $mysqli->prepare('SELECT NULL FROM sys_errors where id = ?');
$result->bind_param('i', $errorID);
$result->execute();
$result->store_result();
if ($result->num_rows > 0) {
    $found = true;
}
$result->close();

if (!$found) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$result = $mysqli->prepare('UPDATE sys_errors SET fixed = NOW() WHERE id = ?');
$result->bind_param('i', $errorID);
$result->execute();
$result->close();

if ($mysqli->errno == 0) {
    echo json_encode('SUCCESS');
} else {
    echo json_encode('ERROR');
}
