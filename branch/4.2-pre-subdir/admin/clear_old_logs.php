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
  require '../include/sidebar_menu.inc';
  set_time_limit(0);
  ob_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['clearoldlogs']; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<script language="JavaScript" src="../js/staff_help.js"></script>
<script language="JavaScript" src="../js/sidebar.js"></script>
</head>

<body>

<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><?php echo $string['clearoldlogs']; ?></div></td><td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(239); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></td></tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>

<?php
  ob_flush();
  flush();

  $log0_deleted = 0;
  $log1_deleted = 0;

  $stmt = $mysqli->prepare("SELECT id FROM users WHERE roles='left' OR roles='graduate'");
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($userID);
  while ($row = $stmt->fetch()) {
    // Delete from formative log.
    $deletequery = $mysqli->prepare("DELETE FROM log0 WHERE userID=?");
    $deletequery->bind_param('s', $userID);
    $deletequery->execute();
    $log0_deleted += $deletequery->affected_rows;
    $deletequery->close();
    
    // Delete from progress test log.
    $deletequery = $mysqli->prepare("DELETE FROM log1 WHERE userID=?");
    $deletequery->bind_param('s', $userID);
    $deletequery->execute();
    $log1_deleted += $deletequery->affected_rows;
    $deletequery->close();
    
    // Reset passwords
    if ($cfg_use_ldap) {
      $updatequery = $mysqli->prepare("UPDATE users SET password='' WHERE roles IN('Student','graduate','left')");
    } else {
      $updatequery = $mysqli->prepare("UPDATE users SET password='' WHERE roles IN('graduate','left')");
    }
    $updatequery->execute();
    $updatequery->close();
  }
  $stmt->close();

  echo "<blockquote>\n<div>" . $string['log0deleted'] . " $log0_deleted</div>";
  echo "<div>" . $string['log1deleted'] . " $log1_deleted</div>\n</blockquote>";
?>
</div>

</body>
</html>
<?php
  $mysqli->close();
  ob_end_flush();
?>