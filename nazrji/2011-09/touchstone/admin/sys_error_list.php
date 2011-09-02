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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>System Error Report<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
th {background-color:#F1F5FB; font-weight:normal; text-align:left}
.no {text-align:right}
.err {padding-left:6px; vertical-align:top}
.errl {padding-right:6px; vertical-align:top; text-align:right}
</style>

<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="JavaScript">
  function updateCookies() {
    if (document.getElementById('showfixed').checked == 1) {
      setting = " checked";
    } else {
      setting = "";
    }

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    NameOfCookie = "showfixed";
    document.cookie = NameOfCookie + "=" + setting +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());

    window.location = 'sys_error_list.php';
  }

  function selErr(lineID) {
    tmp_ID = document.getElementById('errorID').value;
    if (tmp_ID != '') {
      document.getElementById('link' + tmp_ID).style.backgroundColor = 'white';
    }
    document.getElementById('link' + lineID).style.backgroundColor = '#B3C8E8';
    document.getElementById('errorID').value = lineID;   
  }
  
  function lon(lineID) {
    if (lineID != document.getElementById('errorID').value) {
      document.getElementById('link' + lineID).style.backgroundColor = '#EEEEEE';
    }
  }

  function loff(lineID) {
    if (lineID != document.getElementById('errorID').value) {
      document.getElementById('link' + lineID).style.backgroundColor = '';
    }
  }
</script>
</head>
<body>
<?php
  require '../include/sys_errors_menu.inc';
?>
<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr>
<td colspan="4" style="padding-left:0px; background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['systemerrrorreport']; ?></td>
<td colspan="3" style="padding-left:0px; background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a><br /><div style="padding-top:5px"><input type="checkbox" name="showfixed" id="showfixed" value="1" onclick="updateCookies();"<?php if (isset($_COOKIE['showfixed'])) echo $_COOKIE['showfixed']; ?> /> <?php echo $string['showfixed']; ?></div></td>
</tr>
<tr><th>&nbsp;<?php echo $string['date']; ?></th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['type']; ?></th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['message']; ?></th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['file']; ?></th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['lineno']; ?>&nbsp;</th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['user']; ?></th><th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['userid']; ?></th></tr>
<tr><td colspan="7" style="padding-left:0px; height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>

<?php
  if (isset($_COOKIE['showfixed']) and $_COOKIE['showfixed'] == 'checked') {
    $sql = 'SELECT fixed, sys_errors.id, title, initials, surname, DATE_FORMAT(occurred,\'%d/%m/%y&nbsp;%H:%i\'), errtype, errstr, errfile, errline, users.id FROM sys_errors, users WHERE users.id=sys_errors.userID ORDER BY occurred DESC LIMIT 1000';
  } else {
    $sql = 'SELECT fixed, sys_errors.id, title, initials, surname, DATE_FORMAT(occurred,\'%d/%m/%y&nbsp;%H:%i\'), errtype, errstr, errfile, errline, users.id FROM sys_errors, users WHERE users.id=sys_errors.userID AND fixed IS NULL ORDER BY occurred DESC LIMIT 1000';
  }

  $result = $mysqli->prepare($sql);
  $result->execute();
  $result->store_result();
  $result->bind_result($fixed, $errorID, $title, $initials, $surname, $occurred, $errtype, $errstr, $errfile, $errline, $tmp_userID);
  while ($result->fetch()) {
    if ($fixed == '') {
      echo "<tr onclick=\"selErr($errorID)\" onmouseover=\"lon($errorID)\" onmouseout=\"loff($errorID)\" id=\"link$errorID\"><td class=\"err\">$occurred</td><td class=\"err\">$errtype</td><td class=\"err\">$errstr</td><td class=\"err\">$errfile</td><td class=\"errl\">$errline</td><td class=\"err\">$title&nbsp;$initials&nbsp;$surname</td><td class=\"err\">$tmp_userID</td></tr>\n";
    } else {
      echo "<tr onclick=\"selErr($errorID)\" onmouseover=\"lon($errorID)\" onmouseout=\"loff($errorID)\" id=\"link$errorID\" style=\"color:#808080\"><td class=\"err\">$occurred</td><td class=\"err\">$errtype</td><td class=\"err\">$errstr</td><td class=\"err\">$errfile</td><td class=\"errl\">$errline</td><td class=\"err\">$title&nbsp;$initials&nbsp;$surname</td><td class=\"err\">$tmp_userID</td></tr>\n";
    }
  }
?>
</table>

</div>
</body>
</html>
