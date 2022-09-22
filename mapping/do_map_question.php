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
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);
require '../include/staff_auth.inc';
require '../include/mapping.inc';

$modules = param::required('objective_modules', param::TEXT, param::FETCH_POST);
$paperid = param::required('paperID', param::INT, param::FETCH_POST);
$questionid = param::required('questionID', param::INT, param::FETCH_POST);

// Write out curriculum mapping.
save_objective_mappings($mysqli, $modules, $paperid, $questionid);

echo json_encode('SUCCESS');
