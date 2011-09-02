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

  require '../include/admin_auth.inc';
  require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone</title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
.divider {padding-left:16px; padding-bottom:2px; font-weight:bold}
.sch {padding-left:32px; text-indent:-20px}
.greysch {padding-left:12px; color:#808080}
.mod {padding-left:60px; text-indent:-20px}
</style>

<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script src="../javascript/sidebar.js" type="text/javascript"></script>
<script language="JavaScript">
  function displayCredits(){
    notice=window.open("../credits/credits.php","credits","width=700,height=487,scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=0,menubar=0");
    notice.moveTo(screen.width/2-350,screen.height/2-243)
    if (window.focus) {
      notice.focus();
    }
  }

  function showHide(sectionID) {
    sectionID = 'block' + sectionID;
    current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
    document.getElementById(sectionID).style.display = current;
  }
</script>
</head>

<body onclick="hideMenus()">
<?php
  require '../include/options_menu.inc';
?>

<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a></div><div style="font-size:220%; font-weight:bold; margin-left:10px">All Modules</div></td><td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td></tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr><td style="vertical-align:top; width:50%; border-right:#95AEC8 1px solid">
<?php
  $old_faculty = '';
  $old_school = '';
  $module_block = false;
  $block_id=0;
  if (strpos($userroles,'SysAdmin') !== false) {
    $results = $mysqli->query("SELECT DISTINCT faculty.name as faculty, schools.school, moduleid, fullname FROM (schools, faculty) LEFT JOIN modules ON schools.id=modules.schoolid WHERE schools.facultyID=faculty.id ORDER BY faculty.name, school, moduleid");
  } else {
    $results = $mysqli->query("SELECT DISTINCT faculty.name as faculty, schools.school, moduleid, fullname FROM (schools, faculty, admin_access, modules) WHERE schools.facultyID=faculty.id AND schools.id=modules.schoolid AND schools.id=admin_access.schools_id AND admin_access.userID=$userID ORDER BY faculty.name, school, moduleid");
  }
  while ($row = $results->fetch_assoc()) {
    if ($old_faculty != $row['faculty'] or $old_school != $row['school']) {
      if ($module_block == true) {
        echo "</div>\n";
        $module_block = false;
      }
    }
    if ($old_faculty != $row['faculty']) {
      echo "<table border=\"0\" style=\"padding-top:10px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $row['faculty'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    }
    if ($old_school != $row['school']) {
      if ($row['moduleid'] == '') {
        echo "<div class=\"greysch\"><img src=\"../artwork/folder_16_grey.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" />&nbsp;" . $row['school'] . "</div>\n";
      } else {
        echo "<div class=\"sch\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" onclick=\"showHide($block_id)\" />&nbsp;<a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">" . $row['school'] . "</a></div>\n";
      }
      if ($module_block == false) {
        echo "<div id=\"block$block_id\" style=\"display:none\">";
        $module_block = true;
        $block_id++;
      }
    }
    if ($row['moduleid'] != '') {
      echo "<div class=\"mod\"><a href=\"details.php?module=" . $row['moduleid'] . "\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" /></a>&nbsp;<a href=\"details.php?module=" . $row['moduleid'] . "\" target=\"_top\">" . $row['moduleid'] . ": " . $row['fullname'] . "</a></div>\n";
    }
    $old_faculty = $row['faculty'];
    $old_school = $row['school'];
  }
  $results->close();

  echo "</div>\n";      // -- End of 'content' div ------------------

?>
</td><td style="vertical-align:top; width:50%">
<?php
  echo "<table border=\"0\" style=\"padding-top:10px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>By Module Code</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

  $old_faculty = '';
  $old_letter = '';
  $module_block = false;
  if (strpos($userroles,'SysAdmin') !== false) {
    $results = $mysqli->query("SELECT DISTINCT moduleid, fullname FROM modules ORDER BY moduleid");
  } else {
    $results = $mysqli->query("SELECT DISTINCT moduleid, fullname FROM (schools, admin_access, modules) WHERE schools.id=modules.schoolid AND schools.id=admin_access.schools_id AND admin_access.userID=$userID ORDER BY moduleid");
  }
  while ($row = $results->fetch_assoc()) {
    if ($old_letter !== substr($row['moduleid'],0,1)) {
      if ($module_block == true) {
        echo "</div>\n";
        $module_block = false;
      }
    }
    if ($old_letter !== substr($row['moduleid'],0,1)) {
      if ($row['moduleid'] === '') {
        echo "<div class=\"greysch\"><img src=\"../artwork/folder_16_grey.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" />&nbsp;" . substr($row['moduleid'],0,1) . "</div>\n";
      } else {
        echo "<div class=\"sch\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" onclick=\"showHide($block_id)\" />&nbsp;<a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">" . substr($row['moduleid'],0,1) . "</a></div>\n";
      }
      if ($module_block == false) {
        echo "<div id=\"block$block_id\" style=\"display:none\">";
        $module_block = true;
        $block_id++;
      }
    }
    if ($row['moduleid'] !== '') {
      echo "<div class=\"mod\"><a href=\"details.php?module=" . $row['moduleid'] . "\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" /></a>&nbsp;<a href=\"details.php?module=" . $row['moduleid'] . "\" target=\"_top\">" . $row['moduleid'] . ": " . $row['fullname'] . "</a></div>\n";
    }
    $old_letter = substr($row['moduleid'],0,1);
  }
  $results->close();

  echo "</div>\n";      // -- End of 'content' div ------------------
?>
</td></tr>
</table>
</div>
</div>
<?php

  $mysqli->close();
?>
</body>
</html>