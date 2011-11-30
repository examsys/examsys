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
<html>
<head>
<title>Textbox Marking</title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
table {font-size:100%}
.h {background-color:#F1F5FB; color:black}
a {color:blue}
</style>
<link rel="stylesheet" type="text/css" href="../css/breadcrumb.css" />
<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="JavaScript">
  function hideMarked() {
    if (document.getElementById('hidemarked').checked == 1) {
      setting = " checked";
    } else {
      setting = "";
    }

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    NameOfCookie = "hidemarked";
    document.cookie = NameOfCookie + "=" + setting +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());

    parent.body.location.href='textbox_marking.php?<?php echo $_SERVER['QUERY_STRING']; ?>';
  }
</script>
</head>

<body>
<?php
  // Get some paper properties
  $result = $mysqli->prepare("SELECT paper_type AS paper_type, paper_title FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_type, $paper);
  $result->fetch();
  $result->close();
  
  $candidate_no = 0;
  if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
    // Get how many students took the paper.
    $result = $mysqli->prepare("SELECT DISTINCT log_metadata.userID FROM (log$paper_type, log_metadata) WHERE log$paper_type.started=log_metadata.started AND log$paper_type.q_paper=log_metadata.paperID AND log$paper_type.userID=log_metadata.userID AND q_paper=? AND log_metadata.started>=? AND log_metadata.started<=? AND student_grade NOT IN ('university lecturer','University Secretary','IT Support','University Admin','Technical Staff')");
    $result->bind_param('iss', $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
    $result->bind_result($tmp_userID);
    while ($row = $result->fetch()) {
      $candidate_no++;
    }
    $result->close();
  }

  if (!isset($_GET['phase'])) {
    $phase_description = $string['finalisemarks'];
    $tmp_phase = '';
  } elseif ($_GET['phase'] == 1) {
    $phase_description = $string['primarymarking'];
    $tmp_phase = '&phase=1';
  } elseif ($_GET['phase'] == 2) {
    $phase_description = $string['secondmarking'];
    $tmp_phase = '&phase=2';
  }
  if ($candidate_no > 0) $phase_description .= " - $candidate_no " . $string['candidates'];

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

  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n<tr><td class=\"h\" style=\"height:52px\">";
  echo '<div class="breadcrumb"><a href="../staff/index.php" target="_top">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '" target="_top">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '" target="_top">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '" target="_top">' . $paper . '</a></div><div style="margin-left:10px; font-size:180%; color:black; font-weight:bold">' . $phase_description . '</div></td>';
  echo "<td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a><br /><input type=\"checkbox\" name=\"hidemarked\" id=\"hidemarked\" value=\"1\" onclick=\"hideMarked();\"";
  if (isset($_COOKIE['hidemarked']) and $_COOKIE['hidemarked'] == 'checked') echo ' checked';
  echo "  /> " . $string['hidemarked'] . "</td></tr>\n";
  echo "<tr><td colspan=\"2\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" /></td></tr>\n";
  echo "</table>\n";
?>
</body>
</html>