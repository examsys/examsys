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
require_once '../include/errors.php';
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

$stmt = $mysqli->prepare("SELECT ssq.rating, ss.setterID, ss.method, u.title, u.initials, u.surname, p.display_pos, q.q_id, q.theme, 
                          q.q_type, ss.std_set, ss.group_review, ss.id, q.score_method, 
                          (SELECT count(*) FROM options WHERE o_id=q.q_id) AS option_no,
                          (SELECT count(*) FROM options WHERE o_id=q.q_id AND correct='y') AS mrq_correct_per_option,
                          (SELECT count(*) FROM options WHERE o_id=q.q_id AND correct!='') AS rank_correct_per_option,
                          ssq.id
                          FROM papers p INNER JOIN questions q ON p.question=q.q_id 
                          LEFT JOIN std_set_questions ssq ON p.question=ssq.questionID 
                          LEFT JOIN std_set ss ON ssq.std_setID=ss.id 
                          LEFT JOIN users u ON ss.setterID=u.id AND ss.paperID = ? 
                          WHERE p.paper = ? AND q.q_type != 'info' AND u.surname IS NOT NULL " . $excludedSql . " 
                          ORDER BY ss.setterID, ss.id, p.display_pos");

$csv = '';

header('Pragma: public');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper_title . "_" . $paperID) . "_standards_setting_full.csv");

if($stmt) {
  $stmt->bind_param('ss', $paperID, $paperID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($rating, $setter_id, $method, $title, $initials, $surname, $display_pos, $q_id, $theme, $q_type, $date, $group_review, $ss_id, $score_method, $option_no, $mrq_correct_per_option, $rank_correct_per_option, $ssq_id);


  $csvHeader = $string['date'] . "," . $string['standardsetter'];

  $questions = array();
  $q_id_ssq_id_array = array();
  $prev_ss_id = 0;
  $question_number = 0;
  $ratingColumnCount = array();

  while($stmt->fetch()) {

    $ratingColumns = explode(",", $rating);
    $notNullRatingColumns = array_filter($ratingColumns);

    // MRQ and RANK questions have valid ratings with null column values so need a special case
    if($q_type == "mrq" or $q_type == "rank") {

      if ($q_type == "mrq" ) {
        $correct_per_option = $mrq_correct_per_option;
      } else {
        $correct_per_option = $rank_correct_per_option;
      }
      if ($score_method == 'Mark per Question') {
        $mrq_correct = 1;
      } elseif ($score_method == 'Bonus Mark') {
        $mrq_correct = $correct_per_option + 1;
      } else {
        $mrq_correct = $correct_per_option;
      }
      if($mrq_correct != count($notNullRatingColumns)) {
        $rating = $string['incomplete'] . "[COLUMNS-q_id" . $q_id . "]";
      }
    } elseif($q_type == "sct" or $q_type == "textbox") {
        $rating = $string['noncompatible'] . "[COLUMNS-q_id" . $q_id . "]";
    } else {

      // Clearly mark incomplete ratings
      if(($rating == "") || (substr($rating, -1) == ",") || (substr($rating, 0, 1) == ",") || (preg_match("/,,/", $rating))) {
        $rating = $string['incomplete'] . "[COLUMNS-q_id" . $q_id . "]";
      }

    }

    // Calculate correct number of spacer columns for incomplete ratings 
    $currentColumnCount = count($ratingColumns);   
    if(!isset($ratingColumnCount[$q_id])) {
      $ratingColumnCount[$q_id] = 0;
    }
    if($currentColumnCount > $ratingColumnCount[$q_id]) {
      $ratingColumnCount[$q_id] = $currentColumnCount;
    }

    if($group_review == "No") {
      $standard_setter = $title . " " . $initials . " " . $surname;
    } else {
      $standard_setter = $string['groupreview'];
    }

    // Check for new row
    if($ss_id != $prev_ss_id) {
      // Remove last comma
      $csv = rtrim($csv, ",");
      $csv .= "\n";
      $csv .= $date . "," . preg_replace("/,/", " ", addslashes($standard_setter)) . ",";
    } 

    // Add to CSV header
    if(!in_array($display_pos, $questions)) {
        
        $question_number++;

        $csvHeader .= "," . $question_number . " (" . preg_replace("/,/", " ", addslashes($method)) . ";";
        
        if($theme) {
          $csvHeader .= preg_replace("/,/", " ", addslashes($theme)) . ";";
        } 
        
        $csvHeader .= preg_replace("/,/", " ", addslashes($string[$q_type])) . ")[COLUMNS-q_id" . $q_id . "]";
    }

    // Add ratings markers
    $csv .= "[COLSTART_" . $q_id . "_" . $ssq_id . "]" . $rating . "[COLEND_" . $q_id . "_" . $ssq_id . "],";

    $questions[] = $display_pos;
    $prev_ss_id = $ss_id;
    $q_id_ssq_id_array[$q_id][] = $ssq_id;
  }

  $csv = $csvHeader . $csv;

  // Remove last comma
  $csv = rtrim($csv, ",");

  $additionalCommas = array();
  // Replace placeholders with correct number of columns
  foreach($ratingColumnCount as $key => $value) {    
    
    $additionalCommasCurrent = "";
    // Start count at 1 as there is already a single comma between fields
    for($i=1;$i<$ratingColumnCount[$key];$i++) {
      $additionalCommasCurrent .= ",";
    }
    $additionalCommas[$key] = $additionalCommasCurrent;

    $csv = preg_replace("/\[COLUMNS-q_id" . $key . "\]/", $additionalCommas[$key], $csv);

  }

  // Final check that rating columns match maximum column count
  foreach($q_id_ssq_id_array as $q_id => $ssq_ids) {

    foreach($ssq_ids as $ssq_id) {
        
      $pattern = "/(?<=\[COLSTART_" . $q_id . "_" . $ssq_id . "\])(.*?)(?=\[COLEND_" . $q_id . "_" . $ssq_id . "\])/";
      preg_match($pattern, $csv, $ratingColumn);

      if(!empty($ratingColumn)) {
        if(!isset($ratingColumnCount[$q_id])) {
          $ratingColumnCount[$q_id] = null;
        }
        if($ratingColumnCount[$q_id] !== (count(explode(",", $ratingColumn[0])))) {
          // Something has gone wrong (eg marks available have been changed) so mark column as incomplete
          $csv = preg_replace($pattern, $string['incomplete'] . $additionalCommas[$q_id], $csv);
        }     
      }
   }    
  }

  // Remove ratings markers
  $removeStart = "/(\[COLSTART_)(\d+)_(\d+)(\])/";
  $csv = preg_replace($removeStart, "", $csv);

  $removeEnd = "/(\[COLEND_)(\d+)_(\d+)(\])/";
  $csv = preg_replace($removeEnd, "", $csv);

  $stmt->close();

} else {
  $csv .= strip_tags($string['nostandardsset']);
}

echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");

$mysqli->close();

?>