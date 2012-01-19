<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.inc';

check_var('paperID', 'GET', true, false);

require 'summary_report.inc';

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=data.csv");

// write out headings
echo "Title,Surname,First Names,Student ID,Reviewed,Group,Reviews";
for ($i=1; $i<=$heading_no; $i++) {
  echo ',Q' . $i;
}
echo ",Overall\n";


foreach ($user_data as $student_userID => $student) {
  $mean_total = 0;
  echo $student['title'] . ',' . $student['surname'] . ',' . $student['first_names'];
  echo ',' . $student['student_id'];
  if (isset($reviewers[$student['userID']])) {
    echo ',Complete';
  } else {
    echo ',Missing';
  }
  echo ',' . $student['group'];
  if (isset($student['review_no'])) {
    echo ',' . $student['review_no'];
  } else {
    echo ',0';
  }
  foreach ($questions as $questionID => $tmp_data) {
    if (isset($student['means'][$questionID])) {
      echo ',' . padDecimals($student['means'][$questionID],2);
      $mean_total += $student['means'][$questionID];
    } else {
      echo ',';
    }
  }
  echo "," . padDecimals($mean_total / $heading_no, 2) . "\n";
}
?>
