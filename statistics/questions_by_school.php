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
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

	<title>Rog&#333;: <?php echo $string['questionsbyschool']  . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/statistics.css" />
	<style type="text/css">
		.qtype {width:4%}
	</style>
	
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
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../statistics/index.php"><?php echo $string['statistics']; ?></a></div>
  <div class="page_title"><?php echo $string['questionsbyschool']; ?></div>
</div>

<table class="stats">
<tr>
<th><?php echo $string['school'] ?></th>
<?php
	$types = array('area', 'dichotomous', 'enhancedcalc', 'extmatch', 'blank', 'hotspot', 'info', 'labelling', 'likert', 'matrix', 'mcq', 'mrq', 'keyword_based', 'random', 'rank', 'sct', 'textbox', 'true_false');
  foreach ($types as $type) {
	  echo '<th class="qtype">' . $string[$type] . '</th>';
	}
?>
</tr>
<?php
$master_array = array();

// Get a list of all schools in Rogo.
$result = $mysqli->prepare("SELECT schools.id, schools.code, school, faculty.code, name FROM schools, faculty WHERE schools.facultyID = faculty.id AND school != 'Training' AND schools.deleted IS NULL AND faculty.deleted IS NULL ORDER BY name, school");
$result->execute();
$result->bind_result($id, $code, $school, $faculty_code, $faculty);
while ($result->fetch()) {
  $master_array[$id]['name'] = $code . ' ' . $school;
  $master_array[$id]['faculty'] = $faculty_code . ' ' . $faculty;
  $master_array[$id]['types'] = array('blank'=>0, 'dichotomous'=>0, 'flash'=>0, 'hotspot'=>0, 'labelling'=>0, 'likert'=>0, 'matrix'=>0, 'mcq'=>0, 'mrq'=>0, 'rank'=>0, 'textbox'=>0, 'info'=>0, 'extmatch'=>0, 'random'=>0, 'sct'=>0, 'keyword_based'=>0, 'true_false'=>0, 'area'=>0, 'enhancedcalc'=>0);
}
$result->close();

// Get a count of active questions by type for each school.
$statssql = <<<SQL
SELECT DISTINCT s.id, q.q_type, qm.q_id
FROM schools s
  JOIN faculty f ON s.facultyID = f.id
  JOIN modules m ON m.schoolid = s.id
  JOIN questions_modules qm ON qm.idMod = m.id
  JOIN questions q ON q.q_id = qm.q_id
WHERE s.school != 'Training'
  AND s.deleted IS NULL AND f.deleted IS NULL
  AND m.active = 1 AND m.mod_deleted IS NULL
  AND q.deleted IS NULL
SQL;
$stats = $mysqli->prepare($statssql);
$stats->execute();
$stats->bind_result($id, $question_type, $count);
while ($stats->fetch()) {
  $master_array[$id]['types'][$question_type]++;
}
$stats->close();

$old_faculty = '';
$faculty_stats = $types;

foreach ($master_array as $school => $data) {
  if ($old_faculty != $data['faculty']) {
	  if ($old_faculty != '') {
			echo output_faculty_stats($faculty_stats, $types);
	  }
		echo '<tr><td colspan="19" class="faculty">' . $data['faculty'] . '</td></tr>';
		$faculty_stats = $types;
	}
  echo "<tr><td>" . $data['name'] . "</td>";
	
	foreach ($types as $type) {
	  if ($data['types'][$type] == 0) {
			echo "<td class=\"n grey\">" . $data['types'][$type] . "</td>";
		} else {
			echo "<td class=\"n\">" . number_format($data['types'][$type]) . "</td>";
		}
		if (isset($faculty_stats[$type])) {
			$faculty_stats[$type] += $data['types'][$type];
		} else {
			$faculty_stats[$type] = $data['types'][$type];
		}
	}
	echo "</tr>\n";

	$old_faculty = $data['faculty'];
}
echo output_faculty_stats($faculty_stats, $types);
?>
</table>

</div>
</body>
</html>
<?php
function output_faculty_stats($stats, $types) {
  $html = '<tr><td>&nbsp;</td>';
	
	foreach ($types as $type) {
	  $html .= '<td class="n subtotal">' . number_format($stats[$type]) . '</td>';
	}
	
	$html .= '</tr>';
	
	return $html;
}
?>