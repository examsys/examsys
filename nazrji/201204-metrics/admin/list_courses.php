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

function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
  foreach ($marray as $row) {
    $sortarr[] = $row[$column];
  }
  
  $sortarr = array_map('strtolower',$sortarr);
  $sort_method = SORT_STRING;
  if ($sort_order == 'asc') {
    array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
  } else {
    array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
  }
  return $marray;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title><?php echo $string['courses'] . ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">.l {cursor:pointer}
.t {color:black; text-decoration:none}
.h {background-color:#F1F5FB; color:black}
.col {padding-left:5px}
.col1 {padding-left:20px}
</style>
<script src="../js/staff_help.js" type="text/javascript"></script>
<script language="javascript">
  function selDeg(divID, evt) {
    tmp_ID = document.myform.oldID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
      document.getElementById(tmp_ID).style.color = 'black';
    }

    document.getElementById('menu1a').style.display = 'none';
    document.getElementById('menu1b').style.display = 'block';
    
    document.myform.oldID.value = divID;
    document.myform.id.value = divID;
    
    document.getElementById(divID).style.backgroundColor = '#B3C8E8';
    evt.cancelBubble = true;
  }
  
  function deselDeg() {
    tmp_ID = document.myform.oldID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
    }
    document.myform.oldID.value = '';
    document.getElementById('menu1b').style.display = 'none';
    document.getElementById('menu1a').style.display = 'block';
  }

  function lon(lineID) {
    if (lineID != document.myform.oldID.value) {
      document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
    }
  }

  function loff(lineID) {
    if (lineID != document.myform.oldID.value) {
      document.getElementById(lineID).style.backgroundColor = '';
    }
  }

  function edit(courseID) {
    document.location.href='./edit_course.php?courseID=' + courseID;
  }
</script>
</head>

<body onclick="deselDeg()">
<?php
  require '../include/course_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table class="header">
<tr>
<th colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['courses']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(237); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th>
</tr>
<tr style="font-size:90%">
<?php
  if (isset($_GET['sortby'])) {
    $sortby = $_GET['sortby'];
    $ordering = $_GET['ordering'];
  } else {
    $sortby = 'code';
    $ordering = 'asc';
  }

  // output table header
  $table_order = array($string['code']=>'code', $string['name']=>'name', $string['school']=>'school');
  foreach($table_order as $display => $key) {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
    if ($sortby == $key and $ordering == 'asc') {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=desc\">$display</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
    } elseif ($sortby == $key and $ordering == 'desc') {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
    } else {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a></th>";
    }
  }
  
?>
</tr>
<tr><th colspan="3" class="bevel"></th></tr>
<?php
$course_no = 0;
$courses = array();

$result = $mysqli->prepare("SELECT courses.id, school, name, description FROM courses LEFT JOIN schools ON courses.schoolid = schools.id WHERE name != 'left' AND name != 'none' AND courses.deleted IS NULL");
$result->execute();
$result->bind_result($id, $school, $name, $description);
while ($result->fetch()) {
  if ($school == '') $school = '<span style="color:#808080">unknown</span>';
  $courses[$course_no]['id'] = $id;
  $courses[$course_no]['code'] = $name;
  $courses[$course_no]['name'] = $description;
  $courses[$course_no]['school'] = $school;
  $course_no++;
}
$result->close();
$mysqli->close();

if (count($courses) > 0) {
  $courses = array_csort($courses, $sortby, $ordering);
}
$old_code_letter = '';
$old_name_letter = '';
$old_school = '';

for ($i=0; $i<$course_no; $i++) {
  $id = $courses[$i]['id'];
  
  if ($sortby == 'code' and substr($courses[$i]['code'], 0, 1) != $old_code_letter) {
    echo '<tr><td colspan="3"><table border="0" style="padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287"><tr><td>' . substr($courses[$i]['code'], 0, 1) . '</td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>';
  } elseif ($sortby == 'name' and substr($courses[$i]['name'], 0, 1) != $old_name_letter) {
    echo '<tr><td colspan="3"><table border="0" style="padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287"><tr><td>' . substr($courses[$i]['name'], 0, 1) . '</td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>';
  } elseif ($sortby == 'school' and $courses[$i]['school'] != $old_school) {
    echo '<tr><td colspan="3"><table border="0" style="padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287"><tr><td><nobr>' . $courses[$i]['school'] . '</nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>';
  }  
  echo "<tr id=\"$id\" onclick=\"selDeg($id,event)\" ondblclick=\"edit($id)\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td class=\"col1\">" . $courses[$i]['code'] . "</td><td class=\"col\">" . $courses[$i]['name'] . "</td><td class=\"col\"><nobr>" . $courses[$i]['school'] . "</nobr></td></tr>\n";

  $old_code_letter = substr($courses[$i]['code'], 0, 1);
  $old_name_letter = substr($courses[$i]['name'], 0, 1);
  $old_school = $courses[$i]['school'];
}

?>
</table>
</div>

</body>
</html>