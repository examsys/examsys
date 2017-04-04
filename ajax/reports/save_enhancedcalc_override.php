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
* Save marks for individual Calculation questions.
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../../include/staff_auth.inc';
require_once '../../include/errors.php';
require_once '../../plugins/questions/enhancedcalc/enhancedcalc.class.php';

$status = 'ERROR';

$log_id = check_var('log_id', 'POST', true, false, true);
$user_id = check_var('user_id', 'POST', true, false, true);
$q_id = check_var('q_id', 'POST', true, false, true);
$paper_id = check_var('paper_id', 'POST', true, false, true);
$marker_id = check_var('marker_id', 'POST', true, false, true);
$mark_type = check_var('mark_type', 'POST', true, false, true);
$log = check_var('log', 'POST', true, false, true);

$reason = (isset($_POST['reason'])) ? $_POST['reason'] : '';

$mysqli->autocommit(false);

// Read question from database.
$result = $mysqli->prepare("SELECT leadin, settings FROM questions WHERE q_id = ?");
$result->bind_param('i', $q_id);
$result->execute();
$result->bind_result($leadin, $settings);
$result->fetch();
$result->close();

$question_obj = new enhancedcalc($configObject);
$question_obj->set_settings($settings);

$q_marks = $question_obj->get_question_marks();
//$q_marks_rev = array_flip($q_marks);

if ($q_marks !== false) {
  // Get user's current mark
  $sql = "SELECT mark FROM log$log WHERE id = ?";
  $result = $mysqli->prepare($sql);
  $result->bind_param('i', $log_id);
  $result->execute();
  $result->bind_result($orig_mark);
  $result->fetch();
  $result->close();

  $sql = <<< QUERY
INSERT INTO marking_override (log_id, log_type, user_id, q_id, paper_id, marker_id, date_marked, new_mark_type, reason)
VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?) ON DUPLICATE KEY UPDATE
marker_id = ?, new_mark_type = ?, date_marked = NOW(), reason = ?
QUERY;

  $or_id = -1;

  try {
    $result = $mysqli->prepare($sql);
    if ($result) {
      $result->bind_param('iiiiiississ', $log_id, $log, $user_id, $q_id, $paper_id, $marker_id, $mark_type, $reason, $marker_id, $mark_type, $reason);
      $result2 = $result->execute();
      if ($result2 !== false) {
        $status = 'OK';
      }
      $result->close();
    }

    if ($status == 'OK') {
      $or_id = $mysqli->insert_id;

      $new_mark = $q_marks[$mark_type];

      // Update the mark mark
      $sql = "UPDATE log{$log} SET mark = ?, adjmark = ? WHERE id = ? AND q_id = ?";
      $result = $mysqli->prepare($sql);
      if ($result) {
        $result->bind_param('ddii', $new_mark, $new_mark, $log_id, $q_id);
        $result2 = $result->execute();
        $result->store_result();
        if ($result2 == false) {
          $status = 'ERROR';
        } else {
          // Invalidate the cache so it will get rebuilt with new mark
          $assessment = new assessment($mysqli, $configObject);
          $update_params = array(
            'recache_marks' => array('i', 1)
          );
          $cache_result = $assessment->db_update_assessment($paper_id, $update_params);
          if ($cache_result == false) {
            $status = 'ERROR';
          }
        }
        $result->close();
      }
    }
  } catch (exception $ex) {
    $status = 'ERROR';
  }

  if ($status == 'ERROR') {
    $mysqli->rollback();
  } else {
    $mysqli->commit();

    $logger = new Logger($mysqli);
    $logger->track_change('enhancedcalc_override', $or_id, $marker_id, $orig_mark, $new_mark, $q_id);
  }

  $mysqli->autocommit(true);
}

echo $status;
