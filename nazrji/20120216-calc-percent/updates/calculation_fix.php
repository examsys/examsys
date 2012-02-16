<?php
// 15/02/2012
require '../include/sysadmin_auth.inc';
set_time_limit(0);

$result = $mysqli->prepare("SELECT DISTINCT q_id, display_method, score_method, marks_correct, marks_incorrect, marks_partial FROM questions, options WHERE questions.q_id=options.o_id AND q_type='calculation'");
$result->execute();
$result->store_result();
$result->bind_result($q_id, $display_method, $score_method, $marks_correct, $marks_incorrect, $marks_partial);
while ($result->fetch()) {
  $question_parts = explode(',', $display_method);
  $tolerance = $question_parts[1];

  $result2 = $mysqli->prepare("SELECT id, user_answer, mark FROM log2 WHERE q_id=$q_id");
  $result2->execute();
  $result2->store_result();
  $result2->bind_result($log_id, $user_answer, $original_mark);
  while ($result2->fetch()) {
    $answer_parts = explode('|', $user_answer);
    $student_answer = $answer_parts[0];
    $answer = $answer_parts[1];
    
    $difference = round(abs($student_answer - $answer), 12);
    if (trim($student_answer) != '') {
      if ($student_answer == $answer) {
        $mark = $marks_correct;
      } elseif ($difference <= $tolerance and $tolerance > 0) {
        $mark = $marks_correct;
      } elseif ($difference <= $tolerance and $tolerance > 0) {
        $mark = $marks_partial;
      } else {
        $mark = $marks_incorrect;
      }
    } else {
      $mark = 0;  // No answer
    }
    echo "$log_id $user_answer = $mark<br />";

    if ($original_mark != $mark) {
      $adjust = $mysqli->prepare("UPDATE log2 SET mark=? WHERE id=?");
      $adjust->bind_param('di', $mark, $log_id);
      $adjust->execute();
      $adjust->close();
    }
  }
  $result2->close();

}
$result->close();
echo "Done";
?>