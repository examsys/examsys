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
* Save marks for individual textbox questions
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/errors.php';

$status = 'ERROR';

$paperID = check_var('paper_id', 'POST', true, false, true);
$q_id = check_var('q_id', 'POST', true, false, true);
$log_id = check_var('log_id', 'POST', true, false, true);
$marker_id = check_var('marker_id', 'POST', true, false, true);
$mark = check_var('mark', 'POST', true, false, true);
$comments = isset($_POST['comments']) ? $_POST['comments'] : '';
$phase = check_var('phase', 'POST', true, false, true);
$log = check_var('log', 'POST', true, false, true);
$user_id = check_var('user_id', 'POST', true, false, true);
$reminders = isset($_POST['reminders']) ? $_POST['reminders'] : '';

if ($mark != 'NULL') {
  $sql = <<< QUERY
INSERT INTO textbox_marking (paperID, q_id, answer_id, markerID, mark, comments, date, phase, logtype, student_userID, reminders)
VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
markerID = ?, mark = ?, comments = ?, reminders = ?, date = NOW()
QUERY;

  try {
    $result = $mysqli->prepare($sql);
    $x = $mysqli->error;
    if ($result) {
      $result->bind_param('iiiidsiiisidss', $paperID, $q_id, $log_id, $marker_id, $mark, $comments, $phase, $log, $user_id, $reminders, $marker_id, $mark, $comments, $reminders);
      $result2 = $result->execute();
      if ($result2 !== false) {
        $status = 'OK';
      }
      $result->close();
    }
  } catch (exception $ex) {
    // No need to do anything
  }
} else {
    $status = 'OK';
}

echo $status;
