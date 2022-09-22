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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../../include/staff_auth.inc';
$questionsarray = array();
$questions_to_add = param::required('questions_to_add', param::TEXT, param::FETCH_POST);
$questions = explode(',', $questions_to_add);
foreach ($questions as $item) {
    if ($item != '') {
        $stmt = $mysqli->prepare('SELECT leadin FROM questions WHERE q_id=?');
        $stmt->bind_param('i', $item);
        $stmt->execute();
        $stmt->bind_result($leadin);
        $stmt->fetch();
        $stmt->close();

        $leadin = trim(strip_tags($leadin));
        $leadin = preg_replace('/\r\n/', ' ', $leadin);
        if (mb_strlen($leadin) > 160) {
            $leadin = mb_substr($leadin, 0, 160) . '...';
        }
        $questionsarray[$item] = addslashes($leadin);
    }
}
$mysqli->close();
echo json_encode($questionsarray);
