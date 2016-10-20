<?php
/**
 * Converts user answers for fill in the blank questions from
 * a pipe seperated list to a json encoded array.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package update
 */
if ($updater_utils->check_version("6.1.0") and !$updater_utils->has_updated('blank_answer_encoding_log1')) {
  // Find all the answers for fill in the blank questions.
  $select_sql = "SELECT id, user_answer FROM log1 l JOIN questions q ON q.q_id = l.q_id WHERE q.q_type = 'blank'";
  $blank_answers = $mysqli->prepare($select_sql);
  $blank_answers->execute();
  $blank_answers->store_result();
  $blank_answers->bind_result($id, $answer);
  // Split transaction if large.
  $limit = 50000;
  $rows = $blank_answers->num_rows();
  if ($rows > $limit) {
      $split = true;
  } else {
      $split = false;
  }
  // JSON encode all the answers. They will be in a string like:
  // |answer1|answer2|answer3
  $count = 0;
  while ($blank_answers->fetch()) {
    if (substr($answer, 0, 1) != '|') {
      // This is to catch any blank questions created after the code
      // changes have been applied, but before this script has been run.
      continue;
    }
    $user_answer = explode('|', substr($answer, 1));
    $user_answer = json_encode($user_answer);
    $update_sql = "UPDATE log1 SET user_answer = ? WHERE id = ?";
    $update = $mysqli->prepare($update_sql);
    $update->bind_param('si', $user_answer, $id);
    $update->execute();
    $update->close();
    $count++;
    if ($split and $count == $limit) {
      $mysqli->commit();
      $count = 0;
    }
  }
  $blank_answers->close();
  $updater_utils->record_update('blank_answer_encoding_log1');
}
