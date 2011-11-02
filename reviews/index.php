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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<style>
body {font-size:90%; margin:0px; background-color:white; color:black; font-family:Arial,sans-serif}
p {line-height:150%}
</style>
<title><?php echo $string['externalexaminerarea']; ?></title>
<script language="JavaScript">
  function startPaper(paperID, fullsc) {
    var winwidth = screen.width-80;
    var winheight = screen.height-80;
    if (fullsc == 0) {
      window.open("start.php?id="+paperID+"","paper","width="+winwidth+",height="+winheight+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    } else {
      window.open("start.php?id="+paperID+"","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  }

  function launchHelp() {
    var winheight = screen.height-100;
    var winwidth = screen.width-100;
    notice = window.open("../help/student/index.php","help","width=" + winwidth + ",height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
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
<td><div style="padding-left:15px"><img src="../artwork/rogo_logo.gif" width="137" height="61" alt="logo" border="0" style="margin-top:2px" /></div><div style="padding-left:15px; font-size:90%; font-weight:bold"><?php echo $string['externalexamineraccess']; ?> (<?php echo $title . ' ' . $initials . ' ' . $surname; ?>)</div></td>
<td align="right"><img src="../artwork/black_uon_logo.png" width="167" height="70" alt="The University of Nottingham" border="0" /></td>
</tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>

<p style="font-size:130%; font-weight:bold; margin-left:15px"><?php echo $string['instructions']; ?></p>
<p style="margin-left:15px; margin-right:15px; text-align:justify"><?php echo $string['msg1']; ?></p>

<p style="margin-left:15px; margin-right:15px; text-align:justify"><?php echo $string['msg2']; ?></p>
<p style="margin-left:15px; font-weight:bold"><?php echo $string['yourpapersforreview']; ?></p>
<table cellpadding="0" cellspacing="2" border="0" style="margin-left:10px; font-size:90%">
<?php
  $result = $mysqli->prepare("SELECT paper_type, paper_title, property_id, bidirectional, fullscreen, MAX(screen) AS max_screen, DATE_FORMAT(external_review_deadline,'%Y%m%d') AS external_review_deadline, DATE_FORMAT(external_review_deadline,'$cfg_short_date') AS display_deadline, crypt_name FROM (properties, papers) WHERE deleted IS NULL AND DATE_ADD(start_date, INTERVAL 1 WEEK) > NOW() AND properties.property_id=papers.paper AND externals LIKE '%$userID%' GROUP BY paper");
  $result->execute();
  $result->store_result();
  $result->bind_result($paper_type, $paper_title, $property_id, $bidirectional, $fullscreen, $max_screen, $external_review_deadline, $display_deadline, $crypt_name);
  while ($result->fetch()) {
    $reviewed = '';
    $log_results = $mysqli->prepare("SELECT DATE_FORMAT(MAX(reviewed),'$cfg_long_date_time') AS started FROM review_comments WHERE reviewer=$userID and q_paper=?");
    $log_results->bind_param('i', $property_id);
    $log_results->execute();
    $log_results->store_result();
    $log_results->bind_result($reviewed);
    $log_results->fetch();
    $log_results->close();
    $restartdate = '';
    echo "<tr><td align=\"center\"><a href=\"#\" onclick=\"startPaper('$crypt_name',$fullscreen); return false;\"><img src=\"../artwork/summative.png\" width=\"48\" height=\"48\" alt=\"Paper Icon\" border=\"0\" /></a></td>\n";
    echo "  <td><a href=\"#\" onclick=\"startPaper('$crypt_name',$fullscreen); return false;\">$paper_title</a><br /><div style=\"color:#C00000\">" . $string['deadline'] . " ";
    if (date("Ymd") > $external_review_deadline) {
      printf($string['expired'], $cfg_company);
    } else {
      if ($display_deadline == '00/00/0000') {
        echo $string['notset'];
      } else {
        echo $display_deadline;
      }
    }
    echo '</div>';
    if ($reviewed == '') {
      echo '<span style="color:white; background-color:red; padding-left:5px; padding-right:5px">' . $string['notreviewed'] . '</span>';
    } else {
      echo '<span style="color:#808080">' . sprintf($string['reviewed'], $reviewed) . '</span>';
    }
    echo "</td></tr>\n<tr><td colspan=\"2\" style=\"font-size:80%\">&nbsp;</td>\n</tr>\n";
  }
  
  if ($result->num_rows == 0) {
    echo "<tr><td colspan=\"2\"><p style=\"color:red\">" . $string['nopapersfound'] . "</p></td></tr>\n";
  }
  $result->close();
  echo "</td></tr>\n<tr><td colspan=\"2\">&nbsp;</td></tr>\n<tr><td colspan=\"2\" style=\"text-align:left\"><hr noshade=\"noshade\" align=\"left\" style=\"text-align:left; background-color:#C0C0C0; color:#C0C0C0; height:1px; border:0; width:400px\" /></td>\n</tr>\n";
  echo "<tr><td width=\"66\" style=\"text-align:center\"><a href=\"#\" onclick=\"launchHelp(); return false;\"><img src=\"../artwork/help_icon_48.png\" width=\"48\" height=\"48\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></td>\n</td><td><a href=\"#\" onclick=\"launchHelp(); return false;\">" . $string['helpandsupport'] . "</a><br /><span style=\"color:#808080\">" . $string['onlinesupportsystem'] . "</span></td></tr>\n";
  
  echo "<tr><td>&nbsp;</td><td style=\"font-size:80%\">&nbsp;</td></tr>\n";
  echo "<tr><td width=\"66\" style=\"text-align:center\"><a href=\"mailto:$support_email\"><img src=\"../artwork/email_icon_48.png\" width=\"48\" height=\"48\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></td>\n</td><td><a href=\"mailto:$support_email\">$support_email</a><br /><span style=\"color:#808080\">Help and support for external examiners reviewing papers in Rogō.</span></td></tr>\n";
  
  echo "<tr><td>&nbsp;</td><td style=\"font-size:80%\">&nbsp;</td></tr>\n";
  echo "<tr><td width=\"66\" style=\"text-align:center\"><a href=\"mailto:$support_email\"><img src=\"../artwork/osi_logo.png\" width=\"56\" height=\"66\" alt=\"Open Source Initiative\" border=\"0\" /></a></td>\n</td><td><span style=\"color:#808080\">Rogō $ts_version is an open source e-assessment system lead by Information Services at the University of Nottingham.<br />For further details about Rogo please see the project website:</a> <a href=\"https://suivarro.nottingham.ac.uk/trac/rogo/\">suivarro.nottingham.ac.uk/trac/rogo/</a></td></tr>\n";
  $mysqli->close();
?>

</table>
<br />&nbsp;<br />

<div style="margin-left:10px; font-size:80%; color:#808080"><?php printf($string['copyrightmsg'], $cfg_company); ?>.</div>

</body>
</html>