<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/errors.php';
$paperID    = check_var('paperID', 'GET', true, false, true);
$userID     = check_var('userID', 'GET', true, false, true);
$metadataID = check_var('metadataID', 'GET', true, false, true);
$log_type   = check_var('log_type', 'GET', true, false, true);
// Get the order of the questions on the paper.
$row_no = 0;
$questions = array();
$q_no = 1;
$result = $mysqli->prepare("SELECT question FROM papers, questions WHERE papers.question = questions.q_id AND paper = ? AND q_type != 'info' ORDER BY screen, display_pos");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($question);
$result->store_result();
$row_no = $result->num_rows;
while ($result->fetch()) {
    $questions[$question] = $q_no;
    $q_no++;
}
$result->close();
if ($row_no == 0) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['latesubmission']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/loglatereassign.css" />
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src='../js/loglateassignmentinit.min.js'></script>
</head>

<body>
<div class="form-error"></div>
<form name="myform" id="myform" action="" method="post" autocomplete="off">
<?php
  // Check if the exam is still running. Re-assignment mid-exam would upset the data.
  $propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
if ($propertyObj->is_live()) {
    echo '<h1>' . $string['warning'] . '</h1><p>' . $string['msg2'] . '</p><p><input type="button" value="' . $string['ok'] . "\" class=\"cancel\" /></p>\n</body>\n</html>\n";
    exit();
}

  // Check for Random questions , and assign question number.
  // Note that we can only handle one instance of a Random question per paper, subsequent instances will refer to the firsts position.
  $result = $mysqli->prepare('SELECT q_id, option_text FROM papers, questions, options WHERE papers.question = questions.q_id'
      . " AND questions.q_id = options.o_id AND paper = ? AND q_type = 'random' ORDER BY screen, display_pos");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($random_question, $random_option);
  $result->store_result();
  while ($result->fetch()) {
      if (!isset($questions[$random_option])) {
          $questions[$random_option] = $questions[$random_question];
      }
  }
  $result->close();
  // Get any questions which have gone into log_late
  $missing = array();
  $missing_no = 0;
  $row_no = 0;
  $result = $mysqli->prepare("SELECT l.q_id, l.screen, DATE_FORMAT(l.updated,'%d/%m/%Y %T'), lm.ipaddress FROM log_late l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ? AND lm.paperID = ? AND lm.id = ? ORDER BY l.screen");
  $result->bind_param('iis', $userID, $paperID, $metadataID);
  $result->execute();
  $result->bind_result($q_id, $screen, $updated, $ipaddress);
  $result->store_result();
  $row_no = $result->num_rows;
  while ($result->fetch()) {
      $question_no = $questions[$q_id];
      $missing[$missing_no]['question_no']  = $question_no;
      $missing[$missing_no]['screen']       = $screen;
      $missing[$missing_no]['updated']      = $updated;
      $missing[$missing_no]['ipaddress']    = $ipaddress;
      $missing_no++;
  }
  $result->close();
  if ($row_no == 0) {
      $contactemail = support::get_email();
      $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
      $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }

  // Display which records are in log_late for the current student.
  $student_details = UserUtils::get_user_details($userID, $mysqli);
  echo '<p style="font-size:120%">' . $student_details['title'] . ' ' . $student_details['surname'] . ', ' . $student_details['first_names'] . "</p>\n";
  echo "<div style=\"font-size:100%; background-color:#295AAD\"><table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%\">\n";
  echo '<tr><th style="width:80px">' . $string['question'] . '</th><th style="width:70px">' . $string['screen'] . '</th><th style="width:150px">' . $string['saved'] . '</th><th>' . $string['ipaddress'] . "</th></tr>\n";
  echo "</table></div>\n";
  echo "<div style=\"height:180px; overflow-y:scroll; border:1px solid #295AAD; background-color:white; font-size:90%\"><table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%\">\n";
  foreach ($missing as $missing_question) {
      echo '<tr><td style="text-align:right; width:80px">' . $missing_question['question_no'] . '</td><td style="text-align:right; width:70px">' . $missing_question['screen'] . '</td><td style="width:150px">' . $missing_question['updated'] . '</td><td>' . $missing_question['ipaddress'] . "</td></tr>\n";
  }
  echo "</table>\n</div><br />";
  echo '<div><strong>' . $string['Reason'] . ':</strong> <span style="font-size:80%; color:#808080">' . $string['msg1'] . "</div>\n";
  echo "<div><textarea name=\"reason\" cols=\"40\" rows=\"5\" style=\"width:99%; font-family:Arial,sans-serif\"></textarea></div>\n<br />";
  echo "<div style=\"text-align:center\">\n";
  echo '<input type="submit" name="submit" id="accept" value="' . $string['accept'] . '" class="ok" />&nbsp;<input type="submit" name="submit" id="reject" value="' . $string['reject'] . '" class="ok" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="' . $string['Cancel'] . '" class="cancel" /></div>';
  echo "<input type=\"hidden\" name=\"userID\" value=\"$userID\" /><input type=\"hidden\" name=\"paperID\" value=\"$paperID\" /><input type=\"hidden\" name=\"metadataID\" value=\"$metadataID\" /><input type=\"hidden\" name=\"log_type\" value=\"" . $_GET['log_type'] . '" />';
  $mysqli->close();
    ?>
<input type="hidden" name="button_pressed" id="button_pressed" value="" />
</form>

<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
