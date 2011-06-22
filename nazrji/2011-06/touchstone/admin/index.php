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
  require '../include/sidebar_menu.inc';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone: Admin<?php echo " $cfg_install_type"; ?></title>
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
      msg = 'Are you sure you wish to clear all papers/questions form the Training module?';
    } else if (targetPage == 'clear_old_logs.php') {
      msg = 'Are you sure you wish to delete old Formative and Progress Test records?\n\n(Summative assessment records will not be affected)';
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
  
  $results = $mysqli->query("SELECT id FROM temp_users");
  $temp_account_no = $results->num_rows;
  $results->close();

  $mysqli->close();
?>

<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a></div><div style="margin-left:10px; font-size:200%; font-weight:bold">Administrative Tools</div></td></tr>
<tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>

<?php
  $added_string = '';
  if ($temp_account_no > 0) {
    $added_string = ' <span style="background-color:red; color:white; font-weight:bold">&nbsp;' . $temp_account_no . '&nbsp;</span>';
  }
  $titles = array('Calendar','Clear Guest Accounts' . $added_string,'Clear Old Logs','Clear Orphan Media','Clear Training','Computer Labs','Degrees','Modules','Optimize Tables','SMS Imports','System Errors','System Information','Trac (issue tracking)','User Management');
  $paths = array('calendar.php#' . date("n"),'clear_guest_users.php','clear_old_logs.php','orphan_media.php','clear_training_module.php','list_labs.php','list_degrees.php','list_modules.php','optimize_tables.php','sms_import_summary.php','sys_error_list.php','system_info.php','https://tiberius.nottingham.ac.uk/trac/touchstone/','../users/search.php');
  $images = array('calendar_icon.png','clear_guest_users.png','clear_logs.png','remove_orphan_icon.png','training.png','computer_lab_48.png','degrees_icon.png','modules_icon.png','optimize_tables_icon.png','sms_import_icon.png','orange_alert_48.png','information.png','trac_logo.png','user_accounts_icon.png');

  for ($icon_no=0; $icon_no<count($titles); $icon_no++) {
    echo "<div class=\"icon\"><table align=\"center\" id=\"" . $icon_no . "\" onmouseover=\"highlightResource('" . $icon_no . "','#316AC5')\" onmouseout=\"unhighlightResource('" . $icon_no . "')\" onclick=\"callPage('" . $paths[$icon_no] . "')\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"cursor:hand; background-color:white; width:95px; height:95px; border:1px solid #EEEEEE; text-align:center; vertical-align:middle\"><tr><td style=\"text-align:center\">";
    echo "<img src=\"../artwork/" . $images[$icon_no] . "\" width=\"48\" height=\"48\" border=\"0\" alt=\"\"  />";
    echo "</td></tr></table><span id=\"text" . $icon_no . "\" style=\"cursor:hand\" onmouseover=\"highlightResource('" . $icon_no . "','#000080')\" onmouseout=\"unhighlightResource('" . $icon_no . "')\" onclick=\"callPage('" . $paths[$icon_no] . "')\">&nbsp;" . $titles[$icon_no] . "&nbsp;</span></div>";
  }
?>

</div>

</body>
</html>