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

// Check if we have any faculties
$result = $mysqli->prepare("SELECT COUNT(id) FROM faculty");
$result->execute();
$result->bind_result($faculties);
$result->fetch();
$result->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title><?php echo $string['schools'] . ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
.mid {padding-left:30px}
.l {cursor:pointer}
.no {text-align:right; padding-right:10px}
.deleted {color: red; text-decoration: line-through}
</style>

<script src="../js/staff_help.js" type="text/javascript"></script>
<script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
<script language="javascript">
$(function () {
  $('body').click(deselSch);
});

  function selSch(divID, evt) {
    tmp_ID = document.myform.divID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
    }

    document.getElementById('menu1a').style.display = 'none';
    document.getElementById('menu1b').style.display = 'block';
    document.myform.divID.value = divID;
       
    document.getElementById(divID).style.backgroundColor = '#B3C8E8';
    evt.cancelBubble = true;
  }
  
  function deselSch() {
    tmp_ID = document.myform.divID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
    }
//    document.myform.oldDivID.value = '';
    document.getElementById('menu1b').style.display = 'none';
    document.getElementById('menu1a').style.display = 'block';
  }

  function lon(lineID) {
    if (lineID != document.myform.divID.value) {
      document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
    }
  }

  function loff(lineID) {
    if (lineID != document.myform.divID.value) {
      document.getElementById(lineID).style.backgroundColor = '';
    }
  }
  
  function edit(schoolID) {
    document.location.href='./edit_school.php?schoolid=' + schoolID;
  }
</script>
</head>

<body>
<?php
  require '../include/school_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table class="header">
<tr>
<th colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['schools']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr>
<th><div class="mid"><?php echo $string['name']; ?>&nbsp;</div></th>
<th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['faculty']; ?>&nbsp;</th>
<th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['modules']; ?>&nbsp;</th>
</tr>
<tr><th colspan="3" class="bevel"></th></tr>
<?php

if ($faculties > 0) {
  $old_faculty = '';
$id = 0;

$result = $mysqli->prepare("SELECT schools.id, schools.school, faculty.name, faculty.deleted, COUNT(modules.id) FROM (schools, faculty) LEFT JOIN modules ON schools.id=modules.schoolid WHERE schools.facultyID=faculty.id AND schools.deleted IS NULL GROUP BY school ORDER BY faculty.name, school");
$result->execute();
$result->bind_result($id, $school, $faculty, $faculty_deleted, $module_no);
while ($result->fetch()) {
  if ($old_faculty != $faculty) {
    $del = ($faculty_deleted != '') ? ' class="deleted"' : '';
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td{$del}><nobr>$faculty</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  }
  echo "<tr";
  if ($module_no == 0) {
    echo ' style="color:#808080"';
  }
  echo " id=\"$id\" onclick=\"selSch($id,event)\" ondblclick=\"edit('$id')\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td><div class=\"mid\">$school</div></td><td>$faculty</td><td><div class=\"no\">" . number_format($module_no) . "</div></td></tr>\n";
  $old_faculty = $faculty;
  $id++;
}
$result->close();
$mysqli->close();
} else {
  echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
  echo "<tr><td colspan=\"4\">{$string['musthavefaculty']}</td></tr>\n";
}

?>
</table>
</div>

</body>
</html>