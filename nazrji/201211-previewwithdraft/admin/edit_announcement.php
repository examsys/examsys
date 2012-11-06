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

if (isset($_POST['save'])) {
  $news_title = trim($_POST['title']);
  $staff_msg = $_POST['staff_msg'];
  $student_msg = $_POST['student_msg'];
  $startdate = $_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'] . '00';
  $enddate = $_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['ttime'] . '00';
  $icon = $_POST['icon'];
  $announcementid = $_POST['announcementid'];
  
  $result = $mysqli->prepare("UPDATE announcements SET title=?, staff_msg=?, student_msg=?, icon=?, startdate=?, enddate=? WHERE id=?");
  $result->bind_param('ssssssi', $news_title, $staff_msg, $student_msg, $icon, $startdate, $enddate, $announcementid);
  $result->execute();  
  $result->close();
  
  $mysqli->close();
  header("location: list_announcements.php");
  exit;
}

$result = $mysqli->prepare("SELECT title, staff_msg, student_msg, icon, DATE_FORMAT(startdate, '%Y%m%d%H%i'), DATE_FORMAT(enddate, '%Y%m%d%H%i') FROM announcements WHERE id=?");
$result->bind_param('i', $_GET['announcementid']);
$result->execute();
$result->bind_result($news_title, $staff_msg, $student_msg, $news_icon, $startdate, $enddate);
$result->fetch(); 
$result->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $string['editannouncement']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    h1 {font-size:120%}
    .f {text-align:right; padding-right:6px; width:125px}
  </style>
<?php
// Override this variable with a specific configuration file for announcements.
$cfg_editor_javascript = <<< SCRIPT
$cfg_js_root
<script type="text/javascript" src="$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_config_announcements.js"></script>
SCRIPT;

  echo $cfg_editor_javascript;
?>
  <script type="text/javascript" src="../../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
</head>

<body>
<?php
  require '../include/announcement_options.inc';
?>
<div id="content" class="content">
<table class="header">
<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['editannouncement']; ?></div></th><th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th></tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<br />

<br />
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table style="width:875px; margin-left:auto; margin-right:auto; font-size:110%">
<tr>
<td></td><td>
<?php
$icons = array(1=>'news_64.png', 2=>'new_64.png', 3=>'tip_64.png', 4=>'software_64.png', 5=>'exclamation_64.png', 6=>'sync_64.png', 7=>'megaphone_64.png');

for ($i=1; $i<=7; $i++) {
  if ($i == $news_icon) {
    echo "<input type=\"radio\" name=\"icon\" value=\"$i\" checked=\"checked\" /><img src=\"../artwork/" . $icons[$i] . "\" width=\"64\" height=\"64\" border=\"0\" />&nbsp;&nbsp;&nbsp;";
  } else {
    echo "<input type=\"radio\" name=\"icon\" value=\"$i\" /><img src=\"../artwork/" . $icons[$i] . "\" width=\"64\" height=\"64\" border=\"0\" />&nbsp;&nbsp;&nbsp;";
  }
}
?>
</td>
</tr>
<tr>
<td class="f"><?php echo $string['Title']; ?></td><td><input type="text" name="title" size="60" value="<?php echo $news_title; ?>" /></td>
</tr>
<tr>
<td class="f"><?php echo $string['Available from']; ?></td><td><?php echo date_utils::timedate_select('f', $startdate); ?></td>
</tr>
<tr>
<td class="f"><?php echo $string['Available to']; ?></td><td><?php echo date_utils::timedate_select('t', $enddate); ?></td>
</tr>
<tr>
<td class="f"><?php echo $string['Staff Message']; ?></td><td><textarea class="mceEditor" id="staff_msg" name="staff_msg" style="width:750px; height:70px; margin: 0" rows="5" cols="20"><?php echo $staff_msg; ?></textarea></td>
</tr>
<tr>
<td class="f"><?php echo $string['Student Message']; ?></td><td><textarea class="mceEditor" id="student_msg" name="student_msg" style="width:750px; height:70px; margin: 0" rows="5" cols="20"><?php echo $student_msg; ?></textarea></td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td colspan="2" style="text-align:center"><input type="submit" name="save" value="<?php echo $string['save']; ?>" style="width:100px" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:100px" onclick="history.back();" /></td>
</tr>
</table>
<input type="hidden" name="announcementid" value="<?php echo $_GET['announcementid']; ?>" />
</form>
</div>
</body>
</html>
<?php
$mysqli->close();
?>