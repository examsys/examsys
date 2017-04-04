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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
require '../include/errors.php';
require '../include/year_tabs.inc';

$current_year = check_var('calyear', 'GET', true, false, true);
$schoolID = check_var('school', 'GET', true, false, true);

$date_range = " AND start_date > {$current_year}0901000000 AND end_date <= " . ($current_year + 1) . "0831235959";  // Start and end within year			
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

	<title>Rog&#333;: <?php echo $string['feedbackstats'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/statistics.css" />
	<link rel="stylesheet" type="text/css" href="../css/tabs.css" />
	
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">
<div class="head_title">
<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../admin/index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../statistics/index.php"><?php echo $string['statistics'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="summative_feedback.php?calyear=<?php echo $_GET['calyear']; ?>"><?php echo $string['summativeexamfeedback'] ?></a></div>
<div class="page_title"><?php
	echo $string['feedbackstats'] . ': <span style="font-weight:normal">';
	switch ($_GET['type']) {
		case 1:
			echo $string['objectivefeedback'];
			break;
		case 2:
			echo $string['questionfeedback'];
			break;
		case 3:
			echo $string['cohortperformance'];
			break;
	}
	
	$extra = "&school=$schoolID&type=" . $_GET['type'];
  ?></span></div>
</div>

<table class="header">
<tr>
<th style="text-align:right" colspan="2"><div style="text-align:right; vertical-align:bottom"><?php echo drawTabs($current_year, 'academic', $extra); ?></div></th>
</tr>
<tr><td colspan="2" style="border:0px; background-color:#1E3C7B; height:5px"></td></tr>
</table>

<table class="stats">
<tr>
<th><?php echo $string['paper'] ?></th>
<th><?php echo $string['feedbackreleased'] ?></th>
<th><?php echo $string['students'] ?></th>
<th><?php echo $string['feedbackviewed'] ?></th>
</tr>
<?php
$moduleIDs = get_modules($schoolID, $mysqli);
if (count($moduleIDs) > 0) {
	$papers = get_papers_for_school($date_range, $moduleIDs, $mysqli);

	get_feedback_release_dates($date_range, $moduleIDs, $papers, $mysqli);

	count_feedback_views($papers, $mysqli);
	
	foreach ($papers as $paperID=>$paper_details) {
		echo "<tr><td><a href=\"../paper/details.php?paperID=$paperID\">" . $paper_details['paper_title'] . "</a></td>";
		if (isset($paper_details['feedback_date'])) {
			echo "<td class=\"n\">" . $paper_details['feedback_date'] . "</td>";
		} else {
			echo "<td></td>";
		}
		echo "<td class=\"n\">" . count_attempts($paperID, $mysqli) . "</td>";
		if (isset($paper_details['feedback_views'])) {
			echo "<td class=\"n\">" . $paper_details['feedback_views'] . "</td>";
		} else {
			echo "<td class=\"n grey\">0</td>";
		}
		echo "</tr>\n";
	}
}

function count_attempts($paperID, $db) {
	$sql = "SELECT COUNT(log_metadata.id) FROM log_metadata, users WHERE log_metadata.userID = users.id and roles IN ('student', 'graduate') AND paperID = ?";
	$result = $db->prepare($sql);
	$result->bind_param('i', $paperID);
	$result->execute();
	$result->bind_result($attempts);
	$result->fetch();
	$result->close();
	
	return $attempts;
}

function count_feedback_views(&$papers, $db) {
	$sql = "SELECT page, COUNT(DISTINCT userID) FROM access_log WHERE type = 'Objectives-based feedback report' GROUP BY page";
	$result = $db->prepare($sql);
	$result->execute();
	$result->bind_result($page, $views);
	while ($result->fetch()) {
	  if (isset($papers[$page])) {
		  $papers[$page]['feedback_views'] = $views;
		}
	}
	$result->close();
}

function get_modules($schoolID, $db) {
	$moduleIDs = array();
	
	$result = $db->prepare("SELECT id FROM modules WHERE schoolid = ? AND active = 1 AND mod_deleted IS NULL");
	$result->bind_param('i', $schoolID);
	$result->execute();
	$result->bind_result($id);
	while ($result->fetch()) {
		$moduleIDs[] = $id;
	}
	$result->close();
	
	return $moduleIDs;
}

function get_papers_for_school($date_range, $moduleIDs, $db) {
	// Get the papers.
	
	$papers = array();
	
	$result = $db->prepare("SELECT DISTINCT properties.property_id, paper_title, start_date, end_date FROM properties, properties_modules WHERE properties.property_id = properties_modules.property_id $date_range AND paper_type = '2' AND idMod IN (" . implode(',', $moduleIDs) . ") AND deleted IS NULL GROUP BY property_id ORDER BY paper_title");
	$result->execute();
	$result->bind_result($paperID, $paper_title, $start_date, $end_date);
	while ($result->fetch()) {
		$papers[$paperID]['paper_title'] = $paper_title;
		$papers[$paperID]['start_date'] = $start_date;
		$papers[$paperID]['end_date'] = $end_date;
	}
	$result->close();

	return $papers;
}

function get_feedback_release_dates($date_range, $moduleIDs, &$papers, $db) {
	$configObject = Config::get_instance();
	switch ($_GET['type']) {
		case 1:
			$report_type = 'objectives';
			break;
		case 2:
			$report_type = 'questions';
			break;
		case 3:
			$report_type = 'cohort_performance';
		  break;
	}
	
	$sql = "SELECT feedback_release.paper_id, idfeedback_release, type, DATE_FORMAT(date, '" . $configObject->get('cfg_long_date_time') . "') FROM properties, properties_modules, feedback_release WHERE feedback_release.paper_id = properties.property_id AND properties.property_id = properties_modules.property_id $date_range AND paper_type = '2' AND feedback_release.type='$report_type' AND idMod IN (" . implode(',', $moduleIDs) . ") AND deleted IS NULL";
	$result = $db->prepare($sql);
	$result->execute();
	$result->bind_result($paperID, $idfeedback_release, $type, $date);
	while ($result->fetch()) {
		$papers[$paperID]['feedback_date'] = $date;
	}
	$result->close();
}

?>
</table>

</div>
</body>
</html>