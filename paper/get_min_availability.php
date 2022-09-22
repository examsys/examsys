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
 * Get the minimum availiblity period for the paper
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require_once '../include/errors.php';
try {
    $paperid = check_var('paperid', 'POST', true, false, true, param::INT);
    $exam_duration_hours = param::optional('exam_duration_hours', 0, param::INT, param::FETCH_POST);
    $exam_duration_mins = param::optional('exam_duration_mins', 0, param::INT, param::FETCH_POST);
    $exam_duration = $exam_duration_hours * 60;
    $exam_duration += $exam_duration_mins;
    $properties = PaperProperties::get_paper_properties_by_id($paperid, $mysqli, $string);
    $minavailability = $properties->getMinAvailability($exam_duration);
} catch (Exception $e) {
    echo json_encode('ERROR');
    exit();
}
echo json_encode(array('SUCCESS', $minavailability));
