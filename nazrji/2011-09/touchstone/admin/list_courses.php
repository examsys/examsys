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

  require '../include/sysadmin_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['courses'] . ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
.l {cursor:pointer}
</style>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
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

  function edit(degreeID) {
    document.location.href='./edit_degree.php?degreeID=' + degreeID;
  }
</script>
</head>

<body onclick="deselDeg()">
<?php
  require '../include/course_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td colspan="2" style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['courses']; ?></td>
<td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(237); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
</tr>
<tr style="font-size:90%"><td style="background-color:#F1F5FB">&nbsp;<?php echo $string['code']; ?></td>
<td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['name']; ?>&nbsp;</td>
<td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['school']; ?>&nbsp;</td>
</tr>
<tr><td colspan="3" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
<?php
$result = $mysqli->prepare("SELECT id, school, degree, description FROM degrees WHERE degree != 'left' AND degree != 'none' AND school != 'university' AND school != 'NHS' ORDER BY degree");
$result->execute();
$result->bind_result($id, $school, $degree, $description);
while ($result->fetch()) {
  echo "<tr id=\"$id\" onclick=\"selDeg($id,event)\" ondblclick=\"edit($id)\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td>&nbsp;$degree</td><td>$description</td><td>$school</td></tr>\n";
}
$result->close();
$mysqli->close();
?>
</table>
</div>

</body>
</html>