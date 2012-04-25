<?php
// 14/02/2012
require '../include/sysadmin_auth.inc';
set_time_limit(0);

$result = $mysqli->prepare("SELECT q_id, MAX(marks_correct) FROM options, questions WHERE questions.q_id=options.o_id AND q_type='mrq' GROUP BY q_id");
$result->execute();
$result->store_result();
$result->bind_result($q_id, $marks_correct);
while ($result->fetch()) {
  $marks_correct = round($marks_correct);
  if ($marks_correct > 20) {
    $marks_correct = 20;
  }
  echo "<div>ALTER options SET marks_correct=$marks_correct WHERE o_id=$q_id</div>";
  $adjust = $mysqli->prepare("UPDATE options SET marks_correct=? WHERE o_id=?");
  $adjust->bind_param('ii', $marks_correct, $q_id);
  $adjust->execute();
  $adjust->close();
}
$result->close();
echo "Done";
?>