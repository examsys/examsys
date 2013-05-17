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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/class_totals.inc';

$displayDebug = false; //disable debud output in this script as it effects the output

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

        $csv .= $user_results[$i]['student_grade'] . ',"' . $user_results[$i]['module'] . '",' . $user_results[$i]['mark'] . ',' . MathsUtils::formatNumber($user_results[$i]['adj_percent'], $percent_decimals) . '%,';

        if ($user_results[$i]['adj_percent'] < $pass_mark) {
          $csv .= "'" . $string['fail'] . "',";
        } else {
          if ($user_results[$i]['adj_percent'] >= $distinction_mark) {
            $csv .= "'" . $string['distinction'] . "',";
          } else {
            $csv .= "'" . $string['pass'] . "',";
          }
        }
        $csv .= $user_results[$i]['rank'] . ',' . $user_results[$i]['decile'] . ',' . $user_results[$i]['display_started'] . ',' . formatsec($user_results[$i]['duration']) . ',' . $user_results[$i]['ipaddress'] . ',"' . $user_results[$i]['room'] . '"';

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

  $size_msg = ($cohort_size < $display_no) ? $cohort_size . $string['of'] . $display_no : $display_no;
  $csv .= $string['cohortsize'] . ",$size_msg,,,,,,,,,,\n";
  $csv .= $string['failureno'] . "," . $stats['failures'] . ",(" . round($percent_failures) . "% of cohort),,,,,,,,,\n";
  $csv .= $string['passno'] . "," . $stats['passes'] . ",(" . round($percent_passes) . $string['percentofcohort'] . "),,,,,,,,,\n";
  if (isset($ss_hon)) {
    $csv .= $string['distinctionno'] . "," . $stats['honours'] . ",(" . round($percent_honours) . "% of cohort),,,,,,,,,\n";
  }
  $csv .= $string['totalmarks'] . ",$total_marks,,,,,,,,,,\n";
  $csv .= $string['passmark'] . ",$pass_mark%,,,,,,,,,,\n";
  if ($marking == '1') {
    $csv .= $string['randommark'] . "," . number_format($total_random_mark, 2, '.', ',') . ",,,,,,,,,,\n";
  } elseif (substr($marking,0,1) == '2') {
    $csv .= $string['ss'] . "," . round($ss_pass,2) . ",,,,,,,,,,\n";
    $csv .= $string['ssdistinction'] . "," . round($ss_hon,2) . ",,,,,,,,,,\n";
  }
  $csv .= $string['meanmark'] . "," . $stats['mean_mark'] . "," . $stats['mean_percent'] . "%,,,,,,,,,\n";
  $csv .= $string['medianmark'] . "," . $stats['median_mark'] . "," . $stats['median_percent'] . "%,,,,,,,,,\n";
  $csv .= $string['stdevmark'] . "," . number_format($stats['stddev_mark'], 2, '.', ',') . "," . round($stats['stddev_percent'], 1) . "%,,,,,,,,,\n";
  $csv .= $string['maxmark'] . "," . $stats['max_mark'] . "," . number_format($stats['max_percent']) . "%,,,,,,,,,\n";
  $csv .= $string['maxmark'] . "," . $stats['min_mark'] . "," . number_format($stats['min_percent']) . "%,,,,,,,,,\n";
  $csv .= $string['range'] . "," . ($stats['range']) . "," . ($stats['range_percent']) . "%,,,,,,,,,\n";
  $avg_time = ($stats['completed_no'] > 0) ? formatsec(round($stats['total_time'] / $stats['completed_no'],0)) : 'n/a';
  $csv .= $string['averagetime'] . "," . $avg_time . ",,,,,,,,,,\n";
  $csv .= $string['excludedquestions'] . ",$display_excluded,,,,,,,,,,\n";
  $csv .= $string['experimantalquestions'] . ",$display_experimental,,,,,,,,,,\n";
} else {
  $csv .= strip_tags(sprintf($string['noattempts'], nicedate($_GET['startdate']), nicedate($_GET['enddate'])));
}

echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");

$mysqli->close();
?>
