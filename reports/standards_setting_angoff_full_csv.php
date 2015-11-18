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
* @author Richard Whitefoot (UEA)
* @version 1.0
* @copyright Copyright (c) 2015
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/paperproperties.class.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);

$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

// Get some properties of the paper.
$paper_title    = $propertyObj->get_paper_title();

// Get any questions to exclude.
$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();
$excludedSql = "";

if(!empty($exclusions->excluded)) {

  $excluded = array();

  foreach($exclusions->excluded as $key => $value) {    
    $excluded[] = $key;
  } 

  $excludedSql .= implode(",",$excluded);
  $excludedSql = " AND q.q_id NOT IN (" . $excludedSql . ")";
}

$stmt = $mysqli->prepare("SELECT ssq.rating, ss.setterID, ss.method, u.title, u.initials, u.surname, p.display_pos, q.q_id, q.theme, q.q_type, ss.std_set, ss.group_review FROM papers p INNER JOIN questions q ON p.question=q.q_id LEFT JOIN std_set_questions ssq ON p.question=ssq.questionID LEFT JOIN std_set ss ON ssq.std_setID=ss.id LEFT JOIN users u ON ss.setterID=u.id WHERE p.paper = ? " . $excludedSql . " ORDER BY ss.std_set,ss.setterID");

$csv = '';

header('Pragma: public');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper_title . "_" . $paperID) . "_standards_setting_full.csv");

if($stmt) {
  $stmt->bind_param('s', $paperID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($rating, $setter_id, $method, $title, $initials, $surname, $display_pos, $q_id, $theme, $q_type, $date, $group_review);

  $csv .= "Date,Standard Setter,Method,Question Number,Theme,Question Type,Rating\n";

  while($stmt->fetch()) {

    if(($rating == "") || (substr($rating, -1) == ",")) {
      $rating = "Incomplete";
    }

    if($group_review == "No") {
      $standard_setter = $title . " " . $initials . " " . $surname;
    } else {
      $standard_setter = "Group review";
    }

    $csv .= $date . "," . addslashes($standard_setter) . "," . $method . "," . $display_pos . "," . addslashes($theme) . "," . $string[$q_type] . "," . $rating . "\n";

  }

  $stmt->close();
} else {
  $csv .= strip_tags($string['nostandardsset']);
}

echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");

$mysqli->close();

?>