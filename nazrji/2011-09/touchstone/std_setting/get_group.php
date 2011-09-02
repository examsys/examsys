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

require '../include/staff_auth.inc';
require '../include/std_set_shared_functions.inc';
$paperID = $_GET['paperID'];

// Get any questions to exclude.
$exclude = array();
$exclude_query = $mysqli->query("SELECT q_id, parts FROM question_exclude WHERE q_paper=$paperID");
while ($row = $exclude_query->fetch_assoc()) {
  $exclude[$row['q_id']] = $row['parts'];
}
$exclude_query->close();

// Calculate marks for the current paper.
$marks_array = array();
ss_get_marks_correct($mysqli, $paperID, $exclude, $marks_array);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<title>TouchStone: List Settings</title>

<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; color:black; padding:0;margin-top:0px; margin-left:0px; margin-right:0px}
table {font-size:100%}
.heading {background-color:#EBEADB; color:black}
</style>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
</head>

<body>
	<form action="group_set_angoff.php" method="post">	
	
<?php
if (isset($_GET['module'])) {
  $module = $_GET['module'];
} else {
  $module = '';
}

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

// Get how many screens make up the question paper.
$screen_data = array();
$result = $mysqli->prepare("SELECT DISTINCT paper_title, paper_type, total_mark, paper_prologue, marking, screen, leadin, start_date, end_date, bgcolor, fgcolor, themecolor, labelcolor, bidirectional FROM (properties, papers, questions) WHERE papers.question=questions.q_id AND properties.property_id=papers.paper AND paper=? ORDER BY screen, p_id");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($paper_title, $paper_type, $total_marks, $paper_prologue, $marking, $screen, $leadin, $start_date, $end_date, $bgcolor, $fgcolor, $themecolor, $labelcolor, $bidirectional);
while ($row = $result->fetch()) {
  $no_screens = strval($screen);
  if (isset($screen_data[$no_screens])) {
    $screen_data[$no_screens] += 1;
  } else {
    $screen_data[$no_screens] = 1;
  }
}
$result->close();

echo "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
echo "<tr><td style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../index.php\">Home</a>";
if ($folder != '') {
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder='.$folder.'">'.$folder_name.'</a>';
} elseif (isset($_GET['module']) and $_GET['module'] != '') {
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module='.$_GET['module'].'">'.$_GET['module'].'</a>';
}
echo "&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../paper/details.php?paperID=$paperID&module=$module&folder=$folder\">$paper_title</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./index.php?paperID=$paperID&module=$module&folder=$folder\">Standards Setting</a></div>";
$helpID = 98;
echo '<div style="font-family:Arial,sans-serif; font-size:200%; color:black; font-weight:bold; margin-left:10px">Select Reviewers</div>';
echo "</td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp($helpID); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
echo "</table>\n";

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td style="width:18px; background-color:#F1F5FB">&nbsp;</td>
	<td style="background-color:#F1F5FB; width:20%"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Standard Setter&nbsp;</td>
	<td style="background-color:#F1F5FB; width:15%"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Date&nbsp;</td>
	<td style="background-color:#F1F5FB; width:8%"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Pass Score</td>
	<td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Method</td>
	<td width="25%" style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp</td>
</tr>
<tr style="height:4px"><td valign="top" colspan="6"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
<?php
$reviews = get_reviews($mysqli, 'group', $paperID, $total_marks);
foreach($reviews as $review) {
  if($review['group_review'] == 'No') {
    echo "<tr><td align=\"center\"><input type=\"checkbox\" name=\"member{$review['review_no']}\" value=\"{$review['setter_id']},{$review['date']}\" checked /></td><td>&nbsp;{$review['name']}</td><td>&nbsp;{$review['display_date']}</td><td style=\"text-align:right\">{$review['pass_score']}%&nbsp;</td><td>&nbsp;{$review['method']}</td><td></td></tr>\n";
  }
}
$mysqli->close();
echo "<table>\n";
?>
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<br /><p><input type="submit" name="submit" style="width:100px" value="Review" /></p>
</form>
</body>
</html>
