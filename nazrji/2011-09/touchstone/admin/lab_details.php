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

  require '../include/staff_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Lab Details</title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
.foldername {float:left; width:380px; height:60px; padding-left:12px; font-size:80%}
</style>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
</head>

<body>
<?php
  require '../include/lab_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<?php
  $results = $mysqli->prepare("SELECT name, address, hostname, campus, building, room_no, timetabling, it_support, plagarism, low_bandwidth FROM (ip_addresses, labs) WHERE ip_addresses.lab=labs.id AND labs.id=?");
  $results->bind_param('i', $_GET['labID']);
  $results->execute();
  $results->store_result();
  $results->bind_result($name, $address, $hostname, $campus, $building, $room_no, $timetabling, $it_support, $plagarism, $low_bandwidth);
  $ip_no = 0;
  while ($row = $results->fetch()) {
    if ($ip_no == 0) {
      echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
      echo "<tr><td style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../index.php\">Home</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./index.php\">Administrative Tools</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./list_labs.php\">Computer Labs</a></div><div style=\"font-size:220%; font-weight:bold; margin-left:10px\">$name</div></td>\n";
      echo "<td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(231); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
      echo "<tr><td colspan=\"2\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n</table>\n";
      echo "<br />\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; margin-left:10px; margin-right:10px\">\n<tr><td style=\"vertical-align:top; width:440px\"><div><strong>IP Addresses (" . $results->num_rows . ")</strong></div>\n<div style=\"height:590px; overflow-y:scroll; border: 1px solid #EEEDE5\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
    }
    
    if ($address == $hostname) {
      echo "<tr><td><img src=\"../artwork/screen_icon.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"PC icon\" />&nbsp;</td><td style=\"width:135px; color:red\">$address</td><td style=\"color:red\">$hostname</td></tr>\n";
    } else {
      echo "<tr><td><img src=\"../artwork/screen_icon.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"PC icon\" />&nbsp;</td><td style=\"width:135px\">$address</td><td style=\"color:#808080\">$hostname</td></tr>\n";
    }
    
    $ip_no++;
  }
  $results->close();
  echo "</table></div></td><td style=\"width:50px\"></td><td style=\"vertical-align:top\">\n";
  echo "<div><strong>Campus</strong></div>\n<div>$campus</div>\n";
  echo "<br /><div><strong>Building</strong></div>\n<div>$building</div>\n";
  echo "<br /><div><strong>Room Number</strong></div>\n<div>$room_no</div>\n";
  if ($low_bandwidth == 0) {
    echo "<br /><div><strong>Bandwidth</strong></div>\n<span style=\"background-color:#008000; color:white\">&nbsp;High&nbsp;</span><br />\n";
  } else {
    echo "<br /><div><strong>Bandwidth</strong></div>\n<span style=\"background-color:#C00000; color:white\">&nbsp;Low&nbsp;</span><br />\n";
  }
  echo "<br /><div><strong>Timetabling</strong></div>\n<div>$timetabling</div>\n";
  echo "<br /><div><strong>IT Support</strong></div>\n<div>$it_support</div>\n";
  echo "<br /><div><strong>Plagarism</strong></div>\n<div>$plagarism</div>\n";
  if (strpos($userroles,'SysAdmin') !== false or strpos($userroles,'Admin') !== false) {
    echo "<br /><br /><input type=\"button\" onclick=\"window.location='edit_lab.php?labID=" . $_GET['labID'] . "'\" value=\"Edit\" style=\"width:120px\" />\n";
  }
  echo "</td></tr>\n</table>\n";
  $mysqli->close();
?>
</div>
</body>
</html>