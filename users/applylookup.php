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
 * Apply lookup data to record.
 *
 * @author Simon Wilkinson, Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/admin_auth.inc';

$lookupid = param::required('LOOKUP', param::INT, param::FETCH_GET);

if (isset($_SESSION['ldaplookupdata'][$lookupid])) {
    $lookup = Lookup::get_instance($configObject, $mysqli);
    $data = new stdClass();
    $data->lookupdata = $_SESSION['ldaplookupdata'][$lookupid];

    $output = $lookup->userlookup($data);

    if (!isset($output->lookupdata->yearofstudy)) {
        $output->lookupdata->yearofstudy = '';
    }
    if (!isset($output->lookupdata->studentID)) {
        $output->lookupdata->studentID = '';
    }
    if (!isset($output->lookupdata->coursecode)) {
        $output->lookupdata->coursecode = '';
    }
    if (!isset($output->lookupdata->gender)) {
        $output->lookupdata->gender = '';
    }
    $output->lookupdata->title = StringUtils::my_ucwords($output->lookupdata->title); // Stop problems with uppercase titles.
    if ($output->lookupdata->title == 'Prof') {
        $output->lookupdata->title = 'Professor';
    }

    unset($_SESSION['ldaplookupdata']);
    echo json_encode(array('type' => 'SUCCESS',
    'title' => $output->lookupdata->title,
    'surname' => $output->lookupdata->surname,
    'firstname' => $output->lookupdata->firstname,
    'username' => $output->lookupdata->username,
    'email' => $output->lookupdata->email,
    'coursecode' => $output->lookupdata->coursecode,
    'gender' => $output->lookupdata->gender,
    'yearofstudy' => $output->lookupdata->yearofstudy,
    'studentID' => $output->lookupdata->studentID));
    exit();
} else {
    unset($_SESSION['ldaplookupdata']);
    echo json_encode(array('type' => 'ERROR'));
}
