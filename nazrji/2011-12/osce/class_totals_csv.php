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
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require 'class_totals.inc';

  header("Content-type: application/vnd.ms-excel");
  header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".csv");

  echo "Title\tSurname\tFirst Names\tStudent ID\tCourse\tTotal\tClassification\tStart Date\tExaminer\n";
  for ($i=0; $i<$user_no; $i++) {
    echo $user_results[$i]['title'] . "\t" . $user_results[$i]['surname'] . "\t" . $user_results[$i]['first_names'] . "\t";
    if ($user_results[$i]['student_id'] == '') {
      echo "Unknown\t";
    } else {
      echo $user_results[$i]['student_id'] . "\t";
    }
    if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
      echo "\t\tNo Attendance\t\t\n";
    } else {
      echo $user_results[$i]['grade'] . "\t" . $user_results[$i]['numeric_score'] . "\t" . $labels[$user_results[$i]['classification']] . "\t" . $user_results[$i]['display_started'] . "\t" . $user_results[$i]['examiner'] . "\n";
    }
  }
  echo "\t\t\t\t\t\t\t\t\t\n";

  echo "Cohort Size\t$user_no\t\t\t\t\t\t\t\t\n";

  $mysqli->close();
?>
