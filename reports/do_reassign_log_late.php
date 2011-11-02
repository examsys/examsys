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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
?>
<html>
<head>
<title>Reassign Script to User</title>
<style>
body {font-size:100%; background-color:#ECE9D8; color:black; font-family:Arial,sans-serif}
</style>

<script language="JavaScript">
  function reloadClose() {
    window.opener.location.href = window.opener.location.href;
    window.close();
  }
</script>
</head>

<body onload="reloadClose();">
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

    $stmt = $mysqli->prepare("SELECT q_id, mark, totalpos, user_answer, screen, ipaddress, duration, student_grade, year, updated, dismiss FROM log_late WHERE userID=? AND q_paper=? AND started=?");
    $stmt->bind_param('iis', $_POST['userID'], $_POST['paperID'], $_POST['started']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($q_id, $mark, $totalpos, $user_answer, $screen, $ipaddress, $duration, $student_grade, $year, $updated, $dismiss);
    while ($row = $stmt->fetch()) {
      // Delete any existing record for the question in the real log table.
      $result = $mysqli->prepare("DELETE FROM $log_type WHERE userID=? AND q_paper=? AND q_id=? AND screen=? AND started=?");
      $result->bind_param('iiis', $_POST['userID'], $_POST['paperID'], $q_id, $screen, $_POST['started']);
      $result->execute();
      $result->close();
    
      // Insert the records from log_late into the real log table.
      $result = $mysqli->prepare("INSERT INTO $log_type VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $result->bind_param('isiidisisissss', $_POST['userID'], $_POST['started'], $_POST['paperID'], $q_id, $mark, $totalpos, $user_answer, $screen, $ipaddress, $duration, $student_grade, $year, $updated, $dismiss);
      $result->execute();  
      $result->close();
    }
    $stmt->close;
  }
  
  if (trim($_POST['reason']) != '') {
    $result = $mysqli->prepare("INSERT INTO student_notes VALUES (NULL,?,?,NOW(),?,?)");
    $result->bind_param('isis', $_POST['userID'], trim($_POST['reason']), $_POST['paperID'], $userID);
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