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

function padDecimals($number, $rounding) {
  $number = round($number, $rounding);
  
  $parts = explode('.', $number);
  
  $integer = $parts[0];
  if (isset($parts[1])) {
    $decimals = $parts[1];
  } else {
    $decimals = '';
  }
  
  $dec_len = strlen($decimals);
  
  for ($i=$dec_len; $i<$rounding; $i++) {
    $decimals .= '0';
  }

  return $integer . '.' . $decimals;
}

// Get some properties of the paper.
$result = $mysqli->prepare("SELECT property_id, paper_title, modules.id, properties.moduleID, calendar_year, rubric, marking, calendar_year FROM properties, modules WHERE properties.moduleID=modules.moduleid AND property_id=?");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($property_id, $paper_title, $moduleID, $moduleID_text, $calendar_year, $type, $marking, $calendar_year);
$result->fetch();
$result->close();

// Get questions on the paper
$questions = array();
$result = $mysqli->prepare("SELECT question, leadin, display_method FROM (papers, questions) WHERE papers.question=questions.q_id AND paper=? ORDER BY display_pos");
$result->bind_param('i', $property_id);
$result->execute();
$result->bind_result($questionID, $leadin, $display_method);
while ($result->fetch()) {
  $heading_no = substr_count($display_method, '|');
  $questions[$questionID]['leadin'] = $leadin;
  $questions[$questionID]['scale'] = $display_method;
  $questions[$questionID]['scale_size'] = substr_count($display_method, '|');
}
$result->close();

// Load basic user information
$user_data = array();
$groups = array();
$result = $mysqli->prepare("SELECT users_metadata.userID, value, surname, first_names, title, student_id FROM (users_metadata, users) LEFT JOIN sid ON users.id=sid.userID WHERE users_metadata.userID=users.id AND moduleID=? AND calendar_year=? AND type=? ORDER BY surname, initials");
$result->bind_param('iss', $moduleID, $calendar_year, $type);
$result->execute();
$result->bind_result($userID, $group, $surname, $first_names, $title, $student_id);
while ($result->fetch()) {
  $user_data[$userID]['group'] = $group;
  $user_data[$userID]['surname'] = $surname;
  $user_data[$userID]['first_names'] = $first_names;
  $user_data[$userID]['title'] = $title;
  $user_data[$userID]['student_id'] = $student_id;
  $user_data[$userID]['userID'] = $userID;
  
  $groups[$group][$student_id] = $student_id;
}
$result->close();

// Load results from Log6
$reviewers = array();
$result = $mysqli->prepare("SELECT reviewerID, peerID, q_id, rating FROM log6 WHERE paperID=? ORDER BY started");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($reviewerID, $peerID, $q_id, $rating);
while ($result->fetch()) {
  $user_data[$peerID]['data'][$q_id][$reviewerID] = $rating;
  $reviewers[$reviewerID] = $reviewerID;
}
$result->close();

// Parse the data calculating means
foreach ($user_data as $studentID => $student) {
  $available_marks = 0;
  $student_marks = 0;
  if (isset($student['data'])) {
    foreach ($student['data'] as $questionID => $question_data) {
      $review_no = 0;
      $review_total = 0;   
      $sub_student = 0;
      $sub_available = 0;
      foreach ($question_data as $individual_question) {
        if ($individual_question != '') {
          $review_no++;
          $review_total += $individual_question;
          $available_marks += $questions[$questionID]['scale_size'];
          $sub_available += $questions[$questionID]['scale_size'];
          $student_marks += $individual_question;
          $sub_student += $individual_question;
        }
      }
      $user_data[$studentID]['review_no'] = $review_no;
      if ($review_no > 0) {
        $user_data[$studentID]['means'][$questionID] = $review_total / $review_no;
        $user_data[$studentID]['percent'][$questionID] = ($sub_student / $sub_available) * 100;
      } else {
        $user_data[$studentID]['means'][$questionID] = 0;
        $user_data[$studentID]['percent'][$questionID] = 0;
      }
      $user_data[$studentID]['review_total'][$questionID] = $student_marks;
      $user_data[$studentID]['total_percent'][$questionID] = ($student_marks / $available_marks) * 100;
    }
  }
}
