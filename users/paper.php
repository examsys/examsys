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
 * Lists all users on a paper.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

require '../include/staff_auth.inc';
require_once '../include/errors.php';

// When the paperID parameter is used the user accessing gets checked for access to the paper.
$paperid = check_var('paperID', param::FETCH_GET, true, false, true, param::INT);

$properties = PaperProperties::get_paper_properties_by_id($paperid, $mysqli, $string, true);

$render = new render($configObject);

$headerdata = array(
  'css' => [
    '/css/header.css',
    '/css/warnings.css',
    '/css/list.css',
  ],
  'scripts' => [],
  'metadata' => [],
);
$render->render($headerdata, $string, 'header.html');

// Render the user list.
$render->render($properties->get_users(), $string, 'users/userlist.html');

$jsdataset = [
  'name' => 'jsutils',
  'attributes' => ['xls' => json_encode($string)],
];
$render->render($jsdataset, array(), 'dataset.html');

$render->render([], [], 'footer.html');
