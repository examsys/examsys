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
 * Add the session.
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';

$yearutils = new yearutils($mysqli);

$calendar_year = param::required('calendar_year', param::INT, param::FETCH_POST);

if ($yearutils->check_calendar_year($calendar_year)) {
    echo json_encode('DUPLICATE');
    exit();
} else {
    $academic_year = param::required('academic_year', param::TEXT, param::FETCH_POST);
    $cal_status = param::optional('cal_status', false, param::BOOLEAN, param::FETCH_POST);
    $stat_status = param::optional('stat_status', false, param::BOOLEAN, param::FETCH_POST);

    $result = $mysqli->prepare('INSERT INTO academic_year (calendar_year, academic_year, cal_status, stat_status) values(?, ?, ?, ?)');
    $result->bind_param('isii', $calendar_year, $academic_year, $cal_status, $stat_status);
    $result->execute();
    $result->close();

    if ($mysqli->errno == 0) {
        echo json_encode('SUCCESS');
    } else {
        echo json_encode('ERROR');
    }
}
