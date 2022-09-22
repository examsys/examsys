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
 * Edit the announcement
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require '../include/errors.php';

$announcementid = check_var('announcementid', 'REQUEST', true, false, true);

if (!announcement_utils::announcement_exist($announcementid, $mysqli)) {
    echo json_encode('ERROR');
    exit();
}

$news_title = param::required('title', param::TEXT, param::FETCH_POST);
$staff_msg = param::optional('staff_msg', '', param::HTML, param::FETCH_POST);
$student_msg = param::optional('student_msg', '', param::HTML, param::FETCH_POST);
$fyear = param::required('fyear', param::TEXT, param::FETCH_POST);
$fmonth = param::required('fmonth', param::TEXT, param::FETCH_POST);
$fday = param::required('fday', param::TEXT, param::FETCH_POST);
$ftime = param::required('ftime', param::TEXT, param::FETCH_POST);
$tyear = param::required('tyear', param::TEXT, param::FETCH_POST);
$tmonth = param::required('tmonth', param::TEXT, param::FETCH_POST);
$tday = param::required('tday', param::TEXT, param::FETCH_POST);
$ttime = param::required('ttime', param::TEXT, param::FETCH_POST);
$startdate = $fyear . $fmonth . $fday . $ftime;
$enddate = $tyear . $tmonth . $tday . $ttime;
$icon = str_replace('icon', '', param::required('icon_type', param::TEXT, param::FETCH_POST));  // Take the word icon out, store only the number.

$result = $mysqli->prepare('UPDATE announcements SET title = ?, staff_msg = ?, student_msg = ?, icon = ?, startdate = ?, enddate = ? WHERE id = ?');
$result->bind_param('ssssssi', $news_title, $staff_msg, $student_msg, $icon, $startdate, $enddate, $announcementid);
$result->execute();
$result->close();

if ($mysqli->errno == 0) {
    echo json_encode('SUCCESS');
} else {
    echo json_encode('ERROR');
}
