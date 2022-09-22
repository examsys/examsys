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
 * Add event
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';

$title = param::required('title', param::TEXT, param::FETCH_POST);
$message = param::required('message', param::TEXT, param::FETCH_POST);
$thedate = param::required('fyear', param::TEXT, param::FETCH_POST)
    . param::required('fmonth', param::TEXT, param::FETCH_POST)
    . param::required('fday', param::TEXT, param::FETCH_POST)
    . param::required('ftime', param::TEXT, param::FETCH_POST);
$duration = param::required('duration', param::INT, param::FETCH_POST);
$bgcolor = '#' . param::required('color', param::ALPHANUM, param::FETCH_POST);

$result = $mysqli->prepare('INSERT INTO extra_cal_dates VALUES (NULL, ?, ?, ?, ?, ?, NULL)');
$result->bind_param('sssis', $title, $message, $thedate, $duration, $bgcolor);
$result->execute();
$result->close();
if ($mysqli->errno == 0) {
    echo json_encode('SUCCESS');
} else {
    echo json_encode('ERROR');
}
