<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_student_auth.inc';
  if (strpos($userroles,'Staff') === false and strpos($userroles,'External Examiner') === false) {
    access_denied('<strong>Login Failure</strong><br />sorry access denied.',true);
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<style>
body {font-size:90%; margin:0px; background-color:white; color:black; font-family:Arial,sans-serif}
p {line-height:150%}
</style>
<title>External Examiner Area</title>
<script language="JavaScript">
  function startPaper(paperID,fullsc) {
    var winwidth = screen.width-80;
    var winheight = screen.height-80;
    if (fullsc == 0) {
      window.open("start.php?paperID="+paperID+"","paper","width="+winwidth+",height="+winheight+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    } else {
      window.open("start.php?paperID="+paperID+"","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  }

  function launchHelp() {
    var winheight = screen.height-100;
    var winwidth = screen.width-100;
    notice = window.open("../student_help/index.php","help","width=" + winwidth + ",height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
    notice.moveTo(10,10);
    if (window.focus) {
      notice.focus();
    }
  }
</script>
</head>

<body>

<table cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#F1F5FB">
<tr>
<td><div style="padding-left:6px; font-size:200%; font-weight:bold">TouchStone <?php echo $ts_version; ?></div><div style="padding-left:6px; font-size:90%; font-weight:bold">External Examiner Access (<?php echo $title . ' ' . $initials . ' ' . $surname; ?>)</div></td>
<td align="right"><img src="../artwork/black_uon_logo.png" width="167" height="70" alt="The University of Nottingham" border="0" /></td>
</tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>

<p style="font-size:130%; font-weight:bold; margin-left:10px">Instructions</p>
<p style="margin-left:10px; margin-right:10px; text-align:justify">Below is a list of exam papers requiring review. After clicking on their titles the assessment will launch in a new window. Beneath each question are three buttons for you to rate the question: 1) <span style="background-color:#C0FFC0; background-image:url('../artwork/ok_background.png'); background-repeat:repeat-x; color:#004000">&nbsp;Question&nbsp;OK&nbsp;</span> (Default), 2) <span style="background-color:#FFFFC0; background-image:url('../artwork/minor_background.png'); background-repeat:repeat-x; color:#404000">&nbsp;Minor/some&nbsp;problems&nbsp;</span>, or 3) <span style="background-color:#FFC0C0; background-image:url('../artwork/major_background.png'); background-repeat:repeat-x; color:#800000">&nbsp;Major/several&nbsp;problems&nbsp;</span>. There is also a textbox to directly record your feedback to us where you feel there are points to raise.</p>

<p style="margin-left:10px; margin-right:10px; text-align:justify">Navigation buttons for moving between screens can be found at the bottom of each screen. The correct answers are displayed for all questions in this review mode (students will receive unanswered papers). You may launch a paper any number of times. Comments will be saved automatically when you navigate between screens and click 'Finish'.</p>
<p style="margin-left:10px; font-weight:bold">Your papers for review include:</p>
<table cellpadding="0" cellspacing="2" border="0" style="margin-left:10px; font-size:90%">
<?php
  $query_string = "SELECT paper_type, paper_title, property_id, bidirectional, fullscreen, MAX(screen) AS max_screen, DATE_FORMAT(external_review_deadline,'%Y%m%d') AS external_review_deadline, DATE_FORMAT(external_review_deadline,'%d/%m/%Y') AS display_deadline FROM (properties, papers) WHERE deleted IS NULL AND DATE_ADD(start_date, INTERVAL 1 WEEK) > NOW() AND properties.property_id=papers.paper AND externals LIKE '%$userID%' GROUP BY paper";
  $result = $mysqli->query($query_string);
  while ($row = $result->fetch_assoc()) {
    $log_string = "SELECT DATE_FORMAT(MAX(reviewed),'%d/%m/%Y %T') AS started FROM review_comments WHERE reviewer=$userID and q_paper=" . $row['property_id'];
    $log_results = $mysqli->query($log_string);
    $reviewed = '';
    $restartdate = '';
    while ($log_row = $log_results->fetch_assoc()) {
      $reviewed = $log_row['started'];
    }
    $log_results->close();
    echo "<tr><td align=\"center\"><a href=\"#\" onclick=\"startPaper('" . $row['property_id'] . "'," . $row['fullscreen'] . "); return false;\"><img src=\"../artwork/summative.png\" width=\"48\" height=\"48\" alt=\"Paper Icon\" border=\"0\" /></a></td>\n";
    echo "  <td><a href=\"#\" onclick=\"startPaper('" . $row['property_id'] . "'," . $row['fullscreen'] . "); return false;\">" . $row['paper_title'] . "</a><br /><div style=\"color:#C00000\">Deadline: ";
    if (date("Ymd") > $row['external_review_deadline']) {
      echo '&lt;expired&gt; - you may still view the paper and read University of Nottingham actions/reponses to your comments.';
    } else {
      if ($row['display_deadline'] == '00/00/0000') {
        echo '&lt;not set&gt;';
      } else {
        echo $row['display_deadline'];
      }
    }
    echo "</div>";
    if ($reviewed == '') {
      echo "<span style=\"color:white; background-color:red\">&nbsp;Not Reviewed&nbsp;</span>";
    } else {
      echo "<span style=\"color:#808080\">Reviewed: $reviewed</span>";
    }
    echo "</td></tr>\n<tr><td colspan=\"2\" style=\"font-size:80%\">&nbsp;</td>\n</tr>\n";
  }
  
  if ($result->num_rows == 0) {
    echo "<tr><td colspan=\"2\"><p style=\"color:red\">No papers found!</p></td></tr>\n";
  }
  $result->close();
  echo "</td></tr>\n<tr><td colspan=\"2\" style=\"text-align:left\"><hr noshade=\"noshade\" style=\"text-align:left; background-color:#C0C0C0; color:#C0C0C0; height:1px; border:0; width:400px\" /></td>\n</tr>\n";
  echo "<tr><td width=\"66\" style=\"text-align:center\"><a href=\"#\" onclick=\"launchHelp(); return false;\"><img src=\"../artwork/help_icon_48.png\" width=\"48\" height=\"48\" alt=\"Help Icon\" border=\"0\" /></a></td>\n</td><td><a href=\"#\" onclick=\"launchHelp(); return false;\">Help and Support</a><br /><span style=\"color:#808080\">Online support system for students</span></td></tr>\n";
  
  echo "<tr><td>&nbsp;</td><td style=\"font-size:80%\">&nbsp;</td></tr>\n";
  echo "<tr><td width=\"66\" style=\"text-align:center\"><a href=\"mailto:$support_email\"><img src=\"../artwork/email_icon_48.png\" width=\"48\" height=\"48\" alt=\"Email Icon\" border=\"0\" /></a></td>\n</td><td><a href=\"mailto:$support_email\">$support_email</a></td></tr>\n";
  $mysqli->close();
?>

</table>
<br />&nbsp;<br />

<div style="margin-left:10px; font-size:80%; color:#808080">Questions held within TouchStone are protected by UK copyright law and are held by The University of Nottingham.</div>

</body>
</html>