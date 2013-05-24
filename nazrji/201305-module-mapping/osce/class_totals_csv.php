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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

require_once '../include/demo_replace.inc';
require_once '../include/errors.inc';
require_once '../include/sort.inc';
require_once './osce.inc';

require_once '../classes/paperutils.class.php';
require_once '../classes/paperproperties.class.php';

if ($userObject->has_role('Demo')) {
  $demo = true;
} else {
  $demo = false;
}

$sortby = '';
$ordering = '';

$paperID   = check_var('paperID', 'GET', true, false, true);
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate   = check_var('enddate', 'GET', true, false, true);

if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli);
if (!$propertyObj) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$paper = $propertyObj->get_paper_title();
$crypt_name = $propertyObj->get_crypt_name();


$user_results = load_results($propertyObj, $demo, $configObject, $mysqli);
$user_no = count($user_results);
if ($propertyObj->get_pass_mark() == 101) {
  $borderline_method = true;
} else {
  $borderline_method = false;
}

if ($borderline_method) {
  $passmark = getBlinePassmk($user_results, $user_no, $propertyObj);
} elseif ($properties->get_pass_mark() != 102) {
  $passmark = $properties->get_pass_mark();
} else {
  $passmark = 'N/A';
}

set_classification($user_results, $passmark, $user_no, $string);
rating_num_text($user_results, $user_no, $propertyObj, $string);
$user_results = array_csort($user_results, $sortby, $ordering);

header('Pragma: public');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".csv");

$completed_no = 0;
$total_score = 0;

//output table heading
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
