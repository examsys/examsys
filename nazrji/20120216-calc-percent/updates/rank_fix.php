<?php
// 15/02/2012
require '../include/sysadmin_auth.inc';

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
echo "Done";
?>