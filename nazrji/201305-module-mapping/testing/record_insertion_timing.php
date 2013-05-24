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
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
error_reporting(E_ALL);
@ob_flush();
@ob_end_flush();
@ob_implicit_flush(1);
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
/**
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package Rogō
 */


$count = 16000;
$cfg_web_root = '../';
require_once '../include/custom_error_handler.inc';
require_once '../classes/configobject.class.php';
require_once '../classes/usernotices.class.php';
require_once '../classes/userobject.class.php';
require_once '../classes/dbutils.class.php';
@apache_setenv('no-gzip', 1);

@ini_set('implicit_flush', 1);
error_reporting(E_ALL);


@ob_implicit_flush(1);
@apache_setenv('no-gzip', 1);
@ini_set('implicit_flush', 1);

set_time_limit(0);
$configObject = Config::get_instance();
$notice = null;
$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_staff_user'), $configObject->get('cfg_db_staff_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, 'mysqli', 6446);
error_reporting(E_ALL);
$db = $mysqli;
if ($mysqli->error) {
  try {
    throw new Exception("MySQL error $mysqli->error <br> Query:<br> ", $mysqli->errno);
  } catch (Exception $e) {
    echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
    echo nl2br($e->getTraceAsString());

  }
}



function execute($save_answers, $db, $count, $commit_interval = 800) {
  global $log_id, $mark, $totalpos, $saved_response, $screen_no, $tmp_duration, $dismiss, $option_order, $metadataID;


  $screen_no = 1;
  $metadataID = 1+10000+((rand(0,100)/10)*7123);
  $log_id = 1;

  for ($i = 0; $i < $count; $i++) {


    if ($i % 80 == 0) {
      echo "<br>:::$i<br />\n";
    }

    if (rand(0, 100) > 57) {
      $metadataID++;
      if ($metadataID % 25 == 0) {
        $screen_no++;
        if($screen_no>10) { $screen_no=1;}
      }
    }


    $mark = rand(0, 100);
    $totalpos = $mark;
    $saved_response = round($mark * 1.2, 0);
    $tmp_duration = round($mark * 1.5, 0);
    $dismiss = round($mark / 2, 0);
    $option_order = round($mark / 1.2, 0);


    $res = $save_answers->execute();

    $log_id++;

    if ($db->error) {
      try {
        throw new Exception("MySQL error $db->error <br> Query:<br> ", $db->errno);
      } catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";


      }
    }

    if ($i % $commit_interval == 0) {
      $db->commit();
    }
    print $res;
    $old_q_id = $i; //$q_id;


  }
}

$db = $mysqli;

$db->autocommit(false);

for ($times = 0; $times < 10; $times++) {
  $save_answers = $db->prepare("INSERT INTO log10 VALUES (NULL, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
  $save_answers->bind_param('idisiissi', $log_id, $mark, $totalpos, $saved_response, $screen_no, $tmp_duration, $dismiss, $option_order, $metadataID);
  $starttime = microtime(true);
  execute($save_answers, $mysqli, $count);
  $endtime = microtime(true);
  $timetaken[] = $endtime - $starttime;
  $save_answers->close();
  $db->commit();
 // $db->query("TRUNCATE log0");
  $individual[] = $endtime - $starttime;
}
$average = array_sum($timetaken) / count($timetaken);
$results[] = $average;
print "<br>\nINSERT time taken is: " . $average;

unset($timetaken);

for ($times = 0; $times < 10; $times++) {
  $save_answers = $db->prepare("REPLACE INTO log10 VALUES (NULL, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
  $save_answers->bind_param('idisiissi', $log_id, $mark, $totalpos, $saved_response, $screen_no, $tmp_duration, $dismiss, $option_order, $metadataID);
  $starttime = microtime(true);
  execute($save_answers, $mysqli, $count);
  $endtime = microtime(true);
  $timetaken[] = $endtime - $starttime;
  $save_answers->close();
  $db->commit();
 // $db->query("TRUNCATE log0");
  $individual[] = $endtime - $starttime;
}
$average = array_sum($timetaken) / count($timetaken);
$results[] = $average;
print "<br>\nREPLACE time taken is: " . $average;

unset($timetaken);

for ($times = 0; $times < 10; $times++) {
  $save_answers = $db->prepare("INSERT INTO log10 VALUES (NULL, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?) ON DUPLICATE KEY UPDATE mark=?");
  $save_answers->bind_param('idisiissii', $log_id, $mark, $totalpos, $saved_response, $screen_no, $tmp_duration, $dismiss, $option_order, $metadataID, $mark);
  $starttime = microtime(true);
  execute($save_answers, $mysqli, $count);
  $endtime = microtime(true);
  $timetaken[] = $endtime - $starttime;
  $save_answers->close();
  $db->commit();
 // $db->query("TRUNCATE log0");
  $individual[] = $endtime - $starttime;
}
$average = array_sum($timetaken) / count($timetaken);
$results[] = $average;
print "<br>INSERT ON DUPLIATE KEY time taken is: " . $average;

unset($timetaken);

var_dump($results);

var_dump($individual);