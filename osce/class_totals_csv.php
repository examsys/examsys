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
* Outputs the results of an OSCE station as in CSV format.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

require_once '../include/demo_replace.inc';
require_once '../include/errors.php';
require_once '../include/sort.inc';
require_once './osce.inc';

$demo = is_demo($userObject);

$paperID   = check_var('paperID', 'GET', true, false, true);
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate   = check_var('enddate', 'GET', true, false, true);

$percent      = (isset($_GET['percent'])) ? $_GET['percent'] : 100;
$ordering     = (isset($_GET['ordering'])) ? $_GET['ordering'] : 'asc';
$absent       = (isset($_GET['absent'])) ? $_GET['absent'] : 0;
$sortby       = (isset($_GET['sortby'])) ? $_GET['sortby'] : 'name';
$studentsonly = (isset($_GET['studentsonly'])) ? $_GET['studentsonly'] : 1;
$repcourse    = (isset($_GET['repcourse'])) ? $_GET['repcourse'] : '%';
$repmodule    = (isset($_GET['repmodule'])) ? $_GET['repmodule'] : '';

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper = $propertyObj->get_paper_title();
$crypt_name = $propertyObj->get_crypt_name();

$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();                                        // Get any questions to exclude.

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli, $string);
$report->load_answers();
$paper_buffer = $report->get_paper_buffer();
$question_no  = $report->get_question_no();

$user_results = load_osce_results($propertyObj, $demo, $configObject, $question_no, $mysqli);
$report->set_user_results($user_results);
$report->generate_stats();
$user_no = $report->get_user_no();

$q_medians = load_osce_medians($mysqli);

if ($propertyObj->get_pass_mark() == 101) {
  $borderline_method = true;
} else {
  $borderline_method = false;
}

if ($borderline_method) {
  $passmark = getBlinePassmk($user_results, $user_no, $propertyObj);
} elseif ($propertyObj->get_pass_mark() == 102) {
  $passmark = 'N/A';
} else {
  $passmark = $propertyObj->get_pass_mark();
}
$distinction_mark = $propertyObj->get_distinction_mark();

set_classification($propertyObj->get_marking(), $user_results, $passmark, $user_no, $string);
rating_num_text($user_results, $user_no, $propertyObj, $string);
$user_results = array_csort($user_results, $sortby, $ordering);

$stats = $report->get_stats();                        // Generate the main statistics

$results_cache = new ResultsCache($mysqli);
if ($results_cache->should_cache($propertyObj, $percent, $absent)) {
  $results_cache->save_paper_cache($paperID, $stats);                 // Cache general paper stats
  
  $results_cache->save_student_mark_cache($paperID, $user_results);   // Cache student/paper marks
  
  $results_cache->save_median_question_marks($paperID, $q_medians);    // Cache the question/paper medians
}

header('Pragma: public');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".csv");

$completed_no = 0;
$total_score = 0;

// Output table heading
if ($borderline_method) {
  $table_order = array($string['title'], $string['surname'], $string['firstnames'], $string['studentid'], $string['course'], $string['total'], $string['rating'], $string['classification'], $string['starttime'], $string['examiner']);
} else {
  $table_order = array($string['title'], $string['surname'], $string['firstnames'], $string['studentid'], $string['course'], $string['total'], $string['classification'], $string['starttime'], $string['examiner']);
}

$col_no = 0;
foreach ($table_order as $col_string) {
  if ($col_no > 0) echo ',';
  echo $col_string;
  $col_no++;
}
echo "\n";

for ($i=0; $i<$user_no; $i++) {
  echo $user_results[$i]['title'] . ',"' . $user_results[$i]['surname'] . '","' . $user_results[$i]['first_names'] . '",';
  if ($user_results[$i]['student_id'] == '') {
    echo "Unknown,";
  } else {
    echo $user_results[$i]['student_id'] . ",";
  }
  if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
    echo ",,No Attendance,,\n";
  } else {
    echo $user_results[$i]['grade'] . "," . $user_results[$i]['numeric_score'];
    if ($borderline_method) {
      echo "," . $user_results[$i]['rating'];
    }
    echo "," . $user_results[$i]['classification'] . "," . $user_results[$i]['display_started'] . ",\"" . $user_results[$i]['examiner'] . "\"\n";
  }
}
echo ",,,,,,,,,\n";

echo "Cohort Size,$user_no,,,,,,,,\n";

$mysqli->close();
?>
