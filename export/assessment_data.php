<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Report that exports responses in CSV format (raw or text).
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

$paperID    = check_var('paperID', 'GET', true, false, true, param::INT);
$startdate  = check_var('startdate', 'GET', true, false, true, param::SQLDATETIME);
$enddate    = check_var('enddate', 'GET', true, false, true, param::SQLDATETIME);
$percentile    = check_var('percent', 'GET', true, false, true, param::FLOAT);
$studentonly    = check_var('studentsonly', 'GET', true, false, true, param::BOOLEAN);
$repcourse    = check_var('repcourse', 'GET', true, false, true, param::TEXT);

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$demo = \demo::is_demo($userObject);

$mode = param::optional('mode', 'numeric', param::ALPHA, param::FETCH_GET);
if ($mode != 'text') {
  $mode = 'numeric';
}

// The repmodule parameter can be an comma separated list of integers.
// Examples of matches:
// 123
// 123,456
// 123, 456
// Values that match this are safe for direct use in the SQL.
$repmodule_options = array(
  'default' => null,
  'regexp' => '#\d+(,\s?\d+)*#',
);
$repmodule = param::optional('repmodule', '', param::REGEXP, param::FETCH_GET, $repmodule_options);

$students = $propertyObj->get_user_list($startdate, $enddate, $percentile, $studentonly, $repmodule);
$student_no = count($students);
$student_list = implode(',', $students);

// Get any questions to exclude.
$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();

// Capture the paper makeup.
$paper_buffer = $propertyObj->get_paper_questions();
$paper_title = $propertyObj->get_paper_title();

$file = str_replace(' ', '_', $paper_title) . "_ER.csv";
$handler = new \csv\csv_handler($file);
$export = new \export\export_assessment($handler);
$csvdata = array();

if ($student_no > 0) {
  $log_array = $propertyObj->get_paper_assessment_data($repcourse, $startdate, $enddate, $student_list, $studentonly, $demo);
  // Header.
  $export->create_dynamic_header($paper_buffer, $exclusions);
  // Correct answers line.
  $csvdata = $export->create_correct_answer($paper_buffer, $exclusions, $mode, $string, $language);
  // Data lines.
  $csvdata = array_merge($csvdata, $export->create_data($log_array, $paper_buffer, $exclusions, $mode, $string, $language));
} else {
  $export->dynamic_headers = array();
  $csvdata[] = str_getcsv($string['nodata'] . ',,,,,,,');

}
$export->execute($csvdata);