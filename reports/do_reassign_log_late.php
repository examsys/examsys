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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

$paperID    = check_var('paperID', 'POST', true, false, true);
$userID     = check_var('userID', 'POST', true, false, true);
$metadataID = check_var('metadataID', 'POST', true, false, true);
$log_type   = check_var('log_type', 'POST', true, false, true);

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Late Submission<?php echo ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function() {
      window.opener.location.href = window.opener.location.href;
      window.close();
    });
  </script>
</head>

<body>
<?php
  // Check if the exam is still running. Re-assignment mid-exam would upset the data.
  $propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
  if ($propertyObj->is_live()) {
    echo "<h1>" . $string['warning'] . "</h1><p>" . $string['msg2'] . "</p><p><input type=\"button\" value=\"" . $string['ok'] . "\" class=\"ok\" onclick=\"window.close();\"/></p>\n</body>\n</html>\n";
    exit();
  }

  // Get questions that are already in the standard log
  $row_no = 0;
  $logged_qns = array();
  $log_check = $mysqli->prepare("SELECT lm.id, l.id, l.q_id FROM log_metadata lm LEFT JOIN log$log_type l ON l.metadataID = lm.id WHERE lm.userID = ? AND lm.paperID = ? AND lm.id = ?");
  $log_check->bind_param('iis', $userID, $paperID, $metadataID);
  $log_check->execute();
  $log_check->store_result();
  $log_check->bind_result($log_metadata_id, $log_id, $log_q_id);
  $row_no = $log_check->num_rows;
  while ($log_check->fetch()) {
    $logged_qns[$log_q_id] = $log_id;
  }
  $log_check->close();
    
  if ($row_no == 0) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }

  if ($_POST['button_pressed'] == 'Accept') {
    $log_type = 'log' . $log_type;

    $stmt = $mysqli->prepare("SELECT q_id, mark, totalpos, user_answer, screen, duration, updated, dismiss, option_order FROM log_late WHERE metadataID = ?");
    $stmt->bind_param('i', $log_metadata_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($q_id, $mark, $totalpos, $user_answer, $screen, $duration, $updated, $dismiss, $option_order);
    while ($stmt->fetch()) {
      if (array_key_exists($q_id, $logged_qns)) {
        // Update the record in the real log table with values from log_late
        $update = $mysqli->prepare("UPDATE $log_type SET mark = ?, user_answer = ?, duration = ?, updated = ? WHERE id = ?");
        $update->bind_param('isssi', $mark, $user_answer, $duration, $updated, $logged_qns[$q_id]);
        $update->execute();
        $update->close();
      } else {
        // Insert the records from log_late into the real log table
        $insert = $mysqli->prepare("INSERT INTO $log_type (q_id, mark, totalpos, user_answer, screen, duration, updated, dismiss, option_order, metadataID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param('idisiisssi', $q_id, $mark, $totalpos, $user_answer, $screen, $duration, $updated, $dismiss, $option_order, $log_metadata_id);
        $insert->execute();
        $insert->close();
      }
    }
    $stmt->close();
  }

  if (trim($_POST['reason']) != '') {
    $reason = trim($_POST['reason']);

    $result = $mysqli->prepare("INSERT INTO student_notes VALUES (NULL, ?, ?, NOW(), ?, ?)");
    $result->bind_param('isis', $userID, $reason, $paperID, $userObject->get_user_ID());
    $result->execute();
    $result->close();
  }

  // Clearing up of records in 'log_late' table.
  $result = $mysqli->prepare("DELETE FROM log_late WHERE metadataID = ?");
  $result->bind_param('i', $log_metadata_id);
  $result->execute();
  $result->close();

  $mysqli->close();
?>

</body>
</html>