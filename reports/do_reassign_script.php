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
require_once '../include/errors.php';

$userID       = check_var('userID', 'GET', true, false, true);
$temp_userID  = check_var('temp_userID', 'GET', true, false, true);

// Get start time of the paper.
$papers = array();
$paper_no = 0;
$result = $mysqli->prepare("SELECT DISTINCT paperID, started FROM log_metadata WHERE userID = ?");
$result->bind_param('i', $temp_userID);
$result->execute();
$result->bind_result($q_paper, $started);
while ($result->fetch()) {
  $papers[$paper_no]['ID']      = $q_paper;
  $papers[$paper_no]['started'] = $started;
  $paper_no++;
}
$result->close();

// Get grade and student of the user.
$result = $mysqli->prepare("SELECT grade, yearofstudy, username FROM users WHERE id = ?");
$result->bind_param('i', $userID);
$result->execute();
$result->bind_result($grade, $yearofstudy, $new_username);
$result->fetch();
$result->close();

$mysqli->autocommit(false);

$error = false;
foreach ($papers as $paper) {
  // Record the change in 'track_changes'.
  $logger = new Logger($mysqli);
  $logger->track_change('Exam Script', $paper['ID'], $userObject->get_user_ID(), $temp_userID, $userID, 'Reassigned temporary user');

  // Transfer records in log_metadata.
  $result = $mysqli->prepare("UPDATE log_metadata SET userID = ?, student_grade = ?, year = ? WHERE userID = ? AND paperID = ? AND started = ?");
  if ($mysqli->error) {
    $error = true;
  }
  $result->bind_param('issiis', $userID, $grade, $yearofstudy, $temp_userID, $paper['ID'], $paper['started']);
  $result->execute();
  if ($mysqli->error) {
    $error = true;
  }
  $result->close();

  // Transfer textbox marking (just in case marking done before marks reasignment).
  $result = $mysqli->prepare("UPDATE textbox_marking SET student_userID = ? WHERE student_userID = ? AND paperID = ?");
  if ($mysqli->error) {
    $error = true;
  }
  $result->bind_param('iii', $userID, $temp_userID, $paper['ID']);
  $result->execute();
  if ($mysqli->error) {
    $error = true;
  }
  $result->close();

  // Transfer any student notes.
  $result = $mysqli->prepare("UPDATE student_notes SET userID = ? WHERE userID = ? AND paper_id = ?");
  if ($mysqli->error) {
    $error = true;
  }
  $result->bind_param('iii', $userID, $temp_userID, $paper['ID']);
  $result->execute();
  if ($mysqli->error) {
    $error = true;
  }
  $result->close();

  // Transfer any student toilet break.
  $result = $mysqli->prepare("UPDATE toilet_breaks SET userID = ? WHERE userID = ? AND paperID = ?");
  if ($mysqli->error) {
    $error = true;
  }
  $result->bind_param('ssi', $userID, $temp_userID, $paper['ID']);
  $result->execute();
  if ($mysqli->error) {
    $error = true;
  }
  $result->close();

  if ($error === true) {
    break;
  }
}

if ($error !== true) {
// Free up the temporary account once all assignments are complete
  $result = $mysqli->prepare("DELETE FROM temp_users WHERE assigned_account = ?");
  if ($mysqli->error) {
    $error = true;
  }
  $result->bind_param('s', $_GET['assigned_account']);
  $result->execute();
  if ($mysqli->error) {
    $error = true;
  }
  $result->close();
}

if ($error !== true) {
// Change the password of the temporary account
  $result = $mysqli->prepare("UPDATE users SET password = '' WHERE id = ?");
  if ($mysqli->error) {
    $error = true;
  }
  $result->bind_param('i', $temp_userID);
  $result->execute();
  if ($mysqli->error) {
    $error = true;
  }
  $result->close();
}

if ($error === true) {
  $mysqli->rollback();
} else {
  $mysqli->commit();
}

$mysqli->autocommit(true);
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Reassign Script to User</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
</head>

<body onload="window.opener.location.reload(); window.close();">

</body>
</html>