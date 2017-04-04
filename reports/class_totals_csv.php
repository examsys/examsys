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
* Class total report in CSV format.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$displayDebug = false; //disable debud output in this script as it effects the output

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($_GET['paperID'], $mysqli, $string);

$paper            = $propertyObj->get_paper_title();
$marking          = $propertyObj->get_marking();
$pass_mark        = $propertyObj->get_pass_mark();
$distinction_mark = $propertyObj->get_distinction_mark();
$paper_type       = $propertyObj->get_paper_type();

$percent      = (isset($_GET['percent'])) ? $_GET['percent'] : 100;
$ordering     = (isset($_GET['ordering'])) ? $_GET['ordering'] : 'asc';
$absent       = (isset($_GET['absent'])) ? $_GET['absent'] : 0;
$sortby       = (isset($_GET['sortby'])) ? $_GET['sortby'] : 'name';
$studentsonly = (isset($_GET['studentsonly'])) ? $_GET['studentsonly'] : 1;
$repcourse    = (isset($_GET['repcourse'])) ? $_GET['repcourse'] : '%';
$repmodule    = (isset($_GET['repmodule'])) ? $_GET['repmodule'] : '';

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli, $string);
$report->compile_report(false);

$user_results = $report->get_user_results();
$paper_buffer = $report->get_paper_buffer();
$cohort_size  = $report->get_cohort_size();
$stats        = $report->get_stats();
$ss_pass      = $report->get_ss_pass();
$ss_hon       = $report->get_ss_hon();
$question_no  = $report->get_question_no();
$log_late     = $report->get_log_late();

$user_no = count($user_results);

header('Pragma: public');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . "_CT.csv");

$csv = '';
$percent_decimals = $configObject->get('percent_decimals');

if ($cohort_size > 0) {
  if ($marking == '0') {
    $marking_label = '%';
    $marking_key = 'percent';
  } else {
    $marking_label = 'adjusted%';
    $marking_key = 'adj_percent';
  }

  $total_time = 0;

  //output table heading
  $table_order = array($string['title']=>'title', $string['surname']=>'Surname', $string['firstnames']=>'First_Names', $string['studentid']=>'student_id', $string['course']=>'student_grade', $string['module']=>'module', $string['mark']=>'mark', $marking_label=>$marking_key, $string['classification']=>'mark', $string['rank']=>'rank', $string['decile']=>'decile', $string['starttime']=>'started', $string['duration']=>'duration', $string['ipaddress']=>'ipaddress');
  $table_order['room'] = 'room';
  $metadata_cols = array();
  if (isset($user_results[0])){
    foreach ($user_results[0] as $key => $val) {
      if (strrpos($key,'meta_') !== false) {
        $key_display = ucfirst(str_replace('meta_','',$key));
        $table_order[$key_display] = $key;
        $metadata_cols[$key] = $key;
      }
    }
  }

  foreach ($table_order as $display => $key) {
    $csv .= $display . ',';
  }
  $csv .= "\n";

  for ($i=0; $i<$user_no; $i++) {
    if ($user_results[$i]['visible'] == 1) {
      $total_time += $user_results[$i]['duration'];
      $csv .= '"' . $user_results[$i]['title'] . '","' . $user_results[$i]['surname'] . '","' . $user_results[$i]['first_names'] . '",';
      if ($user_results[$i]['student_id'] == '') {
        $csv .= 'Unknown,';
      } else {
        $csv .= $user_results[$i]['student_id'] . ',';
      }
      if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
        $csv .= $user_results[$i]['student_grade'] . "," . $user_results[$i]['module'] . ",,,,No Attendance,,,,\n";
      } else {
        // If room is unknown then it will contain HTML that we want to discard
        $user_results[$i]['room'] = (strpos($user_results[$i]['room'], 'unknown') !== false) ? 'unknown' : $user_results[$i]['room'];

        $csv .= $user_results[$i]['student_grade'] . ',"' . $user_results[$i]['module'] . '",' . $user_results[$i]['mark'] . ',' . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . '%,';

        if (round($user_results[$i]['percent'], $percent_decimals) < $pass_mark) {
          $csv .= '"' . $string['fail'] . '",';
        } else {
          if (round($user_results[$i]['percent'], $percent_decimals) >= $distinction_mark) {
            $csv .= '"' . $string['distinction'] . '",';
          } else {
            $csv .= '"' . $string['pass'] . '",';
          }
        }
        $csv .= $user_results[$i]['rank'] . ',' . $user_results[$i]['decile'] . ',' . $user_results[$i]['display_started'] . ',' . $report->formatsec($user_results[$i]['duration']) . ',' . $user_results[$i]['ipaddress'] . ',"' . $user_results[$i]['room'] . '"';

        // Display any associated metadata
        if (count($metadata_cols) > 0) {
          foreach ($metadata_cols as $type) {
            $csv .= ',' . $user_results[$i][$type];
          }
        }
        $csv .= "\n";
      }
    }
  }
  $csv .= ",,,,,,,,,,,\n";

  if ($cohort_size > 0) {
    $percent_failures = round(($stats['failures'] / $cohort_size) * 100);
    $percent_passes = round(($stats['passes'] / $cohort_size) * 100);
    $percent_honours = round(($stats['honours'] / $cohort_size) * 100);
  } else {
    $percent_failures = 0;
    $percent_passes = 0;
    $percent_honours = 0;
  }

  $size_msg = ($cohort_size < $user_no) ? $cohort_size . $string['of'] . $user_no : $user_no;

  $csv .= $string['cohortsize'] . ",$size_msg,,,,,,,,,,\n";
  $csv .= $string['failureno'] . "," . $stats['failures'] . ",(" . $percent_failures . "% of cohort),,,,,,,,,\n";
  $csv .= $string['passno'] . "," . $stats['passes'] . ",(" . $percent_passes . $string['percentofcohort'] . "),,,,,,,,,\n";
  if (isset($ss_hon)) {
    $csv .= $string['distinctionno'] . "," . $stats['honours'] . ",(" . $percent_honours . "% of cohort),,,,,,,,,\n";
  }
  $csv .= $string['totalmarks'] . "," . $report->get_total_marks() . ",,,,,,,,,,\n";
  $csv .= $string['passmark'] . ",$pass_mark%,,,,,,,,,,\n";
  if ($marking == '1') {
    $csv .= $string['randommark'] . "," . number_format($report->get_total_random_mark(), 2, '.', ',') . ",,,,,,,,,,\n";
  } elseif (substr($marking,0,1) == '2') {
    $csv .= $string['ss'] . "," . round($report->get_ss_pass(), 2) . ",,,,,,,,,,\n";
    $csv .= $string['ssdistinction'] . "," . MathsUtils::formatNumber($report->get_ss_hon(), 2) . ",,,,,,,,,,\n";
  }
  $csv .= $string['meanmark'] . "," . round($stats['mean_mark'], 1) . "," . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%,,,,,,,,,\n";
  $csv .= $string['medianmark'] . "," . round($stats['median_mark'], 1) . "," . MathsUtils::formatNumber($stats['median_percent'], 1) . "%,,,,,,,,,\n";
  $csv .= $string['stdevmark'] . "," . number_format($stats['stddev_mark'], 2, '.', ',') . "," . MathsUtils::formatNumber($stats['stddev_percent'], 2) . "%,,,,,,,,,\n";
  $csv .= $string['maxmark'] . "," . $stats['max_mark'] . "," . number_format($stats['max_percent']) . "%,,,,,,,,,\n";
  $csv .= $string['maxmark'] . "," . $stats['min_mark'] . "," . number_format($stats['min_percent']) . "%,,,,,,,,,\n";
  $csv .= $string['range'] . "," . $stats['range'] . "," . number_format($stats['range_percent']) . "%,,,,,,,,,\n";
  $avg_time = ($stats['completed_no'] > 0) ? $report->formatsec(round($stats['total_time'] / $stats['completed_no'],0)) : 'n/a';
  $csv .= $string['averagetime'] . "," . $avg_time . ",,,,,,,,,,\n";
  $csv .= $string['excludedquestions'] . "," . $report->get_display_excluded() . ",,,,,,,,,,\n";
  $csv .= $string['skippedquestions'] . "," . $report->get_display_experimental() . ",,,,,,,,,,\n";
} else {
  $csv .= strip_tags(sprintf($string['noattempts'], $report->nicedate($startdate), $report->nicedate($enddate)));
}

echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");

$mysqli->close();
?>
