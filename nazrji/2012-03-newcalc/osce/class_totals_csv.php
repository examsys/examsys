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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require 'class_totals.inc';

  header("Content-type: application/vnd.ms-excel");
  header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".csv");

  echo "Title,Surname,First Names,Student ID,Course,Total,Classification,Start Date,Examiner\n";
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
      echo $user_results[$i]['grade'] . "," . $user_results[$i]['numeric_score'] . "," . $labels[$user_results[$i]['classification']] . "," . $user_results[$i]['display_started'] . ",\"" . $user_results[$i]['examiner'] . "\"\n";
    }
  }
  echo ",,,,,,,,,\n";

  echo "Cohort Size,$user_no,,,,,,,,\n";

  $mysqli->close();
?>
