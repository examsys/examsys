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

require_once realpath(dirname(__FILE__).DIR_SEPARATOR.'../classes/networkutils.class.php');

function time_to_seconds($seconds) {
  $hr = intval(substr($seconds,8,2));
  $min = intval(substr($seconds,10,2));
  $sec = intval(substr($seconds,12,2));
  return ($hr * 3600) + ($min * 60) + $sec;
}

function record_comments($paper_id, $screen_no, $dblink, $posted_vars, $userid, $review_type) {
  $question_no = 0;
  $old_q_id = NULL;
  $userid = trim($userid);
  $submit_time = date("YmdHis", time());

  $questions = $dblink->query("SELECT q_id, q_type FROM (papers, questions) WHERE paper=$paper_id AND screen=" . $screen_no . " AND papers.question=questions.q_id ORDER BY display_pos");
  while ($row = $questions->fetch_assoc()) {
    if ($old_q_id != $row['q_id']) {
      // Record external examiner comments.
      if ($row['q_type'] != 'info') {
        $question_no++;
        $result = $dblink->prepare("DELETE FROM review_comments WHERE q_paper=? AND q_id=? AND reviewer=?");
        $result->bind_param('iis', $paper_id, $row['q_id'], $userid);
        $result->execute();  
        $result->close();

        $tmp_duration = time_to_seconds($submit_time) - time_to_seconds($_POST['page_start']);
        if ($tmp_duration < 0) $tmp_duration += 86400;
        $tmp_duration += $_POST['previous_duration'];
        $extcomments = $_POST["extcomments$question_no"];
        $result = $dblink->prepare("INSERT INTO review_comments VALUES (NULL,?,?,?,?,?,?,'Not actioned','',?,'" . NetworkUtils::get_ipaddress() . "',$tmp_duration,?)");
        $result->bind_param('iiisissi', $paper_id, $row['q_id'], $_POST["exttype$question_no"], $extcomments, $userid, $_POST['sessionid'], $review_type, $_POST['old_screen']);
        $result->execute();  
        $result->close();
      }
    }
    $old_q_id = $row['q_id'];
  }                    // End of while loop.
  $questions->close();
}
?>