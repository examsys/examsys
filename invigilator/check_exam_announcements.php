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

require_once '../include/invigilator_auth.inc';

$paperID = $_GET['paperID'];
if (!isset($string)) {
    $string = array();
}

$exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);

$exam_announcements = $exam_announcementObj->get_announcements();

if (count($exam_announcements) == 0) {
    echo '<span class="blankclarification">' . $string['examquestionclarifications'] . '</span>';
    exit();
}

echo '<table><tbody>';
foreach ($exam_announcements as $exam_announcement) {
    $msg = $exam_announcement['msg'];
    if (mb_substr_count($msg, '<p>')) {
        $msg = str_replace('<p>', '', $msg);
        $msg = str_replace('</p>', '', $msg);
    }
    echo '<tr><td class="q_no">Q' . $exam_announcement['q_number'] . '</td><td class="q_msg">' . $msg . '</td></tr>';
}
echo '</tbody></table>';
