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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  $year = $_GET['year'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['summativeexamstats'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .stats {border-collapse:collapse}
    .stats th {background-color:#EAEAEA; border: 1px solid #C0C0C0; font-weight:normal}
    .stats td {border: 1px solid #C0C0C0}
    .n {text-align:right}
  </style>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script language="JavaScript">
    function jumpTo() {
      document.location = 'summative_stats.php?year=' + document.getElementById('year').value;
    }
  </script>
</head>

<body>
<?php
  require '../include/admin_options.inc';
?>
<div id="content" class="content">
<table class="header">
<tr>
<th colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></a></div></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th>
</tr>
<tr>
<th colspan="2"><div style="margin-left:10px; font-size:200%"><strong><?php echo $string['summativeexamstats']; ?>:</strong> <?php echo $_GET['year']; ?>/<?php echo (substr($_GET['year'],2,2)+1); ?></th>
<th style="text-align:right; vertical-align:bottom; padding-bottom:2px; padding-right:6px"><select name="year" id="year" onchange="jumpTo()">
<?php
for ($i=2005; $i<=date('Y'); $i++) {
  if ($i == $_GET['year']) {
    echo "<option value=\"$i\" selected>$i/" . substr(($i+1),2,2) . "</option>\n";
  } else {
    echo "<option value=\"$i\">$i/" . substr(($i+1),2,2) . "</option>\n";
  }
}
?>
</select></th>
</tr>
<tr><th colspan="3" class="bevel"></th></tr>
</table>

<blockquote>
<table class="stats" cellpadding="2" cellspacing="0" border="0" style="width:400px">
<tr><th><?php echo $string['month']; ?></th><th><?php echo $string['papers']; ?></th><th><?php echo $string['mean']; ?></th><th><?php echo $string['min']; ?></th><th><?php echo $string['max']; ?></th><th><?php echo $string['studentpapers']; ?></th></tr>
<?php
$total_paper_no = 0;
$total_student_no = 0;
$month_paper_no = 0;
$month_student_no = 0;
$month_min = 99999;
$month_max = 0;
$old_month = '';
$distinct_users = array();

$result = $mysqli->prepare("SELECT property_id, paper_title, DATE_FORMAT(start_date,'%M'), start_date, end_date, labs FROM properties WHERE start_date > " . $year . "0901000000 AND end_date < " . ($year+1) . "0831235959 AND labs != '' AND deleted IS NULL ORDER BY start_date");
$result->execute();
$result->store_result();
$result->bind_result($property_id, $paper_title, $month, $start_date, $end_date, $labs);
while ($result->fetch()) {
  $paper_count = 0;
  
  $paper_data = $mysqli->prepare("SELECT DISTINCT userid FROM log_metadata, users WHERE log_metadata.userID=users.ID AND roles IN ('Student','graduate') AND paperID = ? AND started >= ? AND started <= ?");
  $paper_data->bind_param('iss', $property_id, $start_date, $end_date);
  $paper_data->execute();
  $paper_data->store_result();
  $paper_data->bind_result($tmp_userID);
  while ($paper_data->fetch()) {
    $distinct_users[$tmp_userID] = 1;
    $paper_count++;
  }
  $paper_data->close();
  
  if ($old_month != $month) {

    if ($old_month != '') {
      if ($month_paper_no > 0) {
        echo "<tr><td>" . $string[strtolower($old_month)] . "</td><td class=\"n\">$month_paper_no</td><td class=\"n\">" . round($month_student_no/$month_paper_no,1) . "</td><td class=\"n\">$month_min</td><td class=\"n\">$month_max</td><td class=\"n\">" . number_format($month_student_no) . "</td></tr>\n";
      }
    }
    $month_paper_no = 0;
    $month_student_no = 0;
    $month_min = 99999;
    $month_max = 0;
  }
  
  if ($paper_count > 0) {
    $lab_no = substr_count($labs, ',') + 1;
    $total_paper_no++;
    $total_student_no += $paper_count;
    $month_paper_no++;
    $month_student_no += $paper_count;
    if ($paper_count < $month_min) $month_min = $paper_count;
    if ($paper_count > $month_max) $month_max = $paper_count;
  }
  $old_month = $month;
}
if ($month_paper_no > 0) {
  echo "<tr><td>".$string[strtolower($old_month)]."</td><td class=\"n\">$month_paper_no</td><td class=\"n\">" . round($month_student_no/$month_paper_no,1) . "</td><td class=\"n\">$month_min</td><td class=\"n\">$month_max</td><td class=\"n\">" . number_format($month_student_no) . "</td></tr>\n";
}
echo "<tr><td><strong>".$string['totals']."</strong></td><td class=\"n\"><strong>" . number_format($total_paper_no) . "</strong></td><td colspan=\"3\">&nbsp;</td><td class=\"n\"><strong>" . number_format($total_student_no) . "</strong></td></tr>\n";

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