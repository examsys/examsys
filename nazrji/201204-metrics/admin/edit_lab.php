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
  require '../config/campuses.inc';

  if (isset($_POST['submit'])) {
    // Delete the existing IP addresses for the lab first.
    $result = $mysqli->prepare("DELETE FROM ip_addresses WHERE lab=?");
    $result->bind_param('i', $_GET['labID']);
    $result->execute();  
    $result->close();

    // Insert the new IP addresses.
    $addresses = explode('<br />',nl2br($_POST['addresses']));
    foreach ($addresses as $individual_address) {
      $ip_address = trim($individual_address);
      if ($ip_address != '') {
        $hostname = gethostbyaddr($ip_address);
        $result = $mysqli->prepare("INSERT INTO ip_addresses VALUES (NULL, ?, ?, ?, ?)");
        $result->bind_param('issi', $_GET['labID'], $ip_address, $hostname, $_POST['low_bandwidth']);
        $result->execute();  
        $result->close();
      }
    }
    
    // Edit Lab table.
    $result = $mysqli->prepare("UPDATE labs SET name=?, campus=?, building=?, room_no=?, timetabling=?, it_support=?, plagarism=? WHERE id=?");
    $result->bind_param('sssssssi', $_POST['name'], $_POST['campus'], $_POST['building'], $_POST['room_no'], $_POST['timetabling'], $_POST['it_support'], $_POST['plagarism'], $_GET['labID']);
    $result->execute();  
    $result->close();

    header("location: lab_details.php?labID=" . $_GET['labID']);
  } else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title><?php echo $string['editcomputerlab']; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
input, textarea {font-family:Arial,sans-serif; line-height:140%}
</style>
<script src="../js/staff_help.js" type="text/javascript"></script>
</head>

<body>
<?php
  require '../include/lab_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<form action="<?php echo $_SERVER['PHP_SELF'] . '?labID=' . $_GET['labID']; ?>" method="post">

<?php
  $ip_no = 0;
  $result = $mysqli->prepare("SELECT name, address, campus, building, room_no, timetabling, it_support, plagarism, low_bandwidth FROM (ip_addresses, labs) WHERE ip_addresses.lab=labs.id AND labs.id=?");
  $result->bind_param('i', $_GET['labID']);
  $result->execute();
  $result->bind_result($name, $address, $campus, $building, $room_no, $timetabling, $it_support, $plagarism, $low_bandwidth);
  while ($result->fetch()) {
    if ($ip_no == 0) {
      echo "<table class=\"header\">\n";
      echo "<tr><th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./index.php\">" . $string['administrativetools'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./list_labs.php\">" . $string['editcomputerlab'] . "</a></div><div style=\"font-size:220%; font-weight:bold; margin-left:10px\">Edit Lab</div></th>\n";
      echo "<th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(231); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";
      echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";
      echo "<br />\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; margin-left:10px; margin-right:10px\">\n<tr><td style=\"vertical-align:top; width:200px\"><div><strong>" . $string['ipaddresses'] . "</strong></div>\n";
      echo "<textarea cols=\"20\" rows=\"28\" style=\"width:200px; height:590px\" name=\"addresses\">\n";
    }
    echo $address . "\n";
    $ip_no++;
  }
  $result->close();
  
  echo "</textarea></td><td style=\"width:50px\"></td><td style=\"vertical-align:top\">\n";
  echo "<div><strong>" . $string['name'] . "</strong></div>\n<div><input type=\"text\" size=\"40\" name=\"name\" value=\"$name\" /></div>\n";
  echo "<br /><div><strong>" . $string['campus'] . "</strong></div>\n<div><select name=\"campus\">\n";
  foreach ($cfg_campus_list as $choice) {
    if ($campus == $choice) {
      echo "<option value=\"$choice\" selected>$choice</option>\n";
    } else {
      echo "<option value=\"$choice\">$choice</option>\n";
    }
  }
  echo "</select></div>\n";
  echo "<br /><div><strong>" . $string['building'] . "</strong></div>\n<div><input type=\"text\" size=\"40\" name=\"building\" value=\"$building\" /></div>\n";
  echo "<br /><div><strong>" . $string['roomnumber'] . "</strong></div>\n<div><input type=\"text\" size=\"10\" name=\"room_no\" value=\"$room_no\" /></div>\n";
  echo "<br /><div><strong>" . $string['bandwidth'] . "</strong></div>\n<div><input type=\"radio\" name=\"low_bandwidth\" value=\"1\"";
  if ($low_bandwidth == 1) echo ' checked';
  echo " />" . $string['low'] . "&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"low_bandwidth\" value=\"0\" ";
  if ($low_bandwidth == 0) echo ' checked';
  echo "/>" . $string['high'] . "</div>\n";
  echo "<br /><div><strong>" . $string['timetabling'] . "</strong></div>\n<div><textarea name=\"timetabling\" rows=\"3\" cols=\"100\">$timetabling</textarea></div>\n";
  echo "<br /><div><strong>" . $string['itsupport'] . "</strong></div>\n<div><textarea name=\"it_support\" rows=\"3\" cols=\"100\">$it_support</textarea></div>\n";
  echo "<br /><div><strong>" . $string['plagarism'] . "</strong></div>\n<div><textarea name=\"plagarism\" rows=\"3\" cols=\"100\">$plagarism</textarea></div>\n";
  echo "<br /><br /><input type=\"submit\" name=\"submit\" value=\"" . $string['save'] . "\" style=\"width:120px\" />\n";
  echo "</td></tr>\n</table>\n";
?>
</form>
</div>

</body>
</html>
<?php
}

$mysqli->close();
?>