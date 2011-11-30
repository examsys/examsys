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
      $marking_key = 'percent';
    } else {
      $marking_label = 'adjusted%';
      $marking_key = 'adj_percent';
    }
  
    $total_time = 0;
    
    //output table heading
    $table_order = array('title'=>'title', 'surname'=>'Surname' ,'firstnames'=>'First_Names','studentid'=>'student_id','course'=>'student_grade','mark'=>'mark',$marking_label=>$marking_key,'classification'=>'mark','starttime'=>'started','duration'=>'duration','ipaddress'=>'ipaddress');
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
      echo $string[$display] . ',';
    }
    echo "\n";
    
    for ($i=0; $i<$user_no; $i++) {
      if ($user_results[$i]['visible'] == 1) {
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
            echo $string['fail'] . ',';
          } else {
            if (isset($ss_hon) and $user_results[$i]['percent'] >= $ss_hon) {
              echo $string['distinction'] . ',';
            } else {
              echo $string['pass'] . ',';
            }
          }
          echo $user_results[$i]['display_started'] . "," . formatsec($user_results[$i]['duration']) . "," . $user_results[$i]['ipaddress'] . "," . $user_results[$i]['room'];
          
          // Display any associated metadata
          if (count($metadata_cols) > 0) {
            foreach ( $metadata_cols as $type) {
              echo ',' . $user_results[$i][$type];
            }
          }
          echo "\n";
        }
      }
    }
    echo ",,,,,,,,,,,\n";
  
    echo $string['cohortsize'] . ",$display_no,,,,,,,,,,\n";
    echo $string['failureno'] . ",$failures,(" . round(($failures / $display_no) * 100) . "% of cohort),,,,,,,,,\n";
    if (isset($ss_hon)) {
      echo $string['distinctionno'] . ",$honours,(" . round(($honours / $display_no) * 100) . "% of cohort),,,,,,,,,\n";
    }
    echo $string['totalmarks'] . ",$total_marks,,,,,,,,,,\n";
    echo $string['passmark'] . ",$pass_mark%,,,,,,,,,,\n";
    if ($marking == '1') {
      echo $string['randommark'] . "," . number_format($total_random_mark, 2, '.', ',') . ",,,,,,,,,,\n";
    } elseif (substr($marking,0,1) == '2') {
      echo $string['ss'] . "," . round($ss_pass,2) . ",,,,,,,,,,\n";
      echo $string['ssdistinction'] . "," . round($ss_hon,2) . ",,,,,,,,,,\n";
    }
    echo $string['meanmark'] . ",$mean_mark,$mean_percent%,,,,,,,,,\n";
    echo $string['medianmark'] . ",$median_mark,$median_percent%,,,,,,,,,\n";
    echo $string['stdevmark'] . "," . number_format($stddev_mark, 2, '.', ',') . "," . round($stddev_percent,1) . "%,,,,,,,,,\n";
    echo $string['maxmark'] . ",$max_mark," . number_format($max_percent) . "%,,,,,,,,,\n";
    echo $string['maxmark'] . ",$min_mark," . number_format($min_percent) . "%,,,,,,,,,\n";
    echo $string['range'] . "," . ($max_mark - $min_mark) . "," . ($max_percent - $min_percent) . "%,,,,,,,,,\n";
    echo $string['top10'] . ",$top_10%,,,,,,,,,,\n";
    echo $string['top15'] . ",$top_15%,,,,,,,,,,\n";
    echo $string['top20'] . ",$top_20%,,,,,,,,,,\n";
    echo $string['top25'] . ",$top_25%,,,,,,,,,,\n";
    echo $string['bottom10'] . ",$bottom_10%,,,,,,,,,,\n";
    $avg_time = ($completed_no > 0) ? formatsec(round($total_time / $completed_no,0)) : 'n/a';
    echo $string['averagetime'] . "," . $avg_time . ",,,,,,,,,,\n";
    echo $string['excludedquestions'] . ",$display_excluded,,,,,,,,,,\n";
    echo $string['experimantalquestions'] . ",$display_experimental,,,,,,,,,,\n";
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
    echo $string['noattempts'];
  }
  $mysqli->close();
?>
