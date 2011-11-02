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
* Displays summative exams and OSCEs in ical format
*
* @author Anthony Brown 
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  //require '../include/staff_auth.inc';
  //require '../include/sidebar_menu.inc';
  $root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/';
  require_once $root . 'config/config.inc';
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
  require '../tools/iCalcreator/iCalcreator.class.php';
  
  if (isset($_GET['calyear'])) {
    $current_year = $_GET['calyear'];
  } else {
    $current_year = date("Y");
  }

  function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
    foreach ($marray as $row) {
      $sortarr[] = $row[$column];
    }
    $sortarr = array_map('strtolower',$sortarr);
    $sort_method = SORT_STRING;
    if ($column == 'mark' or $column == 'duration') $sort_method = SORT_NUMERIC;
    if ($sort_order == 'asc') {
      array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
    } else {
      array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
    }
    return $marray;
  }
  
  // Get lab information.
  $labs = array();
  $results = $mysqli->query("SELECT id, room_no, name FROM labs");
  while ($row = $results->fetch_assoc()) {
    $lab_id = $row['id'];
    $labs[$lab_id]['room_no'] = $row['room_no'];
    $labs[$lab_id]['name'] = $row['name'];
  }
  $results->close();
  
  // Get papers running on various dates.
  $results = $mysqli->query("SELECT DATE_FORMAT(start_date,'%Y/%m/%d') AS date, labs,start_date, DATE_FORMAT(start_date,'%H:%i') AS start_time, DATE_FORMAT(end_date,'%H:%i') AS end_time, property_id, paper_title, DATE_FORMAT(start_date,'%c') AS month, DATE_FORMAT(start_date,'%Y') AS cal_year, DATE_FORMAT(start_date,'%e') AS start_day, end_date, moduleID, paper_type, paper_ownerID, email FROM properties LEFT JOIN users ON users.id = paper_ownerID WHERE start_date>=" . $current_year . "0101000000 AND end_date<=" . $current_year . "1231235959 AND (paper_type='2' OR paper_type='4') AND deleted IS NULL ORDER BY start_date");
  $paper_no = 0;
  while ($row = $results->fetch_assoc()) {
    $paper_details[$paper_no]['labs'] = $row['labs'];
    $paper_details[$paper_no]['date'] = $row['date'];
    $paper_details[$paper_no]['start_day'] = $row['start_day'];
    $paper_details[$paper_no]['start_date'] = $row['start_date'];
    $paper_details[$paper_no]['end_date'] = $row['end_date'];
    $paper_details[$paper_no]['paper_title'] = $row['paper_title'];
    $paper_details[$paper_no]['property_id'] = $row['property_id'];
    $paper_details[$paper_no]['month'] = $row['month'];
    $paper_details[$paper_no]['cal_year'] = $row['cal_year'];
    $paper_details[$paper_no]['start_time'] = $row['start_time'];
    $paper_details[$paper_no]['end_time'] = $row['end_time'];
    $paper_details[$paper_no]['paper_type'] = $row['paper_type'];
    $paper_details[$paper_no]['paper_ownerID'] = $row['paper_ownerID'];
    $paper_details[$paper_no]['paper_owner_email'] = $row['email'];
    $tmp_modules = explode(',',$row['moduleID']);
    $paper_details[$paper_no]['moduleID'] = $tmp_modules[0];
    $paper_no++;
  }
  $results->close();

  // Sort all papers correctly by start time
  $sortby = 'start_time';
  $ordering = 'asc';
  $paper_details = array_csort($paper_details,$sortby,$ordering);
  
  //echo '<pre>';
  //var_dump($paper_details);
  
  $v = new vcalendar();
  // create a new calendar instance
  $v->setConfig( $_SERVER['PHP_AUTH_USER'], $_SERVER['SERVER_ADDR'] );
  $v->setProperty( 'method', 'PUBLISH' );
  // required of some calendar software
  $v->setProperty( "x-wr-calname", "Touchstone Exam Calendar" );
  // required of some calendar software
  $v->setProperty( "X-WR-CALDESC", "A list of TouchStone exams" );
  // required of some calendar software
  $v->setProperty( "X-WR-TIMEZONE", "Europe/London" );
  
  foreach($paper_details as $paper) {
    
    //remove dodgy looking papers
    if($paper['start_date'] == $paper['end_date']) {
      continue;
    }
    if($paper['labs'] == '') {
      continue;
    }
    
    $vevent = new vevent();
    $vevent->setProperty( 'dtstart', $paper['start_date']);
    $vevent->setProperty( 'dtend', $paper['end_date']);
    // alt. date format, now for an all-day event
    $vevent->setProperty( "organizer" , $paper['paper_owner_email'] );
    $vevent->setProperty( 'summary', 'EXAM:' . $paper['paper_title'] );
    $vevent->setProperty( 'description', $paper['paper_title'] );
    
    $rooms = explode(',',$paper['labs']);
    $roomList = '';
    foreach ($rooms as $individual_room) {
      if ($roomList == '') {
        if ($labs[$individual_room]['room_no'] != '') $roomList = $labs[$individual_room]['room_no'];
      } else {
        if ($labs[$individual_room]['room_no'] != '') $roomList .= ',' . $labs[$individual_room]['room_no'];
      }
    }
    $vevent->setProperty( 'LOCATION', $roomList);
    $v->setComponent ( $vevent );
  }
  
  //output ical
  $v->returnCalendar();
  //echo '</pre>';
?>