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
 * Allows the properties of a paper to be edited.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/errors.php';
$paperID   = check_var('paperID', 'POST', true, false, true);
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$startdate = $properties->get_raw_start_date();
$enddate   = $properties->get_raw_end_date();
$report = new ClassTotals(1, 100, 'asc', 0, 'name', $userObject, $properties, $startdate, $enddate, '%', '', $mysqli, $string);
$report->compile_report(true);
$properties->set_recache_marks(0);
// Set the recache to zero to stop it caching again.
$properties->save();
$mysqli->close();
