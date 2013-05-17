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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package Rogō
 */


$cfg_web_root = '../';
require_once '../include/custom_error_handler.inc';
require_once '../classes/configobject.class.php';
require_once '../classes/usernotices.class.php';
require_once '../classes/userobject.class.php';
require_once '../classes/dbutils.class.php';
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

set_time_limit(0);
$configObject = Config::get_instance();
$notice = null;
$db = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), 'root', '@Password1' , 'rogotest', $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port')); //$configObject->get('cfg_db_staff_user') $configObject->get('cfg_db_staff_passwd') $configObject->get('cfg_db_database')

error_reporting(E_ALL);


function get_log_ids_and_lock($paper_log_type, $screen_no, $metadataID, $db) {
  $log_ids = array();
  $log_check = $db->prepare("SELECT id, q_id FROM log$paper_log_type WHERE metadataID = ? AND screen = ? FOR UPDATE");
  $log_check->bind_param('ii', $metadataID, $screen_no);
  $log_check->execute();
  $log_check->bind_result($tmp_id, $tmp_q_id);
  while ($log_check->fetch()) {
    $log_ids[$tmp_q_id] = $tmp_id;
  }
  $log_check->close();

  return $log_ids;
}

$metadataID = $_GET['metadataID'];
$screen_no = $_GET['screen_no'];
$log_id=$_GET['log_id'];
$paper_type = 0;


//turn off auto commit and start transaction. If php exits or we call rollback the inserts/updates will be rolledback
$db->autocommit(false);

//this is set to false on error to trigger a rollback
$save_ok = true;

//get any log ids and lock them for update
$log_ids = get_log_ids_and_lock($paper_type, $screen_no, $metadataID, $db);
$mark = 0;
$old_q_id=0;
if (count($log_ids) > 0) {
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  print "UDPATE<br>\n";
  // prepare to update old records
  $save_answers = $db->prepare("UPDATE log$paper_type SET mark = ?, user_answer = ?, duration = ?, updated = NOW(), dismiss = ? WHERE id = ?");
  $save_answers->bind_param('dsisi', $mark, $saved_response, $tmp_duration, $dismiss, $q_log_id);
} else {
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  print "INSERT<br>\n";
  $save_answers = $db->prepare("INSERT INTO log$paper_type VALUES (NULL, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
  $save_answers->bind_param('idisiissi', $log_id, $mark, $totalpos, $saved_response, $screen_no, $tmp_duration, $dismiss, $option_order, $metadataID);
}
if ($db->error) {
  try {
    throw new Exception("MySQL error $db->error <br> Query:<br> ", $db->errno);
  } catch (Exception $e) {
    echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
    echo nl2br($e->getTraceAsString());

  }
}
for ($i = 0; $i < 10000; $i++) {


  if($i%80 ==0) { echo ":::$i<br />\n";}
  $mark = rand(0, 100);
  $totalpos = rand(0, 50);
  $saved_response = rand(0, 100);
  $tmp_duration = rand(10, 1000);
  $dismiss = rand(0, 10);
  $option_order = rand(0, 100);
  if (isset($log_ids[$old_q_id])) {
    $q_log_id = $log_ids[$old_q_id];
  }

  $res=$save_answers->execute();

  $log_id++;

  if ($db->error) {
    try {
      throw new Exception("MySQL error $db->error <br> Query:<br> ", $db->errno);
    } catch (Exception $e) {
      echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      echo nl2br($e->getTraceAsString());

    }
  }
  print $res;
  $old_q_id = $i;//$q_id;

  usleep(3);
}


$db->commit();
if ($db->error) {
  try {
    throw new Exception("MySQL error $db->error <br> Query:<br> ", $db->errno);
  } catch (Exception $e) {
    echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
    echo nl2br($e->getTraceAsString());

  }
}
?>
