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
  require '../include/errors.inc';
  
  $paperID = $_GET['paperID'];
  $q_id = $_GET['q_id'];
  $startdate = $_GET['startdate'];
  $enddate = $_GET['enddate'];
  $ws = $_GET['ws'];
  $phase = $_GET['phase'];
    
  function displayMarks($id, $default, $log_record_id, $log, $halfmarks, $tmp_username) {
    global $marks_correct, $string;
    
    $html = '<select name="mark' . $id . '"><option value="NULL"></option>';
    $inc = 1;
    if ($halfmarks == true) $inc = 0.5;
    for ($i=0; $i<=$marks_correct; $i+=$inc) {
      $display_i = $i;
      if ($i == 0.5) {
        $display_i = '&#189;';
      } elseif ($i - floor($i) > 0) {
        $display_i = floor($i) . '&#189;';
      }
      if ($i == $default and is_numeric($default)) {
        $html .= "<option value=\"$i\" selected>$display_i</option>";
      } else {
        $html .= "<option value=\"$i\">$display_i</option>";
      }
    }
    $html .= '</select>&nbsp;<span style="color:black">' . $string['marks'] . '</span><br />&nbsp;<input type="hidden" name="logrec' . $id . '" value="' . $log_record_id . '"><input type="hidden" name="log' . $id . '" value="' . $log . '"><input type="hidden" name="username' . $id . '" value="' . $tmp_username . '">';
    return $html;
  }
  
  if ((isset($_POST['submit']) and $_POST['submit'] == 'Save & Exit') or (isset($_POST['continue']) and $_POST['continue'] == 'Save & Continue')) {
    $paper_type = $_POST['paper_type'];
    
    // Delete previous records from the marks table.
    $result = $mysqli->prepare("DELETE FROM textbox_marking WHERE paperID=? AND q_id=? AND phase=?");
    $result->bind_param('iii', $paperID, $q_id, $phase);
    $result->execute();
    $result->close();
    
    // Write in the new marks.
    $comments = '';
    for ($i=1; $i<=$_POST['answer_no']; $i++) {
      if ($_POST["mark$i"] != 'NULL') {
        $result = $mysqli->prepare("INSERT INTO textbox_marking VALUES (NULL, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
        $result->bind_param('iiiidsiis', $paperID, $q_id, $_POST["logrec$i"], $userID, $_POST["mark$i"], $comments, $phase, $_POST["log$i"], $_POST["username$i"]);
        $result->execute();
        $result->close();
      }
    }
   
    if (isset($_POST['submit']) and $_POST['submit'] == 'Save & Exit') {
      $mysqli->close();
      ?>
      <html>
      <body>
      <script language="JavaScript">
        window.top.location = "/reports/textbox_select_q.php?paperID=<?php echo $paperID; ?>&q_id=<?php echo $_GET['q_id']; ?>&startdate=<?php echo $_GET['startdate']; ?>&enddate=<?php echo $_GET['enddate']; ?>&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>&ws=<?php echo $ws; ?>&phase=<?php echo $phase; ?>&action=mark&repdegree=%";
      </script>
      </body>
      </html>
      <?php
      exit;
    }
  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Textbox Marking</title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
td {line-height:150%; text-align:justify}
.heading {background-color:#EBEADB; color:black}
</style>

<script src="../js/ie_fix.js" type="text/javascript"></script>
<script language="JavaScript">
  function move_in(img_name) {
    document[img_name].src=onImg.src;
  }

  function move_out(img_name) {
    document[img_name].src=offImg.src;
  }
  
  onImg = new Image;
  onImg.src = '../artwork/up_folder_icon_on.gif';
  offImg = new Image;
  offImg.src = '../artwork/up_folder_icon_off.gif';
</script>
</head>

<body style="margin:0px">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php echo $paperID; ?>&q_id=<?php echo $_GET['q_id']; ?>&startdate=<?php echo $startdate; ?>&enddate=<?php echo $enddate; ?>&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>&ws=<?php echo $ws; ?>&phase=<?php echo $phase; ?>&action=mark" method="post">
<?php
  // Get some paper properties
  if ($result = $mysqli->prepare("SELECT paper_type FROM properties WHERE property_id=?")) {
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($paper_type);
    $result->fetch();
    $result->close();
  } else {
    display_error("Properties Query Error","SELECT paper_type FROM properties WHERE property_id=$paperID");
    $mysqli->close();
  }
  
  // Get the marks for the question
  if ($result = $mysqli->prepare("SELECT marks_correct, marks_incorrect, leadin, correct_fback FROM (questions, options) WHERE questions.q_id=options.o_id AND o_id=? LIMIT 1")) {
    $result->bind_param('i', $q_id);
    $result->execute();
    $result->bind_result($marks_correct, $marks_incorrect, $leadin, $correct_fback);
    $result->fetch();
    $result->close();
  } else {
    display_error("Marks Query Error",$mysqli->close());
  }
  
  if ($phase == 2) {
    // Get the usernames of papers to second mark.
    $second_mark = array();
    
    $result = $mysqli->prepare("SELECT userID FROM textbox_remark WHERE paperID=?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($remark_userID);
    while ($row = $result->fetch()) {
      $second_mark[] = $remark_userID;
    }
    $result->close();
  }
  
  $half_marks = true; 
?>
<table cellpadding="4" cellspacing="0" border="0" style="margin-right:10px">
<?php
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT 0 AS logtype, log0.id, log0.userID, user_answer, textbox_marking.mark FROM (log0, users) LEFT JOIN textbox_marking ON log0.id=textbox_marking.answer_id AND log0.q_paper=textbox_marking.paperID AND phase=? WHERE q_paper=? AND users.roles='Student' AND users.id=log0.userID AND log0.q_id=? AND DATE_ADD(started, INTERVAL 2 MINUTE)>=? AND started<=?) UNION ALL (SELECT 1 AS logtype, log1.id, log1.userID, user_answer, textbox_marking.mark FROM (log1, users) LEFT JOIN textbox_marking ON log1.id=textbox_marking.answer_id AND log1.q_paper=textbox_marking.paperID AND phase=? WHERE q_paper=? AND users.roles='Student' AND users.id=log1.userID AND log1.q_id=? AND DATE_ADD(started, INTERVAL 2 MINUTE)>=? AND started<=?)");
    $result->bind_param('iiissiiiss', $phase, $paperID, $q_id, $startdate, $enddate, $phase, $paperID, $q_id, $startdate, $enddate);
  } else {
    $result = $mysqli->prepare("SELECT $paper_type AS logtype, log$paper_type.id, log$paper_type.userID, user_answer, textbox_marking.mark FROM (log$paper_type, users) LEFT JOIN textbox_marking ON log$paper_type.id=textbox_marking.answer_id AND log$paper_type.q_paper=textbox_marking.paperID AND phase=? WHERE q_paper=? AND users.roles='Student' AND users.id=log$paper_type.userID AND log$paper_type.q_id=? AND DATE_ADD(started, INTERVAL 2 MINUTE)>=? AND started<=?");
    $result->bind_param('iiiss', $phase, $paperID, $q_id, $startdate, $enddate);
  }
  $answer_no = 0;
  $result->execute();
  $result->store_result();
  $result->bind_result($logtype, $id, $tmp_userID, $user_answer, $student_mark);
  if ($result->num_rows == 0) {
    echo "<p>No students</p>";
  }
  while ($row = $result->fetch()) {
    if ($phase == 1 or ($phase == 2 and in_array($tmp_userID, $second_mark))) {
      $style = '';
      if (trim($user_answer) != '') {
        $answer_no++;
        if (is_numeric($student_mark)) {  // Marked previously so grey out.
          if (isset($_COOKIE['hidemarked']) and $_COOKIE['hidemarked'] == 'checked') {
            $style = ' style="display:none"';
          } else {
            $style = ' style="color:#808080"';
          }
        }
        echo "<tr" . $style . "><td style=\"vertical-align:top; text-align:right; border-bottom:1px solid #CBC7B8\">$answer_no.</td><td style=\"border-bottom:1px solid #CBC7B8\">" . nl2br($user_answer) . "<br />" . displayMarks($answer_no, $student_mark, $id, $logtype, $half_marks, $tmp_userID) . "</td></tr>\n";
      } else {
        $answer_no++;
        if (is_numeric($student_mark)) {  // Marked previously so grey out.
          if (isset($_COOKIE['hidemarked']) and $_COOKIE['hidemarked'] == 'checked') {
            $style = ' style=" display:none"';
          } else {
            $style = ' style="color:#808080"';
          }
        }
        echo "<tr" . $style . "><td style=\"vertical-align:top; text-align:right; border-bottom:1px solid #CBC7B8\">$answer_no.</td><td style=\"border-bottom:1px solid #CBC7B8; color:#C00000\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" /> No answer provided!<br />" . displayMarks($answer_no, $student_mark, $id, $logtype, $half_marks, $tmp_userID) . "</td></tr>\n";
      }
    }
  }
  $result->close();
  $mysqli->close();
?>
</table>

<div align="center"><input type="hidden" name="answer_no" value="<?php echo $answer_no; ?>" />
<input type="hidden" name="paper_type" value="<?php echo $paper_type; ?>" />
<table cellpadding="0" cellspacing="0" border="0">
<tr><td style="text-align:center; color:#808080">ALT + S</td><td style="text-align:center; color:#808080">ALT + C</td><td></td><td></td></tr>

<?php
  if ($answer_no == 0) {
    echo '<tr><td><input type="submit" name="submit" value="' . $string['saveexit'] . '" accesskey="S" style="width:160px" disabled /></td><td><input type="submit" name="continue" value="' . $string['savecontinue'] . '" accesskey="C" style="width:160px" disabled /></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><input onclick="javascript:window.top.location=\'./textbox_select_q.php?paperID=' . $_GET['paperID'] . '&startdate=' . $_GET['startdate'] . '&enddate=' . $_GET['enddate'] . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '&ws=1&phase=' . $phase . '&action=mark&repdegree=%\'" type="button" name="cancel" value="' . $string['cancel'] . '" style="width:90px" /></td></tr>';
  } else {
    echo '<tr><td><input type="submit" name="submit" value="' . $string['saveexit'] . '" accesskey="S" style="width:160px" /></td><td><input type="submit" name="continue" value="' . $string['savecontinue'] . '" accesskey="C" style="width:160px" /></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><input onclick="javascript:window.top.location=\'./textbox_select_q.php?paperID=' . $_GET['paperID'] . '&startdate=' . $_GET['startdate'] . '&enddate=' . $_GET['enddate'] . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '&ws=1&phase=' . $phase . '&action=mark&repdegree=%\'" type="button" name="cancel" value="' . $string['cancel'] . '" style="width:90px" /></td></tr>';
  }
?>
</table>
</div>
</form>
</body>
</html>
