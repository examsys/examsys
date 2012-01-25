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
* @author Anthony Brown, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';  
require '../include/mapping.inc';

if (isset($_POST['submit'])) {
  //get the data from the NLE
  $sessions = getObjectives($_POST['NLE_moduleid'],$_POST['nle_session'],'','',$mysqli);
  $i = 1;
  $moduleID = $_POST['moduleid'];
  foreach ($sessions[$_POST['NLE_moduleid']] as $nleid => $s) {
    $identifier =  $nleid;
    $title = $s['class_code'] . ': ' . $s['title'];
    $url = $s['source_url'];
    $occurrence = $s['occurrance'];

    $stmt = $mysqli->prepare("INSERT INTO sessions VALUES (NULL,?,?,?,?,?,?)");
    $stmt->bind_param('ssssss', $identifier, $moduleID, $title, $url, $_POST['academic_session'], $occurrence);
    $stmt->execute();
    $stmt->close();
	
    foreach ($s['objectives'] as $obj) {
      $stmt = $mysqli->prepare("INSERT INTO objectives VALUES (?,?,?,?,?,?)");
      $stmt->bind_param('issssi', $i, $obj['content'], $moduleID, $identifier, $_POST['academic_session'], $i);
      $stmt->execute();
      $stmt->close();
      $i++;
    }
  }
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/mapping/sessions_list.php?module=$moduleID");
  exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Rogō: Manage Objectives<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style style="text/css">
    img {border:none}
    .editBox {width:90%}
    .field {text-align:right; font-weight:bold}
    .note {width:90%}
  </style>
</head>

<body onclick="hideSessCopyMenu(event);">
<?php
  require '../include/sessions_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<?php
  $module = $_GET['module'];

  $stmt = $mysqli->prepare("SELECT calendar_year FROM student_modules, modules WHERE student_modules.moduleid=modules.moduleid AND student_modules.moduleid=? ORDER BY calendar_year DESC LIMIT 1");
  $stmt->bind_param('s', $module);
  $stmt->execute();
  $stmt->bind_result($session);
  $stmt->fetch();
  $stmt->close();
    
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n";
  echo "<tr><td style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">Home</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../folder/details.php?module=$module\">$module</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./sessions_list.php?module=$module\">Manage Objectives</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>Import from NLE</strong></div></td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(0); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
  echo "<tr><td colspan=\"2\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
  echo "</table>\n";
    //display the form
    ?>
<br />
<br />

<div align="center">

<table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #C0C0C0; width:600px">
<tr>
<td valign="middle" align="left" style="background-color:white"><img src="../artwork/import.gif" width="32" height="32" alt="Icon" />&nbsp;&nbsp;<span style="font-size:160%; font-weight:bold; color:#5582D2">Import Objectives from NLE into <?php echo $_GET['module']; ?></span></td>
</tr>
<tr>
<td align="left" style="background-color:#EEEEEE">

<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">

<table cellpadding="3" cellspacing="0" border="0" style="text-align:left">
<tr><td colspan="2"><strong>NLE</strong></td></tr>
<tr>
<td style="text-align:right">Module</td><td>
<select name="NLE_moduleid">
<option value="">-select module-</option>
    <?php
      $result = $mysqli->prepare("SELECT moduleid, fullname FROM modules WHERE vle_api='NLE' AND active=1 ORDER BY moduleid");
      $result->execute();
      $result->bind_result($moduleid, $fullname);
      while ($row = $result->fetch()) {
        echo "<option value=\"$moduleid\">$moduleid: $fullname</option>";
      }
    ?>
  </select>
  </td>
</tr>

<tr>
<?php

  echo "<td style=\"text-align:right\">Session</td><td><select name=\"nle_session\">\n";
  $startyear = ( date('Y') - 1 );
  for ($i = 0; $i < 2; $i++){
    $tmp_session = ($startyear + $i) . '/' . substr(($startyear + $i + 1),2);
    if ($tmp_session == $session) {
      echo "<option value=\"$tmp_session\" selected>$tmp_session</option>\n";
    } else {
      echo "<option value=\"$tmp_session\">$tmp_session</option>\n";
    }
  }
  echo "</select></td>\n";
?>
</tr>
<tr><td colspan="2"><strong><?php echo $_GET['module']; ?></strong></td></tr>
<tr>
<?php

  echo "<td style=\"text-align:right\">Session</td><td><select name=\"academic_session\">\n";
  $startyear = ( date('Y') - 1 );
  for ($i = 0; $i < 2; $i++){
    $tmp_session = ($startyear + $i) . '/' . substr(($startyear + $i + 1),2);
    if ($tmp_session == $session) {
      echo "<option value=\"$tmp_session\" selected>$tmp_session</option>\n";
    } else {
      echo "<option value=\"$tmp_session\">$tmp_session</option>\n";
    }
  }
  echo "</select></td>\n";
?>
</tr>

<tr><td colspan="2">&nbsp;<input type="hidden" name="moduleid" value="<?php echo $_GET['module']; ?>" /></td></tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" style="width:100px" value="Import" name="submit" />&nbsp;<input style="width:100px" type="button" value="Cancel" name="cancel" onclick="history.go(-1)" /></td></tr>
</form>
</div>
</td>
</tr>
</table>

</div>

<?php	
  $mysqli->close();
?>
</div>

</body>
</html>