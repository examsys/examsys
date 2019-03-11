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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$paperid = check_var('paperID', 'GET', true, false, true);

$paper_modules = Paper_utils::get_modules($paperid, $mysqli);
$module_id_list = implode(',', array_keys($paper_modules));

// Get data about the paper which needs scheduling
$results = $mysqli->prepare("SELECT "
  . "property_id, paper_title, academic_year.calendar_year, academic_year, period, barriers_needed, cohort_size, notes, sittings, campus, title, first_names, "
  . "surname, email, exam_duration "
  . "FROM (properties, scheduling, users, academic_year) "
  . "WHERE property_id = ? "
  . "AND properties.property_id = scheduling.paperID "
  . "AND properties.paper_ownerID = users.id "
  . "AND properties.calendar_year = academic_year.calendar_year");
$results->bind_param('i', $paperid);
$results->execute();
$results->store_result();
$results->bind_result($property_id, $paper_title, $calendar_year, $academic_year, $period, $barriers_needed, $cohort_size, $notes, $sittings, $campus, $title, $first_names, $surname, $email, $exam_duration);
$results->fetch();
if ($results->num_rows == 0) {
  $results->close();
  $contactemail = support::get_email();
  $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$results->close();


// Get student enrolments
$module_sizes = array();
$query = "SELECT moduleID, COUNT(modules_student.id) FROM (modules_student, modules) WHERE modules_student.idMod=modules.id AND idMod IN ($module_id_list) AND calendar_year = ? GROUP BY moduleid";
$results = $mysqli->prepare($query);
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
$results = $mysqli->prepare("SELECT extra_time FROM (special_needs, modules_student) WHERE special_needs.userID=modules_student.userID AND idMod IN ($module_id_list) AND calendar_year = ?");
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
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('Rog&#333;: ' . $string['summativeexamdetails']); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style>
    .data {border-collapse:collapse; margin-left:30px; margin-top:20px}
    .data td {border:1px solid #EAEAEA}
    .f1 {background-color:#EAEAEA}
  </style>
  
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/scheduling_detail_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="summative_scheduling.php"><?php echo $string['summativescheduling'] ?></a></div>
  <div class="page_title"><?php echo $string['Paper'] ?>: <span style="font-weight: normal"><?php echo $paper_title ?></span></div>
</div>

<table cellspacing="0" cellpadding="4" style="font-size:100%" class="data">
<?php
  if ($barriers_needed == 1) {
    $barriers_needed = $string['Yes'];
  } else {
    $barriers_needed = $string['No'];
  }
  $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
  if ($period == '') {
    $display_period = 'unknown';
  } else {
    $display_period = $string[$months[$period]];
  }
  
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

  if ($cohort_size == 0) {
    $cohort_size = $string['whole cohort'];
  }

  echo "<tr><td class=\"f1\">" . $string['papername'] . "</td><td>$paper_title</td></tr>\n";
  $display_name = "$title $first_names $surname";
  if ($userObject->has_role('Demo')) {
    $display_name = \demo::demo_replace_name(0);
    $email = 'joe.bloggs@uni.ac.uk';
  }
  echo "<tr><td class=\"f1\">" . $string['paperowner'] . "</td><td>$display_name (<a href=\"mailto:$email\">$email</a>)</td></tr>\n";  
  echo "<tr><td class=\"f1\">" . $string['session'] . "</td><td>$academic_year</td></tr>\n";
  echo "<tr><td class=\"f1\">" . $string['modules'] . "</td><td>";

  foreach ($paper_modules as $module_id=>$module_name) {
    echo "$module_name<br />\n";
  }
  echo "</td></tr>\n";
  echo "<tr><td class=\"f1\">" . $string['examduration'] . "</td><td>$exam_duration</td></tr>\n";  
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
