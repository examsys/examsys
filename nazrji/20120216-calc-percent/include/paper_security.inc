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

function check_labs($paper_type, $labs, $mysqlidb) {
  global $string;
  
  $low_bandwidth = 0;
  
  if ($labs != '') {
    $stmt = $mysqlidb->prepare("SELECT low_bandwidth FROM ip_addresses WHERE address=? AND lab IN ($labs)");
    $stmt->bind_param('s', $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    $stmt->bind_result($low_bandwidth);
    $stmt->store_result();
    $stmt->fetch();
    if ($stmt->num_rows == 0) {
      access_denied($string['denied_location'], $output_header = false);
    }
    $stmt->close();
  } else {
    // Exit if a summative exam is on no labs.
    if ($paper_type == '2') exit;
  }
  
  return $low_bandwidth;
}

function check_paper_password($password) {
  global $string;
  
  if ($password != '') {
    if ($password != $_COOKIE['paperpwd']) {
      access_denied($string['specificpassword'], $output_header = false);
    }
  }
}

function check_datetime($start_date, $end_date) {
  global $string;

  if ((time()+120) < $start_date or (time()-3600) > $end_date) {
    $tmp_string = sprintf($string['error_time'], date('d/m/Y H:i',$start_date), date('d/m/Y H:i',$end_date));
    access_denied($tmp_string, $output_header = false);
  }
}

function check_modules($userID, $moduleID, $calendar_year, $mysqlidb) {
  global $string, $title, $surname, $username;
  
  $attempt = 1;

  if (stripos($_SERVER['PHP_AUTH_USER'], 'user') !== 0) {
     if ($moduleID != '') {
      $cal_year_sql = '';
      if ($calendar_year != '') $cal_year_sql = "AND calendar_year = '$calendar_year'";
      
      $stmt = $mysqlidb->prepare("SELECT moduleid, attempt FROM student_modules WHERE userID=$userID AND moduleid IN ('" . str_replace(",","','",$moduleID) . "') $cal_year_sql");
      $stmt->execute();
      $stmt->bind_result($moduleid, $attempt);
      $stmt->store_result();
      if ($stmt->num_rows == 0) {
        $tmp_string = sprintf($string['notregistered'], $title, $surname, $username, $moduleID, $calendar_year);
        access_denied($tmp_string, $output_header = false);
      } else {
        $stmt->fetch();
      }
      $stmt->close();
    } else {
      access_denied($string['error_module'], false);
    }
  }
  
  return $attempt;
}

function check_metadata($property_id, $userID, $moduleID, $mysqlidb) {
  global $string;

  $stmt = $mysqlidb->prepare("SELECT name, value FROM paper_metadata_security WHERE paperID=?");
  $stmt->bind_param('i', $property_id);
  $stmt->execute();
  $stmt->bind_result($security_type, $security_value);
  $stmt->store_result();
  while ($stmt->fetch()) {
    $check_security = $mysqlidb->prepare("SELECT users_metadata.id FROM users_metadata, modules WHERE users_metadata.moduleid=modules.id AND modules.moduleid IN ('" . str_replace(",", "','", $moduleID) . "') AND userID=? AND type=? AND value=?");
    $check_security->bind_param('iss', $userID, $security_type, $security_value);
    $check_security->execute();
    $check_security->store_result();
    if ($check_security->num_rows == 0) {
      $tmp_string = sprintf($string['error_metadata'], $security_type, $security_value);
      access_denied($tmp_string, $output_header = false);
    }
    $check_security->close();
  }      
  $stmt->close();
}
?>