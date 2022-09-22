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
 * Delete a course - SysAdmin only.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require '../include/errors.php';

$eventID = check_var('eventID', 'POST', true, false, true);

$result = $mysqli->prepare('UPDATE extra_cal_dates SET deleted = NOW() WHERE id = ?');
$result->bind_param('i', $eventID);
$result->execute();
$result->close();

$render = new render($configObject);
$lang['title'] = $string['delete'];
$lang['success'] = $string['success'];
$data = array();
$render->render($data, $lang, 'admin/do_delete.html');
