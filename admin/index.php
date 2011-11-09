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
  require '../include/sidebar_menu.inc';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Rogō: Admin<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
  a.highlight {color:black}
  a.highlight:hover {background-color:#000080; color:white}
  .icon {width:250px; padding-top:20px; padding-bottom:20px; float:left; text-align:center}
</style>

<script language="JavaScript" src="../javascript/sidebar.js"></script>
<script language="JavaScript">
  function highlightResource(resourceID,highlightColor) {
    document.getElementById(resourceID).style.borderColor = '#316AC5';
    document.getElementById('text' + resourceID).style.backgroundColor = highlightColor;
    document.getElementById('text' + resourceID).style.color = 'white';
  }
  
  function unhighlightResource(resourceID) {
    document.getElementById(resourceID).style.borderColor = '#EEEEEE';
    document.getElementById('text' + resourceID).style.backgroundColor = 'white';
    document.getElementById('text' + resourceID).style.color = 'black';
  }
  
  function callPage(targetPage) {
    var msg = '';
    if (targetPage == 'clear_training_module.php') {
      msg = '<?php echo $string['msg1']; ?>';
    } else if (targetPage == 'clear_old_logs.php') {
      msg = '<?php echo $string['msg2']; ?>';
    }
    if (msg != '') {
      var r=confirm(msg);
      if (r==true) {
        window.location=targetPage;
      }
    } else {
      window.location=targetPage;
    }
  }
</script>
</head>

<body>

<?php
  require '../include/admin_options.inc';
  
  // How many guest accounts are reserved
  $results = $mysqli->query("SELECT id FROM temp_users");
  $temp_account_no = $results->num_rows;
  $results->close();
  
  // How many system errors are there
  $results = $mysqli->query("SELECT id FROM sys_errors WHERE fixed IS NULL");
  $sys_error_no = $results->num_rows;
  $results->close();

  $mysqli->close();
?>

<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['administrativetools']; ?></div></td></tr>
<tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>

<?php
  $added_string = '';
  if ($temp_account_no > 0) {
    $added_string = ' <span style="background-color:red; color:white; font-weight:bold">&nbsp;' . $temp_account_no . '&nbsp;</span>';
  }
  $err_added_string = '';
  if ($sys_error_no > 0) {
    $err_added_string = ' <span style="background-color:red; color:white; font-weight:bold">&nbsp;' . $sys_error_no . '&nbsp;</span>';
  }
  $titles = array($string['calendar'],$string['clearguestaccounts'] . $added_string,$string['clearoldlogs'],$string['clearorphanmedia'],$string['cleartraining'],$string['computerlabs'],$string['courses'],$string['ebelgridtemplates'],$string['faculties'],$string['modules'],$string['optimizetables'],$string['schools'],$string['smsimports'],$string['summativeexamstats'],$string['systemerrors'] . $err_added_string,$string['systeminformation'],$string['trac'],$string['usermanagement']);
  $paths = array('calendar.php#' . date("n"),'clear_guest_users.php','clear_old_logs.php','orphan_media.php','clear_training_module.php','list_labs.php','list_courses.php','list_ebel_grids.php','list_faculties.php','list_modules.php','optimize_tables.php','list_schools.php','sms_import_summary.php','summative_stats.php?year=' . date('Y'),'sys_error_list.php','system_info.php','https://suivarro.nottingham.ac.uk/trac/rogo/','../users/search.php');
  $images = array('calendar_icon.png','clear_guest_users.png','clear_logs.png','remove_orphan_icon.png','training.png','computer_lab_48.png','courses_icon.png','grid_48.png','faculty.png','modules_icon.png','optimize_tables_icon.png','school_icon.png','sms_import_icon.png','summative_stats.png','bug.png','information.png','trac_logo.png','user_accounts_icon.png');

  for ($icon_no=0; $icon_no<count($titles); $icon_no++) {
    echo "<div class=\"icon\"><table align=\"center\" id=\"" . $icon_no . "\" onmouseover=\"highlightResource('" . $icon_no . "','#316AC5')\" onmouseout=\"unhighlightResource('" . $icon_no . "')\" onclick=\"callPage('" . $paths[$icon_no] . "')\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"cursor:hand; background-color:white; width:95px; height:95px; border:1px solid #EEEEEE; text-align:center; vertical-align:middle\"><tr><td style=\"text-align:center\">";
    echo "<img src=\"../artwork/" . $images[$icon_no] . "\" width=\"48\" height=\"48\" border=\"0\" alt=\"\"  />";
    echo "</td></tr></table><span id=\"text" . $icon_no . "\" style=\"cursor:hand\" onmouseover=\"highlightResource('" . $icon_no . "','#000080')\" onmouseout=\"unhighlightResource('" . $icon_no . "')\" onclick=\"callPage('" . $paths[$icon_no] . "')\">&nbsp;" . $titles[$icon_no] . "&nbsp;</span></div>";
  }
?>

</div>

</body>
</html>