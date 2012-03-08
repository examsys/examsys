<?php
// 16/02/2012
require '../include/sysadmin_auth.inc';
set_time_limit(0);

// Partial marks fix
$result = $mysqli->prepare("SELECT q_id FROM questions WHERE q_type='rank' AND score_method='Allow partial Marks'");
$result->execute();
$result->store_result();
$result->bind_result($q_id);
while ($result->fetch()) {
  echo "<div>UPDATE options SET marks_partial=0.5 WHERE o_id=$q_id</div>";
  $adjust = $mysqli->prepare("UPDATE options SET marks_partial=0.5 WHERE o_id=?");
  $adjust->bind_param('d', $q_id);
  $adjust->execute();
  $adjust->close();
}
$result->close();

// Remark student answers in Log
$result = $mysqli->prepare("SELECT q_id, score_method FROM questions WHERE q_type='rank'");
$result->execute();
$result->store_result();
$result->bind_result($q_id, $score_method);
while ($result->fetch()) {
  // Get options and marks for the question
  $correct = array();
  $result2 = $mysqli->prepare("SELECT correct, marks_correct, marks_incorrect, marks_partial FROM options WHERE o_id=$q_id ORDER BY id_num");
  $result2->execute();
  $result2->store_result();
  $result2->bind_result($correct_answer, $marks_correct, $marks_incorrect, $marks_partial);
  while($result2->fetch()) {
    $correct[] = $correct_answer;
  }
  $result2->close();
  
  for ($log_no=0; $log_no<3; $log_no++) {
    // Get student answers for the question
    $result2 = $mysqli->prepare("SELECT id, user_answer, mark FROM log$log_no WHERE q_id=$q_id");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($log_id, $user_answer, $original_mark);
    while ($result2->fetch()) {
      $answers = explode(',', $user_answer);
      $mark = 0;
      $totalpos = 0;
      $correct_rank = true;
      if ($score_method == 'Bonus Mark') $totalpos += $marks_correct;
      
      for ($i=0; $i<count($correct); $i++) {
        if ($score_method == 'Mark per Option') {
          if ($answers[$i] == $correct[$i]) $mark += $marks_correct;
        } elseif ($score_method == 'Mark per Question') {
          if ($answers[$i] != 'u' and $answers[$i] <> $correct[$i]) $correct_rank = false;
        } elseif ($score_method == 'Allow partial Marks') {
          if ($answers[$i] != 0 and $correct[$i] != 0) {
            if ($answers[$i] == $correct[$i]) {
              $mark += $marks_correct;
            } elseif ($answers[$i] == ($correct[$i] + 1)) {
              $mark += $marks_partial;
            } elseif ($answers[$i] == ($correct[$i] - 1)) {
              $mark += $marks_partial;
            }
          }
        } elseif ($score_method == 'Bonus Mark') {
          if ($answers[$i] != 0) {
            if ($correct[$i] != 0) $mark += $marks_correct;
            if ($answers[$i] <> $correct[$i]) $correct_rank = false;
          }
          if ($answers[$i] == 0 and $correct[$i] != 0) $correct_rank = false;
        }  
        if ($score_method == 'Mark per Question') {
          $totalpos = $marks_correct;
        } elseif ($correct[$i] != 0 or $score_method == 'Mark per Option') {
          $totalpos += $marks_correct;
        }
      }
      
      if ($correct_rank == true and $mark == ($totalpos - 1) and $score_method == 'Bonus Mark') {
        $mark++;  // Add one mark if the user has all options in the correct order
      } elseif ($score_method == 'Mark per Question') {
        $totalpos = $marks_correct;
        if ($correct_rank) {
          $mark = $marks_correct;
        } else {
          $mark = $marks_incorrect;
        }
      }
      
      echo "<div>$score_method, UPDATE log$log_no SET mark=$mark, totalpos=$totalpos WHERE id=$log_id</div>";

      $adjust = $mysqli->prepare("UPDATE log$log_no SET mark=?, totalpos=? WHERE id=?");
      $adjust->bind_param('dii', $mark, $totalpos, $log_id);
      $adjust->execute();
      $adjust->close();
    }
    $result2->close();
  }

}
$result->close();
echo "Done";
?>