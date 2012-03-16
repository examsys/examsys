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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['latesubmission']. ' ' . $cfg_install_type; ?></title>
  <style type="text/css">
  body {font-size:90%; background-color:#F1F5FB; color:black; font-family:Arial,sans-serif; margin:4px}
  </style>

  <script type="text/javascript">
    function confirmIntention() {
      if (document.myform.button_pressed.value == 'Accept') {
        var agree = confirm("<?php echo $string['msg3']; ?>");
        if (agree) {
          return true;
        } else {
          return false;
        }
      } else if (document.myform.button_pressed.value == 'Reject') {
        var agree = confirm("<?php echo $string['msg4']; ?>");
        if (agree) {
          return true;
        } else {
          return false;
        }
      }
    }
  </script>
</head>

<body>
<form name="myform" action="do_reassign_log_late.php" method="post" onsubmit="return confirmIntention();">
<?php
  // Check if the exam is still running. Re-assignment mid-exam would upset the data.
  $result = $mysqli->prepare("SELECT UNIX_TIMESTAMP(end_date) FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($end_date);
  $result->fetch();
  $result->close();

  if (time() < $end_date) {
    echo "<p><strong>" . $string['warning'] . "</strong><p><p>" . $string['msg2'] . "</p>\n";
    exit;
  }

  // Get details of the student.
  $questions = array();
  $q_no = 1;
  $result = $mysqli->prepare("SELECT title, surname, first_names FROM users WHERE id=? LIMIT 1");
  $result->bind_param('i', $_GET['userID']);
  $result->execute();
  $result->bind_result($title, $surname, $first_names);
  $result->fetch();
  $result->close();
  
  // Get the order of the questions on the paper.
  $questions = array();
  $q_no = 1;
  $result = $mysqli->prepare("SELECT question FROM papers, questions WHERE papers.question=questions.q_id AND paper=? AND q_type != 'info' ORDER BY screen, display_pos");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($question);
  while ($result->fetch()) {
    $questions[$question] = $q_no;
    $q_no++;
  }
  $result->close();
  
  // Get any the questions which have gone into log_late
  $missing = array();
  $missing_no = 0;
  $result = $mysqli->prepare("SELECT q_id, log_late.screen, DATE_FORMAT(updated,'%d/%m/%Y %T'), ipaddress FROM (log_late, log_metadata) WHERE log_late.userID=log_metadata.userID AND log_late.q_paper=log_metadata.paperID AND log_late.started=log_metadata.started AND log_late.userID=? AND q_paper=? AND log_late.started=? ORDER BY screen");
  $result->bind_param('iis', $_GET['userID'], $_GET['paperID'], $_GET['started']);
  $result->execute();
  $result->bind_result($q_id, $screen, $updated, $ipaddress);
  while ($result->fetch()) {
    $question_no = $questions[$q_id];
    $missing[$missing_no]['question_no'] = $question_no; 
    $missing[$missing_no]['screen'] = $screen;
    $missing[$missing_no]['updated'] = $updated;
    $missing[$missing_no]['ipaddress'] = $ipaddress;
    $missing_no++;
  }
  $result->close();
  
  // Display which records are in log_late for the current student.
  echo "<p><strong>$title $surname, $first_names</strong></p>\n";
  
  echo "<div style=\"font-size:100%\"><table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%\">\n";
  echo "<tr style=\"font-weight:bold\"><td style=\"width:80px\">" . $string['question'] . "</td><td style=\"width:70px\">" . $string['screen'] . "</td><td style=\"width:150px\">" . $string['saved'] . "</td><td>" . $string['ipaddress'] . "</td></tr>\n";
  echo "</table></div>\n";
  
  
  echo "<div style=\"height:180px; overflow-y:scroll; border:1px solid #CCD9EA; background-color:white; font-size:90%\"><table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%\">\n";
  foreach ($missing as $missing_question) {
    echo "<tr><td style=\"text-align:right; width:80px\">" . $missing_question['question_no'] . "</td><td style=\"text-align:right; width:70px\">" . $missing_question['screen'] . "</td><td style=\"width:150px\">" . $missing_question['updated'] . "</td><td>" . $missing_question['ipaddress'] . "</td></tr>\n";
  }
  echo "</table>\n</div><br />";
  echo "<div><strong>Reason:</strong> <span style=\"font-size:80%; color:#808080\">" . $string['msg1'] . "</div>\n";
  echo "<div><textarea name=\"reason\" cols=\"40\" rows=\"3\" style=\"width:100%; font-family:Arial,sans-serif\"></textarea></div>\n<br />";
  echo "<div style=\"text-align:center\">\n";
  
  echo "<input type=\"submit\" name=\"submit\" value=\"" . $string['accept'] . "\" onclick=\"document.myform.button_pressed.value='Accept';\" style=\"width:100px\" />&nbsp;<input type=\"submit\" name=\"submit\" value=\"" . $string['reject'] . "\" onclick=\"document.myform.button_pressed.value='Reject';\" style=\"width:100px\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" name=\"cancel\" value=\"Cancel\" style=\"width:100px\" onclick=\"window.close();\" /></div>";
  echo "<input type=\"hidden\" name=\"userID\" value=\"" . $_GET['userID'] . "\" /><input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" /><input type=\"hidden\" name=\"started\" value=\"" . $_GET['started'] . "\" /><input type=\"hidden\" name=\"log_type\" value=\"" . $_GET['log_type'] . "\" />";

  $mysqli->close();
?>
<input type="hidden" name="button_pressed" value="" />
</form>
</body>
</html>