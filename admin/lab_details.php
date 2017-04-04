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

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$lab_id = check_var('labID', 'GET', true, false, true);

$results = $mysqli->prepare("SELECT labs.name, campus.name, building, room_no, timetabling, it_support, plagarism FROM
    labs, campus WHERE labs.campus = campus.id AND labs.id = ? LIMIT 1");
$results->bind_param('i', $lab_id);
$results->execute();
$results->store_result();
$results->bind_result($name, $campus, $building, $room_no, $timetabling, $it_support, $plagarism);
if ($results->num_rows == 0) {
  $results->close();
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$results->fetch();
$results->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['labdetails']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
	
  <style type="text/css">
    .foldername {float:left; width:380px; height:60px; padding-left:12px; font-size:80%}
  </style>
	
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/lab_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(231);
?>
<div id="content">
<?php
  $ip_no = 0;

  $results = $mysqli->prepare("SELECT address, hostname, low_bandwidth FROM client_identifiers WHERE lab = ?");
  $results->bind_param('i', $lab_id);
  $results->execute();
  $results->store_result();
  $results->bind_result($address, $hostname, $low_bandwidth);
  while ($results->fetch()) {
    if ($ip_no == 0) {
      echo "<div class=\"head_title\">\n";
      echo "<img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" />\n";
      echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a><img src=\"../artwork/breadcrumb_arrow.png\" class=\"breadcrumb_arrow\" alt=\"-\" /><a href=\"./index.php\">" . $string['administrativetools'] . "</a><img src=\"../artwork/breadcrumb_arrow.png\" class=\"breadcrumb_arrow\" alt=\"-\" /><a href=\"./list_labs.php\">" . $string['computerlabs'] . "</a></div>";
      echo "<div class=\"page_title\">$name</div>\n";
      echo "</div>\n";
      echo "<br />\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; margin-left:10px; margin-right:10px\">\n<tr><td style=\"vertical-align:top; width:440px\"><div><strong>" . $string['ipaddresses'] . " (" . $results->num_rows . ")</strong></div>\n<div style=\"height:590px; overflow-y:scroll; border: 1px solid #EEEDE5\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
    }

    if ($configObject->get('cfg_client_lookup') == 'name') {
      echo "<tr><td><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"PC icon\" />&nbsp;</td><td style=\"width:135px\">$address</td></tr>\n";
    } else {
      if ($address == $hostname) {
        echo "<tr><td><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"PC icon\" />&nbsp;</td><td style=\"width:200px; color:red\">$address</td><td style=\"color:red\">$hostname</td></tr>\n";
      } else {
        echo "<tr><td><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"PC icon\" />&nbsp;</td><td style=\"width:200px\">$address</td><td style=\"color:#808080\">$hostname</td></tr>\n";
      }
    }

    $ip_no++;
  }
  $results->close();
  echo "</table></div></td><td style=\"width:50px\"></td><td style=\"vertical-align:top\">\n";
  echo "<div><strong>" . $string['campus'] . "</strong></div>\n<div>$campus</div>\n";
  echo "<br /><div><strong>" . $string['building'] . "</strong></div>\n<div>$building</div>\n";
  echo "<br /><div><strong>" . $string['roomnumber'] . "</strong></div>\n<div>$room_no</div>\n";
  if ($low_bandwidth == 0) {
    echo "<br /><div><strong>" . $string['bandwidth'] . "</strong></div>\n<span style=\"background-color:#008000; color:white\">&nbsp;" . $string['high'] . "&nbsp;</span><br />\n";
  } else {
    echo "<br /><div><strong>" . $string['bandwidth'] . "</strong></div>\n<span style=\"background-color:#C00000; color:white\">&nbsp;" . $string['low'] . "&nbsp;</span><br />\n";
  }
  echo "<br /><div><strong>" . $string['timetabling'] . "</strong></div>\n<div>$timetabling</div>\n";
  echo "<br /><div><strong>" . $string['itsupport'] . "</strong></div>\n<div>$it_support</div>\n";
  echo "<br /><div><strong>" . $string['plagarism'] . "</strong></div>\n<div>$plagarism</div>\n";
  if ($userObject->has_role(array('SysAdmin', 'Admin'))) {
    echo "<br /><br /><input type=\"button\" onclick=\"window.location='edit_lab.php?labID=" . $_GET['labID'] . "'\" value=\"" . $string['edit'] . "\" style=\"width:120px\" />\n";
  }
  echo "</td></tr>\n</table>\n";
  $mysqli->close();
?>
</div>
</body>
</html>