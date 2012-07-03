<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';

// Get data about the paper which needs scheduling
$results = $mysqli->prepare("SELECT property_id, paper_title, moduleID, calendar_year, period, barriers_needed, cohort_size, notes, sittings, campus, title, first_names, surname, email FROM (properties, scheduling, users) WHERE property_id=? AND properties.property_id=scheduling.paperID AND properties.paper_ownerID=users.id");
$results->bind_param('i', $_GET['paperID']);
$results->execute();
$results->store_result();
$results->bind_result($property_id, $paper_title, $moduleID, $calendar_year, $period, $barriers_needed, $cohort_size, $notes, $sittings, $campus, $title, $first_names, $surname, $email);
$results->fetch();
$results->close();

// Get student enrolments
$module_sizes = array();
$results = $mysqli->prepare("SELECT moduleID, COUNT(id) FROM student_modules WHERE moduleid IN ('" . str_replace(",", "','", $moduleID) . "') AND calendar_year=? GROUP BY moduleid");
$results->bind_param('s', $calendar_year);
$results->execute();
$results->store_result();
$results->bind_result($tmp_moduleID, $module_size);
while ($results->fetch()) {
  $module_sizes[$tmp_moduleID] = $module_size;
}
$results->close();

// Get extra time
$extra_time_list = array();
$results = $mysqli->prepare("SELECT extra_time FROM special_needs, student_modules WHERE special_needs.userID=student_modules.userID AND moduleid IN ('" . str_replace(",", "','", $moduleID) . "') AND calendar_year=?");
$results->bind_param('s', $calendar_year);
$results->execute();
$results->store_result();
$results->bind_result($extra_time);
while ($results->fetch()) {
  if (isset($extra_time_list[$extra_time])) {
    $extra_time_list[$extra_time]++;
  } else {
    $extra_time_list[$extra_time] = 1;
  }
}
$results->close();
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['summativeexamdetails'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style>
  .data {border-collapse:collapse; margin-left:30px; margin-top:20px}
  .data td {border:1px solid #C0C0C0}
  .f1 {background-color:#EAEAEA}
  </style>
  <script src="../js/staff_help.js" type="text/javascript"></script>
<script language="javascript">

</script>
</head>

<body>
<?php
  require '../include/scheduling_detail_options.inc';
?>
<table class="header" style="font-size:80%">
<tr>
<th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="summative_scheduling.php"><?php echo $string['summativescheduling']; ?></a></div><div style="margin-left:10px; font-size:200%"><strong>Paper:</strong> <?php echo $paper_title; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(0); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>

<table cellspacing="0" cellpadding="4" style="font-size:90%" class="data">
<?php
  if ($barriers_needed == 1) {
    $barriers_needed = 'Yes';
  } else {
    $barriers_needed = 'No';
  }
  $months = array('january','february','march','april','may','june','july','august','september','october','november','december');
  $display_period = $string[$months[$period]];
  
  if ($cohort_size == '<whole cohort>') {
    $cohort_size = 0;
    foreach($module_sizes as $tmp_moduleID=>$module_size) {
      $cohort_size += $module_size;
    }
    
    if (count($extra_time_list) > 0) {
      foreach ($extra_time_list as $extra_time=>$number) {
        $cohort_size .= '<br />' . $extra_time . '% x' . $number;
      }
    }
  }

  echo "<tr><td class=\"f1\">" . $string['papername'] . "</td><td>$paper_title</td></tr>\n";  
  echo "<tr><td class=\"f1\">" . $string['paperowner'] . "</td><td>$title $first_names $surname (<a href=\"mailto:$email\">$email</a>)</td></tr>\n";  
  echo "<tr><td class=\"f1\">" . $string['session'] . "</td><td>$calendar_year</td></tr>\n";
  echo "<tr><td class=\"f1\">" . $string['modules'] . "</td><td>";
  $module_list = explode(',', $moduleID);
  foreach ($module_list as $individual_module) {  
    echo "$individual_module<br />\n";
  }
  echo "</td></tr>\n";
  echo "<tr><td class=\"f1\">" . $string['cohortsize'] . "</td><td>$cohort_size</td></tr>\n";  
  echo "<tr><td class=\"f1\">" . $string['sittings'] . "</td><td>$sittings</td></tr>\n";  
  echo "<tr><td class=\"f1\">" . $string['examperiod'] . "</td><td>$display_period</td></tr>\n";  
  echo "<tr><td class=\"f1\">" . $string['barriersneeded'] . "</td><td>$barriers_needed</td></tr>\n";  
  echo "<tr><td class=\"f1\">" . $string['campus'] . "</td><td>$campus</td></tr>\n";  
  echo "<tr><td class=\"f1\">" . $string['notes'] . "</td><td>$notes</td></tr>\n";  

?>
</table>


</div>

</body>
</html>
