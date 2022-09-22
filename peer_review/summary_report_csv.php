<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Output a summary of peer review data in CSV format.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require_once '../include/errors.php';
$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);
// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
require 'summary_report.inc';
header('Pragma: public');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="data.csv"');
// Write out headings
echo 'Title,Surname,First Names,Student ID,Reviewed,Group';
$heading_no = count($questions);
if ($review_type == 1) {
    echo ',Reviews';
}
for (
    $i = 1; $i <= $heading_no; $i++
) {
    echo ',Q' . $i;
}
if ($review_type == 1) {
    echo ',Overall';
}
echo "\n";
foreach ($user_data as $student_userID => $student) {
    if ($student_userID > 0) {
        $mean_total = 0;
        echo $student['title'] . ',' . $student['surname'] . ',' . $student['first_names'];
        echo ',' . $student['student_id'];
        if (isset($reviewers[$student['userID']])) {
            echo ',Complete';
        } else {
            echo ',Missing';
        }
        echo ',' . $student['group'];
        if ($review_type == 1) {
            if (isset($student['review_no'])) {
                echo ',' . $student['review_no'];
            } else {
                echo ',0';
            }
        }
        if ($review_type == 1) {
            foreach ($questions as $questionID => $tmp_data) {
                if (isset($student['means'][$questionID])) {
                    echo ',' . padDecimals($student['means'][$questionID], 2);
                    $mean_total += $student['means'][$questionID];
                } else {
                    echo ',';
                }
            }
            echo ',' . padDecimals($mean_total / $heading_no, 2) . "\n";
        } else {
            foreach ($questions as $questionID => $tmp_data) {
                echo ',';
                if (isset($user_data[0]['data'][$questionID][$student_userID])) {
                    echo $user_data[0]['data'][$questionID][$student_userID];
                    $mean_total += $user_data[0]['data'][$questionID][$student_userID];
                }
            }
            echo "\n";
        }
    }
}
