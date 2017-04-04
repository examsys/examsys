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
require '../include/errors.php';
require '../include/year_tabs.inc';

$current_year = check_var('calyear', 'GET', true, false, true);
$current_month = (int)check_var('month', 'GET', true, false, true);

$month_names = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');


$month_start[9] = $current_year . '0901000000';
$month_start[10] = $current_year . '1001000000';
$month_start[11] = $current_year . '1101000000';
$month_start[12] = $current_year . '1201000000';
$month_start[1] = ($current_year + 1) . '0101000000';
$month_start[2] = ($current_year + 1) . '0201000000';
$month_start[3] = ($current_year + 1) . '0301000000';
$month_start[4] = ($current_year + 1) . '0401000000';
$month_start[5] = ($current_year + 1) . '0501000000';
$month_start[6] = ($current_year + 1) . '0601000000';
$month_start[7] = ($current_year + 1) . '0701000000';
$month_start[8] = ($current_year + 1) . '0801000000';

$month_end[9] = $current_year . '1001000000';
$month_end[10] = $current_year . '1101000000';
$month_end[11] = $current_year . '1201000000';
$month_end[12] = ($current_year + 1) . '0101000000';
$month_end[1] = ($current_year + 1) . '0201000000';
$month_end[2] = ($current_year + 1) . '0301000000';
$month_end[3] = ($current_year + 1) . '0401000000';
$month_end[4] = ($current_year + 1) . '0501000000';
$month_end[5] = ($current_year + 1) . '0601000000';
$month_end[6] = ($current_year + 1) . '0701000000';
$month_end[7] = ($current_year + 1) . '0801000000';
$month_end[8] = ($current_year + 1) . '0901000000';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['summativeexamstats'] . ' ' . $configObject->get('cfg_install_type') ?></title>
  
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
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../statistics/index.php"><?php echo $string['statistics']; ?></a></div>
  <div class="page_title"><?php echo $string['summativeexamstats'] ?>: <span style="font-weight:normal"><?php echo $string[$month_names[$current_month - 1]] . ' (' .$_GET['calyear']; ?>/<?php echo (substr($_GET['calyear'],2,2)+1) ?>)</span></div>
</div>

<table class="header" style="font-size:90%">
<tr>
<th style="text-align:right" colspan="2"><div style="text-align:right; vertical-align:bottom"><?php echo drawTabs($current_year, 'academic', '&month=' . $current_month); ?></div></th>
</tr>
<tr><td colspan="2" style="border:0; background-color:#1E3C7B; height:5px"></td></tr>
</table>

<blockquote>
<table class="stats">
  <tr><th><?php echo $string['date'] ?></th><th><?php echo $string['papers'] ?></th><th><?php echo $string['students'] ?></th></tr>
<?php
$total_student_no = 0;
$distinct_users = array();

$result = $mysqli->prepare("SELECT property_id, paper_title, DATE_FORMAT(start_date, '" . $configObject->get('cfg_long_date_time') . "') AS display_start_date, start_date, end_date FROM properties WHERE paper_type = '2' AND start_date >= " . $month_start[$current_month] . " AND end_date < " . $month_end[$current_month] . " AND labs != '' AND deleted IS NULL ORDER BY start_date");
$result->execute();
$result->store_result();
$result->bind_result($property_id, $paper_title, $display_start_date, $start_date, $end_date);
while ($result->fetch()) {
  $paper_count = 0;
  
  $paper_data = $mysqli->prepare("SELECT DISTINCT userid FROM log_metadata, users WHERE log_metadata.userID = users.ID AND roles IN ('Student', 'graduate') AND paperID = ? AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ?");
  $paper_data->bind_param('iss', $property_id, $start_date, $end_date);
  $paper_data->execute();
  $paper_data->store_result();
  $paper_data->bind_result($tmp_userID);
  $user_no = $paper_data->num_rows;
  while ($paper_data->fetch()) {
    $distinct_users[$tmp_userID] = 1;
  }
  $paper_data->close();
  
  if ($user_no == 0) {
    $class = ' grey';
  } else {
    $class = '';    
  }
  
  echo "<tr><td>" . $display_start_date . "</td><td><a href=\"../paper/details.php?paperID=$property_id\">" . $paper_title . "</a></td><td class=\"n$class\">$user_no</td></tr>\n";
  $total_student_no += $user_no;
}

echo "<tr><td colspan=\"3\" class=\"n subtotal\">" . number_format($total_student_no) . "</td></tr>\n";

$result->close();
$mysqli->close();
?>
</table>
<br />
<?php
  printf($string['uniquestudents'], number_format(count($distinct_users)));
?>
</blockquote>
</div>
  
</body>
</html>