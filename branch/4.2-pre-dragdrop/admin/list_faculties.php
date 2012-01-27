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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['faculties'] . ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
.mid {padding-left:10px}
.l {cursor:pointer}
.no {text-align:right; padding-right:10px}
</style>

<script src="../js/staff_help.js" type="text/javascript"></script>
<script language="javascript">
  function selFac(divID, evt) {
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
  
  function deselFac() {
    tmp_ID = document.myform.divID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
    }
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
</script>
</head>

<body onclick="deselFac()">
<?php
  require '../include/faculty_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td style="background-color:#F1F5FB" colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['faculties']; ?></td>
<td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></td>
</tr>
<tr>
<td style="background-color:#F1F5FB" class="mid"><?php echo $string['name']; ?>&nbsp;</td><td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['schoolno']; ?></td><td style="width:50%; background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" /></td></tr>
<tr><td colspan="3" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
<?php
$old_faculty = '';
$id = 0;

$result = $mysqli->prepare("SELECT faculty.id, name, COUNT(school) FROM faculty LEFT JOIN schools ON schools.facultyID=faculty.id WHERE faculty.deleted IS NULL GROUP BY name ORDER BY name");
$result->execute();
$result->bind_result($id, $name, $school_no);
while ($result->fetch()) {
  echo "<tr id=\"$id\" onclick=\"selFac($id,event)\" ondblclick=\"edit('$id')\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td class=\"mid\">$name</td><td class=\"mid\" style=\"text-align:right\">$school_no</td><td></td></tr>\n";
  $id++;
}
$result->close();
$mysqli->close();
?>
</table>
</div>

</body>
</html>