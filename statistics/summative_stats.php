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

function display_row($month, $string, $month_paper_no, $month_papers_unused, $month_student_no, $month_min, $month_max, $current_year) {
  $month_names = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');

  if ($month_paper_no > 0) {
    echo "<tr><td><a href=\"summative_stats_detail.php?calyear=$current_year&month=$month\">" . $string[$month_names[$month - 1]] . "</a></td><td class=\"n\">$month_paper_no</td>";
    if ($month_papers_unused == 0) {
      echo "<td class=\"n grey\">$month_papers_unused</td>";
    } else {
      echo "<td class=\"n\">$month_papers_unused</td>";      
    }
    echo "<td class=\"n\">" . round($month_student_no/$month_paper_no,1) . "</td><td class=\"n\">$month_min</td><td class=\"n\">$month_max</td><td class=\"n\">" . number_format($month_student_no) . "</td></tr>\n";
  }
}

function count_labs($labs, &$lab_count) {
  $lab_list = explode(',', $labs);
  foreach ($lab_list as $labID) {
    if ($labID != '') {
      if (isset($lab_count[$labID])) {
        $lab_count[$labID]++;
      } else {
        $lab_count[$labID] = 1;
      }
    }
  }
}

function display_lab_stats($lab_count, $string, $db) {
  echo "<table class=\"stats\" style=\"width:350px !important\">\n";
  echo "<tr><th>" . $string['computerlab'] . "</th><th>" . $string['examno'] . "</th></tr>\n";
  $result = $db->prepare("SELECT id, name FROM labs ORDER BY name");
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $name);
  while ($result->fetch()) {
    if (isset($lab_count[$id])) {
      echo "<tr><td>$name</td><td class=\"n\">" . $lab_count[$id] . "</td></tr>";
    } else {
      $used_no = 0;
      echo "<tr><td>$name</td><td class=\"n grey\">0</td></tr>";
    }
  }
  $result->close();
  
  echo "</table>\n";
}

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
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../statistics/index.php"><?php echo $string['statistics']; ?></a></div>
  <div class="page_title"><?php echo $string['summativeexamstats']; ?>: <span style="font-weight:normal"><?php echo $_GET['calyear']; ?>/<?php echo (substr($_GET['calyear'],2,2)+1); ?></span></div>
</div>

<table class="header" style="font-size:90%">
<tr>
<th style="text-align:right" colspan="2"><div style="text-align:right; vertical-align:bottom"><?php echo drawTabs($current_year, 'academic'); ?></div></th>
</tr>
<tr><td colspan="2" style="border:0; background-color:#1E3C7B; height:5px"></td></tr>
</table>

<blockquote>
<table class="stats" style="width:500px !important">
<tr><th rowspan="2"><?php echo $string['month'] ?></th><th colspan="2"><?php echo $string['papers'] ?></th><th colspan="3"><?php echo $string['students'] ?></th><th rowspan="2"><?php echo $string['studentpapers'] ?></th></tr>
<tr><th><?php echo $string['taken'] ?></th><th><?php echo $string['unused'] ?></th><th><?php echo $string['mean'] ?></th><th><?php echo $string['min'] ?></th><th><?php echo $string['max'] ?></th></tr>
<?php
$total_paper_no = 0;
$total_paper_unused = 0;
$total_student_no = 0;
$month_paper_no = 0;
$month_student_no = 0;
$month_min = 99999;
$month_max = 0;
$old_month = '';
$month_papers_unused = 0;
$distinct_users = array();

$lab_count = array();

$result = $mysqli->prepare("SELECT property_id, paper_title, DATE_FORMAT(start_date,'%m'), start_date, end_date, labs FROM properties WHERE paper_type = '2' AND start_date >= " . $current_year . "0901000000 AND end_date < " . ($current_year+1) . "0831235959 AND labs != '' AND deleted IS NULL ORDER BY start_date");
$result->execute();
$result->store_result();
$result->bind_result($property_id, $paper_title, $month, $start_date, $end_date, $labs);
while ($result->fetch()) {
  $paper_count = 0;
  
  count_labs($labs, $lab_count);
  
  $paper_data = $mysqli->prepare("SELECT DISTINCT userid FROM log_metadata, users WHERE log_metadata.userID = users.ID AND roles IN ('Student', 'graduate') AND paperID = ? AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ?");
  $paper_data->bind_param('iss', $property_id, $start_date, $end_date);
  $paper_data->execute();
  $paper_data->store_result();
  $paper_data->bind_result($tmp_userID);
  if ($paper_data->num_rows == 0) {
    $month_papers_unused++;
    $total_paper_unused++;
  } else {
    while ($paper_data->fetch()) {
      $distinct_users[$tmp_userID] = 1;
      $paper_count++;
    }
  }
  $paper_data->close();
  
  if ($old_month != $month) {

    if ($old_month != '') {
      display_row($old_month, $string, $month_paper_no, $month_papers_unused, $month_student_no, $month_min, $month_max, $current_year);
    }
    $month_paper_no = 0;
    $month_student_no = 0;
    $month_min = 99999;
    $month_max = 0;
    $month_papers_unused = 0;
  }
  
  if ($paper_count > 0) {
    $total_paper_no++;
    $total_student_no += $paper_count;
    $month_paper_no++;
    $month_student_no += $paper_count;
    if ($paper_count < $month_min) $month_min = $paper_count;
    if ($paper_count > $month_max) $month_max = $paper_count;
	}
  $old_month = $month;
}
display_row($old_month, $string, $month_paper_no, $month_papers_unused, $month_student_no, $month_min, $month_max, $current_year);

echo "<tr><td>&nbsp;</td><td class=\"n subtotal\">" . number_format($total_paper_no) . "</td><td class=\"n subtotal\">" . number_format($total_paper_unused) . "</td><td class=\"subtotal\" colspan=\"3\">&nbsp;</td><td class=\"n subtotal\">" . number_format($total_student_no) . "</td></tr>\n";

$result->close();
?>
</table>
<br />
<?php
  printf($string['uniquestudents'], number_format(count($distinct_users)));
?>
  <br />
  <br />
<?php
  display_lab_stats($lab_count, $string, $mysqli);
?>
</blockquote>
</div>
  
</body>
</html>