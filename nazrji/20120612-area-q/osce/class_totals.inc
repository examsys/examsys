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

  if (strpos($userroles,'Demo') !== false) {
    $demo = true;
  } else {
    $demo = false;
  }
  require_once '../include/demo_replace.inc';

  function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
    $sortarr = array();
    foreach ($marray as $row) {
      $sortarr[] = $row[$column];
    }
    $sortarr = array_map('strtolower',$sortarr);
    $sort_method = SORT_STRING;
    if ($column == 'numeric_score') $sort_method = SORT_NUMERIC;
    if ($sort_order == 'asc') {
      array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
    } else {
      array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
    }
    return $marray;
  }
  $paperID = '';
  $sortby = '';
  $ordering = '';
  if (isset($_GET['paperID'])) $paperID = $_GET['paperID'];
  if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
  if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

  // Get the module ID and calendar year of the OSCE station.
  $result = $mysqli->prepare("SELECT paper_title, moduleID, calendar_year, marking FROM properties WHERE property_id=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper, $moduleID, $calendar_year, $marking);
  $result->fetch();
  $result->close();
  
  // Get the students who are enrolled on the module/session.
  $user_no = 0;
  if (!isset($_GET['absent']) or $_GET['absent'] == 0) {
    $result = $mysqli->prepare("SELECT DISTINCT students.id, students.gender, log4_overall.student_grade, log4_overall.year, students.surname, students.initials, students.first_names, students.title, student_id, started, DATE_FORMAT(started,'$cfg_long_date_time') AS display_started, overall_rating, numeric_score, examiners.title, examiners.surname, examiners.initials FROM (users AS students, users AS examiners, sid, log4_overall) WHERE students.id=log4_overall.userID AND examiners.id=log4_overall.examinerID AND started>=? AND started<=? AND q_paper=? AND students.id=sid.userID ORDER BY students.surname, students.initials");
    $result->bind_param('ssi', $_GET['startdate'], $_GET['enddate'], $_GET['paperID']);
  } else {
    $result = $mysqli->prepare("SELECT students.id, students.gender, log4_overall.student_grade, log4_overall.year, students.surname, students.initials, students.first_names, students.title, student_id, started, DATE_FORMAT(started,'$cfg_long_date_time') AS display_started, overall_rating, numeric_score, examiners.title, examiners.surname, examiners.initials FROM (student_modules, users AS students, sid) LEFT JOIN log4_overall ON students.id=log4_overall.userID AND q_paper=? LEFT JOIN users AS examiners ON log4_overall.examinerID=examiners.id WHERE student_modules.userID=students.id AND students.id=sid.userID AND moduleid=? AND calendar_year=? ORDER BY students.surname, students.initials");
    $result->bind_param('iss', $_GET['paperID'], $moduleID, $calendar_year);
  }
  $result->execute();
  $result->bind_result($tmp_userID, $gender, $grade, $year, $surname, $initials, $first_names, $title, $student_id, $started, $display_started, $overall_rating, $numeric_score, $examiner_title, $examiner_surname, $examiner_initials);
  $user_results = array();
  while ($result->fetch()) {
    $user_results[$user_no]['name'] = $surname . $initials;
    $user_results[$user_no]['display_name'] = $title . ' ' . $surname . ', <span style="color:#808080">' . $first_names . '</span>';
    $user_results[$user_no]['classification'] = $overall_rating;
    $user_results[$user_no]['numeric_score'] = $numeric_score;
    $user_results[$user_no]['grade'] = $grade;
    $user_results[$user_no]['started'] = $started;
    $user_results[$user_no]['tmp_userID'] = $tmp_userID;
    $user_results[$user_no]['student_grade'] = $grade;
    $user_results[$user_no]['year'] = $year;
    $user_results[$user_no]['display_started'] = $display_started;
    $user_results[$user_no]['title'] = $title;  
    $user_results[$user_no]['surname'] = demo_replace($surname,$demo);
    $user_results[$user_no]['initials'] = demo_replace($initials,$demo);
    $user_results[$user_no]['first_names'] = demo_replace($first_names,$demo);
    $user_results[$user_no]['student_id'] = demo_replace_number($student_id,$demo);
    $user_results[$user_no]['gender'] = $gender;
    $user_results[$user_no]['examiner'] = $examiner_title . ' ' . $examiner_initials . ' ' . $examiner_surname;
    $user_results[$user_no]['visible'] = 1;    // Default to visible unless switched off below.

    $user_no++;
  }
  $result->close();
  
  $user_results = array_csort($user_results,$sortby,$ordering);

  switch ($marking) {
    case '1':
      $labels = array(1=>'Fail',2=>'Pass','ERROR'=>'ERROR');
      break;
    case '3':
      $labels = array(1=>'Clear Fail',2=>'Borderline',3=>'Clear Pass','ERROR'=>'ERROR');
      break;
    case '4':
      $labels = array(1=>'Fail',2=>'Borderline fail',3=>'Borderline pass',4=>'Pass',5=>'Good pass','ERROR'=>'ERROR');
      break;
    case '5':
      $labels = array(1=>'Unsatisfactory',2=>'Competent','ERROR'=>'ERROR');
      break;
    case '6':
      $labels = array(1=>'Clear FAIL',2=>'BORDERLINE',3=>'Clear PASS',4=>'Honours PASS','ERROR'=>'ERROR');
      break;
  }
  
  $completed_no = 0;
  $total_score = 0;
  $classifications = array(''=>'',1=>0,2=>0,3=>0,4=>0,5=>0,'ERROR'=>0);
  
  for ($i=0; $i<$user_no; $i++) {
    if ($user_results[$i]['started'] != '') {   // No attendance
      $classifications[$user_results[$i]['classification']]++;
      $total_score += $user_results[$i]['numeric_score'];
      $completed_no++;
    }
  }

?>