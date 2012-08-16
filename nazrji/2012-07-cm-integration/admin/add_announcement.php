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
require '../include/errors.inc';
require_once '../classes/dateutils.class.php';

if (isset($_POST['ok']) or (isset($_POST['returnhit']) and $_POST['returnhit'] == '1')) {
  $title = trim($_POST['title']);
  $staff_msg = $_POST['staff_msg'];
  $student_msg = $_POST['student_msg'];
  $startdate = $_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'] . '00';
  $enddate = $_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['ttime'] . '00';
  $icon = $_POST['icon'];
  
  $result = $mysqli->prepare("INSERT INTO announcements VALUES (NULL, ?, ?, ?, ?, ?, ?, NULL)");
  $result->bind_param('ssssss', $title, $staff_msg, $student_msg, $icon, $startdate, $enddate);
  $result->execute();  
  $result->close();
  
  $mysqli->close();
  header("location: list_announcements.php");
  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html; charset=<?php echo $cfg_page_charset ?>" />
<title><?php echo $string['addannouncement']; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:100%; background-color:white; color:black}
textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
h1 {font-size:120%}
.field {text-align:right; padding-right:6px; width:125px}
</style>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<?php
// Override this variable with a specific configuration file for announcements.
$cfg_editor_javascript = <<< SCRIPT
$cfg_js_root
<script type="text/javascript" src="$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_config_announcements.js"></script>
SCRIPT;

  echo $cfg_editor_javascript;
?>
<script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
<script type="text/javascript" src="../js/staff_help.js"></script>

</head>

<body>
<?php
  require '../include/announcement_options.inc';
?>
<div id="content" class="content">
<table class="header">
<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['addannouncement']; ?></div></th><th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th></tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<br />

<br />
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table style="width:875px; margin-left:auto; margin-right:auto; font-size:110%">
<tr>
<td></td><td>
<input type="radio" name="icon" value="1" checked="checked" /><img src="../artwork/news_64.png" width="64" height="64" border="0" />
&nbsp;&nbsp;&nbsp;
<input type="radio" name="icon" value="2" /><img src="../artwork/new_64.png" width="64" height="64" border="0" />
&nbsp;&nbsp;&nbsp;
<input type="radio" name="icon" value="3" /><img src="../artwork/tip_64.png" width="64" height="64" border="0" />
&nbsp;&nbsp;&nbsp;
<input type="radio" name="icon" value="4" /><img src="../artwork/software_64.png" width="64" height="64" border="0" />
&nbsp;&nbsp;&nbsp;
<input type="radio" name="icon" value="5" /><img src="../artwork/exclamation_64.png" width="64" height="64" border="0" />
&nbsp;&nbsp;&nbsp;
<input type="radio" name="icon" value="6" /><img src="../artwork/sync_64.png" width="64" height="64" border="0" />
&nbsp;&nbsp;&nbsp;
<input type="radio" name="icon" value="7" /><img src="../artwork/megaphone_64.png" width="64" height="64" border="0" />
</td>
</tr>
<tr>
<td class="field">Title</td><td><input type="text" name="title" size="60" /></td>
</tr>
<tr>
<td class="field">Available from</td><td><?php echo date_utils::timedate_select('f', date('YmdH00')); ?></td>
</tr>
<tr>
<td class="field">Available to</td><td><?php echo date_utils::timedate_select('t', date('YmdH00')); ?></td>
</tr>
<tr>
<td class="field">Staff Message</td><td><textarea class="mceEditor" id="staff_msg" name="staff_msg" style="width:750px; height:70px; margin: 0" rows="5" cols="20"></textarea></td>
</tr>
<tr>
<td class="field">Student Message</td><td><textarea class="mceEditor" id="student_msg" name="student_msg" style="width:750px; height:70px; margin: 0" rows="5" cols="20"></textarea></td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td colspan="2" style="text-align:center"><input type="submit" name="ok" value="<?php echo $string['ok']; ?>" style="width:100px" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:100px" onclick="history.back();" /></td>
</tr>
</table>

</form>
</div>
</body>
</html>
<?php
$mysqli->close();
?>