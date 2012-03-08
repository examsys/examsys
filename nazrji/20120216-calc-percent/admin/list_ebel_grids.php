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
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Ebel Grids<?php echo ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
.mid {padding-left:10px}
.l {cursor:pointer}
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
    document.myform.oldDivID.value = '';
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
  require '../include/ebel_grid_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table class="header">
<tr>
<th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['ebeltemplates']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr>
<th><div class="mid"><?php echo $string['gridnames']; ?>&nbsp;</div></th><th>&nbsp;</th></tr>
<tr><th colspan="2" class="bevel"></th></tr>
<?php
$old_faculty = '';
$id = 0;

$result = $mysqli->prepare("SELECT id, name FROM ebel_grid_templates ORDER BY name");
$result->execute();
$result->bind_result($id, $name);
while ($result->fetch()) {
  echo "<tr id=\"$id\" onclick=\"selFac($id,event)\" ondblclick=\"editTemplate()\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td colspan=\"2\"><div class=\"mid\">$name</div></td></tr>\n";
  $id++;
}
$result->close();
$mysqli->close();
?>
</table>
</div>

</body>
</html>