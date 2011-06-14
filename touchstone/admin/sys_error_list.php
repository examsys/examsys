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
<title>System Error Report<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
th {background-color:#F1F5FB; font-weight:normal; text-align:left}
.no {text-align:right}
.err {padding-left:6px; vertical-align:top}
</style>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
</head>
<body>
<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr>
<td colspan="6" style="padding-left:0px; background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">Administrative Tools</a></div><div style="margin-left:10px; font-size:200%; font-weight:bold">System Error Report</td>
<td style="padding-left:0px; background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
</tr>
<tr><th>&nbsp;Date</th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Type</th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Message</th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;File</th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Line&nbsp;No.&nbsp;</th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;User</th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;UserID</th></tr>
<tr><td colspan="7" style="padding-left:0px; height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>

<?php
  $result = $mysqli->prepare("SELECT title, initials, surname, DATE_FORMAT(occurred,'%d/%m/%y&nbsp;%H:%i'), errtype, errstr, errfile, errline, users.id FROM sys_errors, users WHERE users.id=sys_errors.userID ORDER BY occurred DESC LIMIT 1000");
  $result->execute();
  $result->store_result();
  $result->bind_result($title, $initials, $surname, $occurred, $errtype, $errstr, $errfile, $errline, $tmp_userID);
  while ($result->fetch()) {
    echo "<tr><td class=\"err\">$occurred</td><td class=\"err\">$errtype</td><td class=\"err\">$errstr</td><td class=\"err\">$errfile</td><td class=\"err\">$errline</td><td class=\"err\">$title&nbsp;$initials&nbsp;$surname</td><td class=\"err\">$tmp_userID</td></tr>\n";
  }
?>
</table>

</div>
</body>
</html>
