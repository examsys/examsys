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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['modules'] . ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
.mid {padding-left:30px}
.l {cursor:pointer}
</style>

<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="javascript">
  function selMod(divID, moduleID, evt) {
    tmp_ID = document.myform.divID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
    }

    document.getElementById('menu1a').style.display = 'none';
    document.getElementById('menu1b').style.display = 'block';
    document.myform.divID.value = divID;
    
    document.myform.moduleID.value = moduleID;
    
    document.getElementById(divID).style.backgroundColor = '#B3C8E8';
    evt.cancelBubble = true;
  }
  
  function deselMod() {
    tmp_ID = document.myform.divID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
    }
    document.myform.divID.value = '';
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
  
  function edit(moduleID) {
    document.location.href='./edit_module.php?moduleid=' + moduleID;
  }
</script>
</head>

<body onclick="deselMod()">
<?php
  require '../include/module_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td colspan="3" style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['modules']; ?></td>
<td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></td>
</tr>
<tr>
<td style="background-color:#F1F5FB" class="mid"><?php echo $string['moduleid']; ?>&nbsp;</td>
<td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['name']; ?>&nbsp;</td>
<td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['active']; ?>&nbsp;</td><td style="width:30%; background-color:#F1F5FB">&nbsp;</td></tr>
<tr><td colspan="4" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
<?php
$old_school = '';
$id = 0;

$result = $mysqli->prepare("SELECT moduleid, fullname, school, active FROM modules, schools WHERE modules.schoolid=schools.id ORDER BY school, moduleid");
$result->execute();
$result->bind_result($moduleid, $fullname, $school, $active);
while ($result->fetch()) {
  if ($old_school != $school) {
    echo "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"5\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>$school</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  }
  if ($active == 1) {
    $tmp_active = $string['yes'];
  } else {
    $tmp_active = $string['no'];
  }
  echo "<tr id=\"$id\" onclick=\"selMod($id,'$moduleid',event)\" ondblclick=\"edit('$moduleid')\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td class=\"mid\">$moduleid</td><td>$fullname</td><td colspan=\"2\">$tmp_active</td></tr>\n";
  $old_school = $school;
  $id++;
}
$result->close();
$mysqli->close();
?>
</table>
</div>

</body>
</html>