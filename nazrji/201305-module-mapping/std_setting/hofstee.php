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
* Hofstee plot
*
* @author Nikodem Miranowicz
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/results_cache.class.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php echo $string['hofstee'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/warnings.css" />

<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/staff_help.js"></script>

</head>
<body>

<?php
	$paperID    = check_var('paperID', 'GET', true, false, true);
	$results_cache = new ResultsCache($mysqli);
	$marks = $results_cache->get_paper_marks_by_paper($paperID);
	sort($marks);
	
	echo "<div id='canvas_div'>\n";
	echo "<canvas id='canvas_graph' width='800' height='600'></canvas><br>\n";
	echo "<table style=\"font-size:85%;\"><tr><td width='200'>&nbsp;</td><td>&nbsp;</td>\n";
	echo "<td>x1:</td><td>x2:</td><td>y1:</td><td>y2:</td><td>xs:</td><td>ys:</td>\n";
	echo "</tr><tr><td>&nbsp;</td>\n";
	echo "<td><input type='checkbox' name='checkbox' id='checkbox'>" . $string['integeronly'] . "
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
	echo "<td><input type='text' size='5' name='x1' id='x1' class='tf'></td>\n";
	echo "<td><input type='text' size='5' name='x2' id='x2' class='tf'></td>\n";
	echo "<td><input type='text' size='5' name='y1' id='y1' class='tf'></td>\n";
	echo "<td><input type='text' size='5' name='y2' id='y2' class='tf'></td>\n";
	echo "<td><input type='text' size='5' name='xs' id='xs' readonly></td>\n";
	echo "<td><input type='text' size='5' name='ys' id='ys' readonly></td>\n";
	echo "</tr><tr>";
	echo "<td></td><td colspan='7'><input type='button' value='".$string['passmark']."'>&nbsp;<input type='button' value='".$string['distinction']."'></td>";
	echo "</tr></table>\n";
	
	echo "<script type='text/javascript'>
		var lang_cohort = '".  $string['cohort'] . "';
		var lang_correct = '".  $string['correct'] . "';			
		var marks = ".  json_encode($marks) . ";
		</script>";
	echo "<script type='text/javascript' src='../html5/hofstee.js'></script></div>\n";

?>
</body>
</html>
