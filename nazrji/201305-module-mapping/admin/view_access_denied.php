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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['deniedlogwarnings'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  
  <script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script src="../js/staff_help.js" type="text/javascript"></script>
</head>
<body>
<?php
require '../include/admin_options.inc';
  
if (isset($_GET['sortby'])) {
  $sortby = $_GET['sortby'];
} else {
  $sortby = 'tried';
}
if (isset($_GET['ordering'])) {
  $ordering = $_GET['ordering'];
} else {
  $ordering = 'desc';
}
?>

<div id="content" class="content" style="font-size:80%">
<table class="header">
<tr>
<th colspan="4"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['deniedlogwarnings']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<?php
$table_order = array(''=>'', $string['date']=>'tried', $string['user']=>'surname', $string['url']=>'page', $string['message']=>'msg');

foreach ($table_order as $display => $key) {
  if ($key == '') {
    echo "<th style=\"width:20px\">&nbsp;";
  } else {
    echo "<th class=\"vert_div\">&nbsp;";
  }
  if ($sortby == $key and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=desc\">$display</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } elseif ($sortby == $key and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>&nbsp;</th>";
  }
}
?>
<tr><th colspan="5" class="bevel"></th></tr>
<?php

$id = 1;
$result = $mysqli->prepare("SELECT UNIX_TIMESTAMP(tried), ipaddress, page, msg, users.id, users.title, initials, surname FROM denied_log, users WHERE denied_log.userID = users.id ORDER BY $sortby $ordering");
$result->execute();
$result->store_result();
$result->bind_result($tried, $ipaddress, $page, $msg, $userID, $title, $initials, $surname);
while ($result->fetch()) {
  $tried_date = new DateTime();
  $tried_date->setTimestamp($tried);

  echo "<tr class=\"l\"><td><img src=\"../artwork/access_denied_16.gif\" width=\"16\" height=\"16\" /></td><td>" . $tried_date->format($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_long_time_php')) . "</td><td class=\"l\"><a href=\"../users/details.php?search_surname=$surname&search_username=&student_id=&moduleID=&calendar_year=&students=on&submit=Search&userID=$userID&email=&tmp_surname=&tmp_courseID=&tmp_yearID=\">$title $initials $surname</a></td><td class=\"l\">/$page</td><td class=\"l\">$msg</td></tr>\n";
  $id++;
}
?>
</table>
</div>

</body>
</html>
