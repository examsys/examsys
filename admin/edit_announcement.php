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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require '../include/errors.php';

$announcementid = check_var('announcementid', 'REQUEST', true, false, true);

if (!announcement_utils::announcement_exist($announcementid, $mysqli)) {
  $contactemail = support::get_email();
  $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['save'])) {
  $news_title = trim($_POST['title']);
  $staff_msg = $_POST['staff_msg'];
  $student_msg = $_POST['student_msg'];
  $startdate = $_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'];
  $enddate = $_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['ttime'];
  $icon = str_replace('icon', '', $_POST['icon_type']);
  
  $result = $mysqli->prepare("UPDATE announcements SET title = ?, staff_msg = ?, student_msg = ?, icon = ?, startdate = ?, enddate = ? WHERE id = ?");
  $result->bind_param('ssssssi', $news_title, $staff_msg, $student_msg, $icon, $startdate, $enddate, $announcementid);
  $result->execute();  
  $result->close();
  
  $mysqli->close();
  header("location: list_announcements.php");
  exit();
}

$result = $mysqli->prepare("SELECT title, staff_msg, student_msg, icon, DATE_FORMAT(startdate, '%Y%m%d%H%i'), DATE_FORMAT(enddate, '%Y%m%d%H%i') FROM announcements WHERE id = ?");
$result->bind_param('i', $announcementid);
$result->execute();
$result->bind_result($news_title, $staff_msg, $student_msg, $news_icon, $startdate, $enddate);
$result->fetch(); 
$result->close();

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rog&#333;: <?php echo $string['editannouncement']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/announcement.css" />
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<?php
$texteditorplugin = \plugins\plugins_texteditor::get_editor();
$texteditorplugin->display_header();
$texteditorplugin->get_javascript_config(\plugins\plugins_texteditor::ANNOUNCEMENTS);

?>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
   
      $('#cancel').click(function() {
        history.back();
      });
      
      $('.icon').click(function() {
        current = $('#icon_type').val();
        $('#' + current).css('border-color', 'white');

        newvalue = $(this).attr('id');
        $('#' + newvalue).css('border-color', '#FFBD69');
        $('#icon_type').val(newvalue)
        
      });
      
    });
  </script>
</head>

<body>
<?php
  require '../include/announcement_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools']; ?></a></div>
  <div class="page_title"><?php echo $string['editannouncement']; ?></div>
</div>

<form id="theform" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">

<table class="tblform">
<tr>
<td></td><td>
<?php
$icons = array(1=>'news_64.png', 2=>'new_64.png', 3=>'tip_64.png', 4=>'software_64.png', 5=>'exclamation_64.png', 6=>'sync_64.png', 7=>'megaphone_64.png');

for ($i=1; $i<=7; $i++) {
  if ($i == $news_icon) {
    echo "<img class=\"icon\" id=\"icon$i\" src=\"../artwork/" . $icons[$i] . "\" style=\"border-color:#FFBD69\" />";
  } else {
    echo "<img class=\"icon\" id=\"icon$i\" src=\"../artwork/" . $icons[$i] . "\" />";
  }
}

if (substr($startdate, 0, 4) < date('Y')) {
  $start_year = substr($startdate, 0, 4);
} else {
  $start_year = date('Y');
}
?>
</td>
</tr>
<tr>
<td class="field"><?php echo $string['Title'] ?></td><td><input type="text" name="title" size="60" maxlength="255" value="<?php echo $news_title ?>" required /></td>
</tr>
<tr>
<td class="field"><?php echo $string['Available from'] ?></td><td><?php echo date_utils::timedate_select('f', $startdate, false, $start_year, date('Y')+2, $string) ?></td>
</tr>
<tr>
<td class="field"><?php echo $string['Available to'] ?></td><td><?php echo date_utils::timedate_select('t', $enddate, false, $start_year, date('Y')+2, $string) ?></td>
</tr>
<tr>
<td class="field"><?php echo $string['Staff Message'] ?></td><td><?php $texteditorplugin->get_textarea('staff_msg', 'staff_msg', $staff_msg, plugins\plugins_texteditor::TYPE_STANDARD); ?></td>
</tr>
<tr>
<td class="field"><?php echo $string['Student Message'] ?></td><td><?php $texteditorplugin->get_textarea('student_msg', 'student_msg', $student_msg, plugins\plugins_texteditor::TYPE_STANDARD); ?></td>
</tr>
<tr>
<td colspan="2" style="text-align:center; padding-top:10px"><input type="submit" name="save" value="<?php echo $string['save'] ?>" class="ok" /><input type="button" name="cancel" id="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" /></td>
</tr>
</table>

<input type="hidden" id="icon_type" name="icon_type" value="icon<?php echo $news_icon ?>" />
<input type="hidden" name="announcementid" value="<?php echo $announcementid ?>" />
</form>
</div>
  
</body>
</html>
<?php
$mysqli->close();
?>