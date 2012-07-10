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

  require '../include/staff_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Reassign Script to User</title>
  <style type="text/css">
  body {color:black; font-family:Arial,sans-serif}
  </style>

  <script type="text/javascript">
    function reloadClose() {
      window.opener.location.href = window.opener.location.href;
      window.close();
    }
  </script>
</head>

<body onload="reloadClose()">
<?php
  // Check if the exam is still running. Re-assignment mid-exam would upset the data.
  $result = $mysqli->prepare("SELECT UNIX_TIMESTAMP(end_date) FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($end_date);
  $result->fetch();
  $result->close();

  if (time() < $end_date) {
    echo "<p><strong>Warning</strong><p><p>Exam scripts cannot be reassigned mid exam.<br />Please wait until after the exam has finished</p>\n";
    exit;
  }

  if ($_POST['button_pressed'] == 'Accept') {
    $log_type = 'log' . $_POST['log_type'];

    // Get questions that are already in the standard log
    $logged_qns = array();
    $log_check = $mysqli->prepare("SELECT $log_type.id, $log_type.q_id FROM ($log_type, log_metadata) WHERE $log_type.userID=log_metadata.userID AND $log_type.q_paper=log_metadata.paperID AND $log_type.started=log_metadata.started AND $log_type.userID=? AND q_paper=? AND $log_type.started=?");
    $log_check->bind_param('iis', $_POST['userID'], $_POST['paperID'], $_POST['started']);
    $log_check->execute();
    $log_check->store_result();
    $log_check->bind_result($log_id, $log_q_id);
    while($log_check->fetch()) {
      $logged_qns[$log_q_id] = $log_id;
    }
    $log_check->close();
    
    $stmt = $mysqli->prepare("SELECT q_id, mark, totalpos, user_answer, log_late.screen, ipaddress, duration, student_grade, year, updated, dismiss, attempt, option_order FROM (log_late, log_metadata) WHERE log_late.userID=log_metadata.userID AND log_late.q_paper=log_metadata.paperID AND log_late.started=log_metadata.started AND log_late.userID=? AND q_paper=? AND log_late.started=?");
    $stmt->bind_param('iis', $_POST['userID'], $_POST['paperID'], $_POST['started']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($q_id, $mark, $totalpos, $user_answer, $screen, $ipaddress, $duration, $student_grade, $year, $updated, $dismiss, $attempt, $option_order);
    while ($stmt->fetch()) {
      if (array_key_exists($q_id, $logged_qns)) {
        // Update the record in the real log table with values from log_late
        $update = $mysqli->prepare("UPDATE $log_type SET mark=?, user_answer=?, duration=?, updated=? WHERE id=?");
        $update->bind_param('iissi', $mark, $user_answer, $duration, $updated, $logged_qns[$q_id]);
        $update->execute();
        $update->close();
      } else {
        // Insert the records from log_late into the real log table
        $insert = $mysqli->prepare("INSERT INTO $log_type VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param('isiidisiisss', $_POST['userID'], $_POST['started'], $_POST['paperID'], $q_id, $mark, $totalpos, $user_answer, $screen, $duration, $updated, $dismiss, $option_order);
        $insert->execute();
        $insert->close();
      }
    }
    $stmt->close();
  }
  
  if (trim($_POST['reason']) != '') {
    $reason = trim($_POST['reason']);
    
    $result = $mysqli->prepare("INSERT INTO student_notes VALUES (NULL, ?, ?, NOW(), ?, ?)");
    $result->bind_param('isis', $_POST['userID'], $reason, $_POST['paperID'], $userID);
    $result->execute();
    $result->close();
  }
  
  // Clearing up of records in 'log_late' table.
  $result = $mysqli->prepare("DELETE FROM log_late WHERE userID=? AND q_paper=? AND started=?");
  $result->bind_param('iis', $_POST['userID'], $_POST['paperID'], $_POST['started']);
  $result->execute();
  $result->close();
  
  $mysqli->close();
?>

</body>
</html>