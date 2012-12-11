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
* This script is designed to compare marks between the Class Totals report and students' actual exam scripts (finish.php).
* It works by:
*   1. Get summative exam papers in the require date range.
*   2. For each paper call class_totals.php and parse for student IDs and marks.
*   3. For each student call finish.php and compare the mark.
*   4. Echo errors for any which do not match.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
set_time_limit(0);
session_write_close();
$response = '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
ignore_user_abort(true);
header("Connection: close");
header("Content-Length: " . mb_strlen($response));
echo $response;
flush();

$end_dateSQL = 'NOW()';
if (isset($_POST['period']) and $_POST['period'] != '') {
  if ($_POST['period'] == 'week') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 WEEK)';
  } elseif ($_POST['period'] == 'month') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 MONTH)';
  } elseif ($_POST['period'] == 'year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 YEAR)';
  } elseif ($_POST['period'] == '2year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 2 YEAR)';
  } elseif ($_POST['period'] == '3year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 3 YEAR)';
  }
} else {
  $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 5 YEAR)';
}

if (isset($_POST['server']) and $_POST['server'] != '') {
  $server = $_POST['server'];
} else {
  $server = $protocol . $_SERVER['SERVER_ADDR'];
}

function getData($url) {
  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSLVERSION, 3);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: en-us,en;q=0.5'));

  $output = curl_exec($ch);
  curl_close($ch);
  
  return $output;
}

function tidyLine($line) {
  $line = str_replace('<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="Marking not complete" />&nbsp;', '', $line);
  $parts = explode('>', $line);
  $parts2 = explode('<', $parts[1]);
  
  return str_replace('&nbsp;', '', $parts2[0]);
}

function parseRawMarks($data) {
  $marks = array();
  $line = 0;
  $data_line = explode('<tr', $data);
  
  foreach ($data_line as $row) {
    if (strpos($row,'Display exam script') !== false) {
      $cols = explode('<td', $row);
      
      $tmp_parts = explode("popMenu('", $cols[2]);
      $started = substr($tmp_parts[1], 0, 19);
      
      $tmp_parts2 = explode(',', $tmp_parts[1]);
      $tmp_userID = $tmp_parts2[1];
     
      $marks[$line]['mark'] = tidyLine($cols[5]);
      $marks[$line]['percent'] = tidyLine($cols[6]);
      $marks[$line]['started'] = $started;
      $marks[$line]['userID'] = $tmp_userID;

      $line++;
    }
  }
  return $marks;
}

function parseScript($data) {
  $data_line = explode('<tr', $data);
  
  foreach ($data_line as $row) {
    if (strpos($row,'Your mark') !== false) {
      $cols = explode('>', $row);
      
      $parts = explode(' out of', $cols[4]);
      $mark = round($parts[0],1);  // Round it to 1 decimal because this is what Class Totals does.
    }
  }
  return $mark;
}

$papers = array();
if (isset($_POST['paper']) and $_POST['paper'] != '') {
  $result = $mysqli->prepare("SELECT crypt_name, property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y'), DATE_FORMAT(start_date,'%Y%m%d%H%i%s'), DATE_FORMAT(end_date,'%Y%m%d%H%i%s') FROM properties WHERE property_id=?");
  $result->bind_param('i', $_POST['paper']);
} else {
  $result = $mysqli->prepare("SELECT crypt_name, property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y'), DATE_FORMAT(start_date,'%Y%m%d%H%i%s'), DATE_FORMAT(end_date,'%Y%m%d%H%i%s') FROM properties WHERE paper_type = '2' AND start_date > $start_dateSQL AND end_date < $end_dateSQL AND deleted IS NULL ORDER BY start_date");
}
$result->execute();
$result->bind_result($crypt_name, $paperID, $title, $display_start_date, $start_date, $end_date);
while ($result->fetch()) {
  $papers[] = array('crypt_name'=>$crypt_name, 'paperID'=>$paperID, 'title'=>$title, 'display_start_date'=>$display_start_date, 'start_date'=>$start_date, 'end_date'=>$end_date);
}
$result->close();

$paper_no = count($papers);
$current_no = 0;

$result = $mysqli->prepare("DELETE FROM class_totals_test_local WHERE user_id=?");
$result->bind_param('i', $userObject->get_user_ID());
$result->execute();

$result = $mysqli->prepare("SELECT surname, first_names, username FROM users WHERE id=? LIMIT 1");
foreach ($papers as $paper) {
  $url = $server . "/reports/class_totals.php?paperID=" . $paper['paperID'] . "&startdate=" . $paper['start_date'] . "&enddate=" . $paper['end_date'] . "&repmodule=&repcourse=%&sortby=student_id&module=A14CHH&folder=&percent=100&absent=0&direction=asc&studentsonly=1";
  
  $output = getData($url);
  $marks_set = parseRawMarks($output);
  
  $current_no++;

  $insert = $mysqli->prepare("INSERT INTO class_totals_test_local(user_id, paper_id, status) VALUES(?, ?, 'in_progress')");
  $insert->bind_param('ii', $userObject->get_user_ID(), $paper['paperID']);
  $insert->execute();
  $insert->close();

  $errors = '';
  foreach ($marks_set as $mark) {
    $url = $server . "/paper/finish.php?id=" . $paper['crypt_name'] . "&previous=" . str_replace(' ', '%20', $mark['started']) . "&userid=" . $mark['userID'] . "&surname=Test&log_type=2&percent=" . str_replace('%' ,'', $mark['percent']) . "&disable_mappings=1";
    $output = getData($url);
    $script_mark = parseScript($output);
    
    if ($script_mark != $mark['mark']) {
      $result->bind_param('i', $mark['userID']);
      $result->execute();
      $result->store_result();
      $result->bind_result($tmp_surname, $tmp_first_names, $tmp_username);
      $result->fetch();

      if ($errors == '') {
        $errors = '<ul>';
      }
      $errors .= "<li>Problem with " . $mark['userID'] . " $tmp_surname, $tmp_first_names ($tmp_username) - $script_mark / " . $mark['mark'] . "</li>";
    }
  }

  if ($errors != '') {
    $errors .= '</ul>';
    $status = 'failure';
  } else {
    $status = 'success';
  }

  $update = $mysqli->prepare("UPDATE class_totals_test_local SET status=?, errors=? WHERE user_id=? AND paper_id=?");
  $update->bind_param('ssii', $status, $errors, $userObject->get_user_ID(), $paper['paperID']);
  $update->execute();
  $update->close();
}
$result->close();
