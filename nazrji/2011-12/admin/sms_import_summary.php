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
<title><?php echo $string['smsimportsummary'] . ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
th {background-color:#F1F5FB; font-weight:normal}
.no {text-align:right}
</style>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="javascript">
  function lon(lineID) {
    if (lineID != document.myform.oldDivID.value) {
      document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
    }
  }

  function loff(lineID) {
    if (lineID != document.myform.oldDivID.value) {
      document.getElementById(lineID).style.backgroundColor = '';
    }
  }
</script>
</head>
<body>
<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr>
<td colspan="4" style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['smsimportsummary']; ?></td>
<td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></td>
</tr>
<tr><th><?php echo $string['date']; ?></th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['modules']; ?></th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['enroled']; ?></th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['deleted']; ?></th><th style="width:50%">&nbsp;</th></tr>
<tr><td colspan="5" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>

<?php
  $id = 1;
  $result = $mysqli->prepare("SELECT DATE_FORMAT(updated,'%Y%m%d'), DATE_FORMAT(updated,'%d/%m/%Y'), COUNT(id), SUM(enrolements), SUM(deletions) FROM sms_imports GROUP BY updated ORDER BY updated DESC");
  $result->execute();
  $result->store_result();
  $result->bind_result($updated, $display_updated, $module_no, $enrolement_no, $deletion_no);
  while ($result->fetch()) {
    echo "<tr id=\"$id\" onclick=\"window.location='sms_import_detail.php?day=$updated';\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td style=\"padding-left:10px\">$display_updated</td><td class=\"no\">$module_no</td><td class=\"no\">$enrolement_no</td><td class=\"no\">$deletion_no</td><td>&nbsp;</td></tr>\n";
    $id++;
  }
?>
</table>

</div>
<form name="myform">
<input type="hidden" name="oldDivID" value="" />
</form>
</body>
</html>
