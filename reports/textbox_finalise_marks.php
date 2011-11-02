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

require '../include/staff_auth.inc';

$paperID = $_GET['paperID'];

function displayMarks($id) {
  global $marks_correct;
  $html = '<select name="override' . $id . '"><option value="NULL"></option>';
  $inc = 0.5;
  for ($i=0; $i<=$marks_correct; $i+=$inc) {
    $display_i = $i;
    if ($i == 0.5) {
      $display_i = '&#189;';
    } elseif ($i - floor($i) > 0) {
      $display_i = floor($i) . '&#189;';
    }
    $html .= "<option value=\"$i\">$display_i</option>";
  }
  $html .= '</select>';
  return $html;
}

if (isset($_POST['submit'])) {
  for ($i=1; $i<$_POST['student_no']; $i++) {
    if (isset($_POST["override$i"]) and $_POST["override$i"] != 'NULL') {
      $tmp_mark = $_POST["override$i"];
    } else {
      $tmp_mark = $_POST["mark$i"];
    }
    $logtype = $_POST["logtype$i"];
    $log_id = $_POST["log_id$i"];

    $result = $mysqli->prepare("UPDATE log$logtype SET mark=? WHERE id=?");
    $result->bind_param('di', $tmp_mark, $log_id);
    $result->execute();
    $result->close();
  }
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/paper/details.php?paperID=" . $_GET['paperID'] . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder']);
} else {
  $q_id = $_GET['q_id'];
  $startdate = $_GET['startdate'];
  $enddate = $_GET['enddate'];

  // Get some paper properties
  $result = $mysqli->prepare("SELECT paper_type, paper_title FROM properties WHERE property_id=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_type, $paper);
  $result->fetch();
  $result->close();

  // Get primary marks
  $primary_marks = array();
  $result = $mysqli->prepare("SELECT answer_id, mark FROM textbox_marking WHERE paperID=? AND q_id=? AND phase=1");
  $result->bind_param('ii', $paperID, $q_id);
  $result->execute();
  $result->bind_result($answer_id, $mark);
  while ($result->fetch()) {
    $primary_marks[$answer_id] = $mark;
  }
  $result->close();

  // Get secondary marks
  $secondary_marks = array();
  $result = $mysqli->prepare("SELECT answer_id, mark FROM textbox_marking WHERE paperID=? AND q_id=? AND phase=2");
  $result->bind_param('ii', $paperID, $q_id);
  $result->execute();
  $result->bind_result($answer_id, $mark);
  while ($result->fetch()) {
    $secondary_marks[$answer_id] = $mark;
  }
  $result->close();

  // Get some paper properties
  $result = $mysqli->prepare("SELECT marks_correct FROM options WHERE o_id=?");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->bind_result($marks_correct);
  $result->fetch();
  $result->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['finalisemarks'] . ' ' . $cfg_install_type; ?></title>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
table {font-size:100%}
.h {background-color:#F1F5FB; color:black}
.breadcrumb {margin-left:10px; font-size:90%}
.breadcrumb a:link {color:blue; text-decoration:none; cursor:pointer}
.breadcrumb a:visited {color:blue; text-decoration:none; cursor:pointer}
.breadcrumb a:hover {color:blue; text-decoration:underline; cursor:pointer}
</style>
</head>

<body>
<?php
  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }

  echo "<form action=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder'] . "\" method=\"post\">\n";
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n<tr><td class=\"h\" colspan=\"3\">";
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div></td><td class="h" style="text-align:right; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(0); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td></tr>';

  echo '<tr><td class="h"><div style="margin-left:10px; font-size:180%; color:black; font-weight:bold">' . $string['finalisemarks'] . '</div></td><td class="h" style="text-align:center; vertical-align:bottom">1st</td><td class="h" style="text-align:center; vertical-align:bottom">2nd</td><td class="h" style="text-align:center; vertical-align:bottom">Override</td></tr>';
  echo "<tr style=\"height:4px\"><td colspan=\"4\" valign=\"top\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
  
  $student_no = 1;
  $marked_no = 0;

  // Get student answers
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT 0 AS logtype, log0.id, log0.userID, user_answer FROM (log0, users) WHERE q_paper=? AND users.roles='Student' AND users.id=log0.userID AND log0.q_id=? AND DATE_ADD(started, INTERVAL 2 MINUTE)>=? AND started<=?) UNION ALL (SELECT 1 AS logtype, log1.id, log1.userID, user_answer FROM (log1, users) WHERE q_paper=? AND users.roles='Student' AND users.id=log1.userID AND log1.q_id=? AND DATE_ADD(started, INTERVAL 2 MINUTE)>=? AND started<=?)");
    $result->bind_param('iissiiss', $paperID, $q_id, $startdate, $enddate, $paperID, $q_id, $startdate, $enddate);
  } else {
    $result = $mysqli->prepare("SELECT $paper_type AS logtype, log$paper_type.id, log$paper_type.userID, user_answer FROM (log$paper_type, users) WHERE q_paper=? AND users.roles='Student' AND users.id=log$paper_type.userID AND log$paper_type.q_id=? AND DATE_ADD(started, INTERVAL 2 MINUTE)>=? AND started<=?");
    $result->bind_param('iiss', $paperID, $q_id, $startdate, $enddate);
  }
  $result->execute();
  $result->bind_result($logtype, $log_id, $tmp_userID, $user_answer);
  while ($result->fetch()) {
    if (trim($user_answer) != '') {
      if (isset($secondary_marks[$log_id]) and abs($primary_marks[$log_id] - $secondary_marks[$log_id]) > 1) {
        echo "<tr><td style=\"padding-left:10px; padding-right:10px; vertical-align:top; border-bottom:1px solid #C0C0C0\">" . nl2br($user_answer) . "<br />&nbsp;</td><td style=\"text-align:right; border-bottom:1px solid #CBC7B8; border-left:1px solid #CBC7B8; background-color:#FFC0C0; font-weight:bold\">" . $primary_marks[$log_id] . "&nbsp;<input type=\"radio\" name=\"mark$student_no\" value=\"" . $primary_marks[$log_id] . "\" checked /></td><td style=\"text-align:right; border-bottom:1px solid #CBC7B8; border-left:1px solid #CBC7B8; border-right:1px solid #CBC7B8; background-color:#FFC0C0; font-weight:bold\">" . $secondary_marks[$log_id] . "&nbsp;<input type=\"radio\" name=\"mark$student_no\" value=\"" . $secondary_marks[$log_id] . "\" /><input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /></td><td style=\"text-align:right; border-bottom:1px solid #CBC7B8; background-color:#FFC0C0\">" . displayMarks($student_no);
      } else {
        if (isset($primary_marks[$log_id])) {
          echo "<tr><td style=\"padding-left:10px; padding-right:10px; vertical-align:top; border-bottom:1px solid #C0C0C0\">" . nl2br($user_answer) . "<br />&nbsp;</td><td style=\"text-align:right; border-bottom:1px solid #CBC7B8; border-left:1px solid #CBC7B8; width:50px\">" . $primary_marks[$log_id] . "&nbsp;<input type=\"radio\" name=\"mark$student_no\" value=\"" . $primary_marks[$log_id] . "\" checked /></td>";
          $marked_no++;
        } else {
          echo "<tr><td style=\"padding-left:10px; padding-right:10px; vertical-align:top; border-bottom:1px solid #C0C0C0\">" . nl2br($user_answer) . "<br />&nbsp;</td><td style=\"text-align:right; border-bottom:1px solid #CBC7B8; border-left:1px solid #CBC7B8; width:50px; background-color:#EEEEEE; color:#800000\">&lt;unmarked&gt;</td>";
        }
        if (isset($secondary_marks[$log_id])) {
          echo "<td style=\"text-align:right; border-bottom:1px solid #CBC7B8; border-left:1px solid #C0C0C0; border-right:1px solid #C0C0C0\">" . $secondary_marks[$log_id] . "&nbsp;<input type=\"radio\" name=\"mark$student_no\" value=\"" . $secondary_marks[$log_id] . "\" /></td>";
        } else {
          echo "<td style=\"text-align:right; border-bottom:1px solid #C0C0C0; border-left:1px solid #C0C0C0; border-right:1px solid #C0C0C0; width:50px; background-color:#EEEEEE\">&nbsp;</td>";
        }
        echo "<td style=\"text-align:right; border-bottom:1px solid #C0C0C0\">" . displayMarks($student_no);
      }
    } else {
      if (!isset($primary_marks[$log_id])) $primary_marks[$log_id] = '';
      if (!isset($secondary_marks[$log_id])) $secondary_marks[$log_id] = '';
      echo "<tr><td style=\"padding-left:10px; padding-right:10px; vertical-align:top; border-bottom:1px solid #C0C0C0; color:#C00000; font-weight:bold\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" />&nbsp;" . $string['noanswer'] . "<br />&nbsp;</td><td style=\"text-align:right; border-bottom:1px solid #C0C0C0; border-left:1px solid #C0C0C0; background-color:#FFC0C0; font-weight:bold\">" . $primary_marks[$log_id] . "&nbsp;<input type=\"radio\" name=\"mark$student_no\" value=\"" . $primary_marks[$log_id] . "\" checked /></td><td style=\"text-align:right; border-bottom:1px solid #C0C0C0; border-left:1px solid #C0C0C0; border-right:1px solid #C0C0C0; background-color:#FFC0C0; font-weight:bold\">" . $secondary_marks[$log_id] . "&nbsp;<input type=\"radio\" name=\"mark$student_no\" value=\"" . $secondary_marks[$log_id] . "\" /><input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /></td><td style=\"text-align:right; border-bottom:1px solid #C0C0C0; background-color:#FFC0C0\">" . displayMarks($student_no);
    }
    echo "<input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /><input type=\"hidden\" name=\"logtype$student_no\" value=\"$logtype\" /></td></tr>\n";
    $student_no++;
  }
  $result->close();
?>
</table>
<br />
<div style="text-align:center">
<input type="hidden" name="student_no" value="<?php echo $student_no; ?>" />
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<?php
  if ($marked_no == 0) {
    echo '<input type="submit" name="submit" value="' . $string['finalisemarks'] . '" disabled />';
  } else {
    echo '<input type="submit" name="submit" value="' . $string['finalisemarks'] . '" />';
  }
?>
</div>
</form>
</body>
</html>

<?php
}
?>