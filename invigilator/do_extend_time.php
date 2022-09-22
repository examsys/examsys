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
 * Extends the time available to a user for an exam.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/invigilator_auth.inc';

$student_id = param::optional('userID', 0, param::INT, param::FETCH_POST);
if ($student_id !== 0) {
    $paper_id = param::optional('paperID', 0, param::INT, param::FETCH_POST);
    if ($paper_id !== 0) {
        $student = array();
        $student['user_ID'] = $student_id;

        $current_address = NetworkUtils::get_client_address();

        $lab_factory = new LabFactory($mysqli);
        $lab_object = $lab_factory->get_lab_based_on_client($current_address);

        $propertyObj = PaperProperties::get_paper_properties_by_id($paper_id, $mysqli, $string);
        $log_lab_end_time = $propertyObj->getLogLabEndTime($lab_object->get_id());
        $log_extra_time = new LogExtraTime($log_lab_end_time, $student, $mysqli);

        $invigilator_id = $userObject->get_user_ID();

        $extratime = param::optional('extra_time', 0, param::INT, param::FETCH_POST);
        if ($extratime == 0) {
            $log_extra_time->delete($invigilator_id);
        } elseif ($extratime > 0) {
            $special_needs_percentage = $extratime;
            $log_extra_time->save($invigilator_id, $special_needs_percentage);
        }

        echo json_encode('SUCCESS');
        exit();
    }
}

echo json_encode('ERROR');
