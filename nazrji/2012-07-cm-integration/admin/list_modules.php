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
require '../include/sort.inc';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title><?php echo $string['modules'] . ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
.l {cursor:pointer}
.t {color:black; text-decoration:none}
.col {padding-left:5px}
.col1 {padding-left:20px}
</style>

<script src="../js/staff_help.js" type="text/javascript"></script>
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

<table class="header">
<tr>
<th colspan="3"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['modules']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr>
<?php
  if (isset($_GET['sortby'])) {
    $sortby = $_GET['sortby'];
    $ordering = $_GET['ordering'];
  } else {
    $sortby = 'moduleid';
    $ordering = 'asc';
  }

  // output table header
  $table_order = array($string['moduleid']=>'moduleid', $string['name']=>'name', $string['school']=>'school', $string['active']=>'active');
  foreach ($table_order as $display => $key) {
    echo '<th><nobr>';
    if ($key == 'moduleid') {
      echo '<div style="padding-left:10px">';
    } else {
      echo '<div><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;';
    }
    if ($sortby == $key and $ordering == 'asc') {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=desc\">$display</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" />&nbsp;</div>";
    } elseif ($sortby == $key and $ordering == 'desc') {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" />&nbsp;</div>";
    } else {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>&nbsp;</div>";
    }
    echo "</nobr></th>\n";
  }
?>
</tr>
<tr><th colspan="4" class="bevel"></th></tr>
<?php
$old_school = '';
$id = 0;
$module_no = 0;

$modules = array();

$result = $mysqli->prepare("SELECT moduleid, fullname, school, active FROM modules, schools WHERE modules.schoolid=schools.id");
$result->execute();
$result->bind_result($moduleid, $fullname, $school, $active);
while ($result->fetch()) {
  $modules[$module_no]['moduleid'] = $moduleid;
  $modules[$module_no]['name'] = $fullname;
  $modules[$module_no]['school'] = $school;
  $modules[$module_no]['active'] = $active;
  
  $module_no++;
}

$modules = array_csort($modules, $sortby, $ordering);
$old_moduleid_letter = '';
$old_name_letter = '';
$old_school = '';
$old_active = '';

for ($i=0; $i<$module_no; $i++) {
  if ($modules[$i]['school'] == '') $modules[$i]['school'] = '<span style="color:#808080">unknown</span>';
  if ($modules[$i]['active'] == 1) {
    $tmp_active = $string['yes'];
  } else {
    $tmp_active = $string['no'];
  }
  if ($sortby == 'school' and $old_school != $modules[$i]['school']) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" class=\"subsect\" style=\"margin-left:10px; width:99%\"><tr><td><nobr>" . $modules[$i]['school'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  } elseif ($sortby == 'moduleid' and $old_moduleid_letter != substr($modules[$i]['moduleid'], 0, 1)) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" class=\"subsect\" style=\"margin-left:10px; width:99%\"><tr><td><nobr>" . substr($modules[$i]['moduleid'], 0, 1) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  } elseif ($sortby == 'name' and $old_name_letter != substr($modules[$i]['name'], 0, 1)) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" class=\"subsect\" style=\"margin-left:10px; width:99%\"><tr><td><nobr>" . substr($modules[$i]['name'], 0, 1) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  } elseif ($sortby == 'school' and $old_school != $modules[$i]['school']) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" class=\"subsect\" style=\"margin-left:10px; width:99%\"><tr><td><nobr>" . substr($modules[$i]['school'], 0, 1) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  } elseif ($sortby == 'active' and $old_active != $modules[$i]['active']) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" class=\"subsect\" style=\"margin-left:10px; width:99%\"><tr><td><nobr>" . $tmp_active . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  }
  echo "<tr id=\"$i\" onclick=\"selMod($i,'" . $modules[$i]['moduleid'] . "',event)\" ondblclick=\"edit('" . $modules[$i]['moduleid'] . "')\" onmouseover=\"lon($i)\" onmouseout=\"loff($i)\" class=\"l\"><td><div class=\"col1\">" . $modules[$i]['moduleid'] . "</div></td><td><div class=\"col\">" . $modules[$i]['name'] . "</div></td><td><div class=\"col\"><nobr>" . $modules[$i]['school'] . "</nobr></div></td><td><div class=\"col\">$tmp_active</div></td></tr>\n";
  
  $old_moduleid_letter = substr($modules[$i]['moduleid'], 0, 1);
  $old_name_letter = substr($modules[$i]['name'], 0, 1);
  $old_school = $modules[$i]['school'];
  $old_active = $modules[$i]['active'];
}
$result->close();
$mysqli->close();
?>
</table>
</div>

</body>
</html>