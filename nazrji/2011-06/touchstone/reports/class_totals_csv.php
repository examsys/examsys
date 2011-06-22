<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Class total report in CSV format.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

// TODO: if no students have completed the question

  require '../include/staff_auth.inc';
  require '../include/class_totals.inc';

  header("Content-type: application/vnd.ms-excel");
  header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".csv");

  if($cohort_size > 0) {
    if ($marking == '0') {
      $marking_label = '%';
    } else {
      $marking_label = 'Adjusted %';
    }
  
    $total_time = 0;
    echo "Title,Surname$user_no,First Names,Student ID,Course,Mark,$marking_label,Classification,Start Date,Duration,IP Address,Room\n";
    for ($i=0; $i<$user_no; $i++) {
      $total_time += $user_results[$i]['duration'];
      echo $user_results[$i]['title'] . "," . $user_results[$i]['surname'] . "," . $user_results[$i]['first_names'] . ",";
      if ($user_results[$i]['student_id'] == '') {
        echo "Unknown,";
      } else {
        echo $user_results[$i]['student_id'] . ",";
      }
      if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
        echo $user_results[$i]['module'] . ",,,,No Attendance,,,\n";
      } else {
  	  	// If room is unknown then it will contain HTML that we want to discard
  	  	$user_results[$i]['room'] = (strpos($user_results[$i]['room'], 'unknown') !== false) ? 'unknown' : $user_results[$i]['room'];
  
      	echo $user_results[$i]['module'] . "," . $user_results[$i]['mark'] . "," . $user_results[$i]['adj_percent'] . "%,";
        
        
        if ($user_results[$i]['adj_percent'] < $pass_mark) {
          echo "Fail,";
        } else {
          if (isset($ss_hon) and $user_results[$i]['percent'] >= $ss_hon) {
            echo "Distinction,";
          } else {
            echo "Pass,";
          }
        }
        echo $user_results[$i]['display_started'] . "," . formatsec($user_results[$i]['duration']) . "," . $user_results[$i]['ipaddress'] . "," . $user_results[$i]['room'] . "\n";
      }
    }
    echo ",,,,,,,,,,,\n";
  
    echo "Cohort Size,$cohort_size,,,,,,,,,,\n";
    echo "# Failures,$failures,(" . round(($failures / $cohort_size) * 100) . "% of cohort),,,,,,,,,\n";
    if (isset($ss_hon)) {
      echo "# Distinction,$honours,(" . round(($honours / $cohort_size) * 100) . "% of cohort),,,,,,,,,\n";
    }
    echo "Total available marks,$total_marks,,,,,,,,,,\n";
    echo "Pass Mark,$pass_mark%,,,,,,,,,,\n";
    if ($marking == '1') {
      echo "Random Mark," . number_format($total_random_mark, 2, '.', ',') . ",,,,,,,,,,\n";
    } elseif (substr($marking,0,1) == '2') {
      echo "SS," . round($ss_pass,2) . ",,,,,,,,,,\n";
      echo "SS Distinction," . round($ss_hon,2) . ",,,,,,,,,,\n";
    }
    echo "Mean Mark,$mean_mark,$mean_percent%,,,,,,,,,\n";
    echo "Median Mark,$median_mark,$median_percent%,,,,,,,,,\n";
    echo "StDev Mark," . number_format($stddev_mark, 2, '.', ',') . "," . round($stddev_percent,1) . "%,,,,,,,,,\n";
    echo "Max Mark,$max_mark," . number_format($max_percent) . "%,,,,,,,,,\n";
    echo "Min Mark,$min_mark," . number_format($min_percent) . "%,,,,,,,,,\n";
    echo "Range," . ($max_mark - $min_mark) . "," . ($max_percent - $min_percent) . "%,,,,,,,,,\n";
    echo "Top 10%,$top_10%,,,,,,,,,,\n";
    echo "Top 15%,$top_15%,,,,,,,,,,\n";
    echo "Top 20%,$top_20%,,,,,,,,,,\n";
    echo "Top 25%,$top_25%,,,,,,,,,,\n";
    echo "Bottom 10%,$bottom_10%,,,,,,,,,,\n";
    $avg_time = ($completed_no > 0) ? formatsec(round($total_time / $completed_no,0)) : 'n/a';
    echo "Average Time," . $avg_time . ",,,,,,,,,,\n";
    echo "Excluded Questions,$display_excluded,,,,,,,,,,\n";
    echo "Experimental Questions,$display_experimental,,,,,,,,,,\n";
    if(count($warnings['deleted_qns']) > 0) {
    	echo "Warnings,Answers found for questions that no longer appear on the paper (IDs ";
      for($i = 0; $i < count($warnings['deleted_qns']); $i++) {
       	echo $warnings['deleted_qns'][$i];
        if($i < count($warnings['deleted_qns']) - 1) {
        	echo ", ";
        }
      }
    	echo "),,,,,,,,,,\n";
    }
  } else {
    echo "This paper has not been attempted by anyone.";
  }
  $mysqli->close();
?>
