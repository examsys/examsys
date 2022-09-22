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
 * Perform session edit.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$year = check_var('year', 'GET', true, false, true);
$academic_year = param::required('academic_year', param::TEXT, param::FETCH_POST);
$cal_status = param::optional('cal_status', false, param::BOOLEAN, param::FETCH_POST);
$stat_status = param::optional('stat_status', false, param::BOOLEAN, param::FETCH_POST);

$result = $mysqli->prepare('SELECT academic_year, cal_status, stat_status FROM academic_year WHERE calendar_year = ?');
$result->bind_param('i', $year);
$result->execute();
$result->store_result();
$result->bind_result($curr_academic_year, $curr_cal_status, $curr_stat_status);
$result->fetch();
if ($result->num_rows == 0) {
    $result->close();
    echo json_encode('ERROR');
    exit();
}
$result->close();

$changed = ($curr_academic_year != $academic_year or $curr_cal_status != $cal_status or $curr_stat_status != $stat_status);

if ($changed) {
    $result = $mysqli->prepare('UPDATE academic_year SET academic_year = ?, cal_status = ?, stat_status = ? WHERE calendar_year = ?');
    $result->bind_param('siii', $academic_year, $cal_status, $stat_status, $year);
    $result->execute();
    $result->close();
    if ($mysqli->errno != 0) {
        echo json_encode('ERROR');
        exit();
    }
    $logger = new Logger($mysqli);
    if ($curr_academic_year != $academic_year) {
        $logger->track_change('Academic Session', $year, $userObject->get_user_ID(), $curr_academic_year, $academic_year, $string['academicyear']);
    }
    if ($curr_cal_status != $cal_status) {
        $logger->track_change('Calendar Status', $year, $userObject->get_user_ID(), $curr_cal_status, $cal_status, $string['calstatus']);
    }
    if ($curr_stat_status != $stat_status) {
        $logger->track_change('Statistics Status', $year, $userObject->get_user_ID(), $curr_stat_status, $stat_status, $string['statstatus']);
    }
}

echo json_encode('SUCCESS');
