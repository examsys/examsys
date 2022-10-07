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
 * Displays a list of previous attempts on a paper.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 */

require '../include/staff_student_auth.inc';
require_once '../include/errors.php';

$id = check_var('id', 'GET', true, false, true, param::ALPHANUM);

// Create paper object.
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);
$property_id = $propertyObj->get_property_id();
$test_type = $propertyObj->get_paper_type();
$marking_style = $propertyObj->get_marking();
$total_marks = $propertyObj->get_total_mark();
$total_random_mark  = $propertyObj->get_random_mark();
$log = log::get_paperlog($test_type);
$prev_attempts = $log->loadAttempts($property_id, $userObject->get_user_ID(), $marking_style, $total_marks, $total_random_mark);

$render = new render($configObject);

$headerdata = array(
    'css' => [
        '/css/header.css',
        '/css/warnings.css',
        '/css/list.css',
    ],
    'scripts' => ['/js/previousinit.min.js'],
);
$render->render($headerdata, $string, 'header.html');

// Render the user list.
$render->render($prev_attempts, $string, 'users/paperlist.html');

$jsdataset = [
    'name' => 'jsutils',
    'attributes' => ['xls' => json_encode($string)],
];
$render->render($jsdataset, array(), 'dataset.html');
$dataset['name'] = 'dataset';
$dataset['attributes']['id'] = $id;
$render->render($dataset, array(), 'dataset.html');

$render->render([], [], 'footer.html');
