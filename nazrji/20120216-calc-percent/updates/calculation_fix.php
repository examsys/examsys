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
  $partial_tolerance = $question_parts[2];

  for ($i=0; $i<3; $i++) {
    $result2 = $mysqli->prepare("SELECT id, user_answer, mark FROM log$i WHERE q_id=$q_id");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($log_id, $user_answer, $original_mark);
    while ($result2->fetch()) {
      $answer_parts = explode('|', $user_answer);
      $student_answer = $answer_parts[0];
      $answer = $answer_parts[1];

      $student_answer = preg_replace('([^0-9\.\-])', '', $student_answer);
      
      $difference = round(abs($student_answer - $answer), 12);
      if (trim($student_answer) != '') {
        if ($student_answer == $answer) {
          $mark = $marks_correct;
        } elseif ($difference <= $tolerance and $tolerance > 0) {
          $mark = $marks_correct;
        } elseif ($difference <= $partial_tolerance and $partial_tolerance > 0) {
          $mark = $marks_partial;
        } else {
          $mark = $marks_incorrect;
        }
      } else {
        $mark = 0;  // No answer
      }
      echo "$log_id $user_answer = $mark<br />";

      if ($original_mark != $mark) {
        $adjust = $mysqli->prepare("UPDATE log$i SET mark=? WHERE id=?");
        $adjust->bind_param('di', $mark, $log_id);
        $adjust->execute();
        $adjust->close();
      }
    }
    $result2->close();
  }

}
$result->close();
echo "Done";
?>