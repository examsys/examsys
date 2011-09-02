<?php
  require '../config/config.inc';
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);

  /*
  $result = $mysqli->prepare("SELECT userID, started, q_paper, q_id, mark, totalpos, user_answer, screen, ipaddress, duration, student_grade, year, updated, dismiss, option_order FROM log2 WHERE q_paper IN (3022,3023,3024) AND started>20110324120000 AND started<20110324130000");
  $result->execute();
  $result->store_result();
  $result->bind_result($userID, $started, $q_paper, $q_id, $mark, $totalpos, $user_answer, $screen, $ipaddress, $duration, $student_grade, $year, $updated, $dismiss, $option_order);
  while ($result->fetch()) {
    echo "<div>INSERT INTO log0 VALUES(NULL, $userID, '$started', $q_paper, $q_id, $mark, $totalpos, '$user_answer', $screen, '$ipaddress', $duration, '$student_grade', '$year', '$updated', '$dismiss', '$option_order')</div>\n";
    $adjust = $mysqli->prepare("INSERT INTO log0 VALUES(NULL, $userID, '$started', $q_paper, $q_id, $mark, $totalpos, '$user_answer', $screen, '$ipaddress', $duration, '$student_grade', '$year', '$updated', '$dismiss', '$option_order')");
    $adjust->execute();
    $adjust->close();
  }
  $result->close();
  */
  
  $adjust = $mysqli->prepare("DELETE FROM log2 WHERE q_paper IN (3022,3023,3024)");
  $adjust->execute();
  $adjust->close();

?>