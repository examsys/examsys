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

require '../include/sysadmin_auth.inc';
require '../include/errors.php';

$lab_id = check_var('labID', 'REQUEST', true, false, true);

$results = $mysqli->prepare("SELECT labs.name, campus.id, building, room_no, timetabling, it_support, plagarism FROM
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

$bad_addresses = array();
if (isset($_POST['submit'])) {
  // Delete the existing addresses for the lab first.
  $result = $mysqli->prepare("DELETE FROM client_identifiers WHERE lab = ?");
  $result->bind_param('i', $lab_id);
  $result->execute();
  $result->close();
	
	// Get a list of all existing addresses.
	$existing_addresses = array();
  $result = $mysqli->prepare("SELECT address FROM client_identifiers");
  $result->execute();
	$result->bind_result($address);
	while (	$result->fetch()) {
		$existing_addresses[$address] = 1;
	}
  $result->close();	

  // Insert the new addresses.
  $addresses = explode('<br />', nl2br($_POST['addresses']));
  foreach ($addresses as $individual_address) {
    $address = trim($individual_address);
    if ($address != '' and !isset($existing_addresses[$address])) {
      if ($configObject->get('cfg_client_lookup') == 'name') {
        $test_re = '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/';
      } else {
        $test_re = '/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';
      }

      if (preg_match($test_re, $address)) {
        if ($configObject->get('cfg_client_lookup') == 'name') {
          $hostname = $address;
        } else {
          $hostname = gethostbyaddr($address);
        }
        $result = $mysqli->prepare("INSERT INTO client_identifiers VALUES (NULL, ?, ?, ?, ?)");
        $result->bind_param('issi', $lab_id, $address, $hostname, $_POST['low_bandwidth']);
        $result->execute();
        $result->close();
      } else {
        $bad_addresses[] = $address;
      }
    }
  }

  // Edit Lab table.
  $result = $mysqli->prepare("UPDATE labs SET name = ?, campus = ?, building = ?, room_no = ?, timetabling = ?, it_support = ?, plagarism = ? WHERE id = ?");
  $result->bind_param('sisssssi', $_POST['name'], $_POST['campus'], $_POST['building'], $_POST['room_no'], $_POST['timetabling'], $_POST['it_support'], $_POST['plagarism'], $lab_id);
  $result->execute();
  $result->close();

  if (count($bad_addresses) == 0) {
    header("location: lab_details.php?labID=$lab_id");
    exit();
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rog&#333;: <?php echo $string['editcomputerlab']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
    });
  </script>
</head>

<body>
<?php
  require '../include/lab_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(231);
?>
<div id="content">
<form id="theform" action="<?php echo $_SERVER['PHP_SELF'] . '?labID=' . $_GET['labID']; ?>" method="post" autocomplete="off">

<?php
  $ip_no = 0;
  $result = $mysqli->prepare("SELECT address, low_bandwidth FROM client_identifiers WHERE lab = ?");
  $result->bind_param('i', $lab_id);
  $result->execute();
  $result->bind_result($address, $low_bandwidth);
  while ($result->fetch()) {
    if ($ip_no == 0) {
      echo "<div class=\"head_title\">\n";
      echo "<img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" />\n";
      echo "<tr><th><div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a><img src=\"../artwork/breadcrumb_arrow.png\" class=\"breadcrumb_arrow\" alt=\"-\" /><a href=\"./index.php\">" . $string['administrativetools'] . "</a><img src=\"../artwork/breadcrumb_arrow.png\" class=\"breadcrumb_arrow\" alt=\"-\" /><a href=\"./list_labs.php\">" . $string['editcomputerlab'] . "</a></div>\n";
      echo "<div class=\"page_title\">Edit Lab</div>\n";
      echo "</div>\n";
      if (count($bad_addresses) > 0) {
        echo "<tr><td style=\"color: #f00; font-weight: bold\">\n";
        $address_list = '';
        foreach ($bad_addresses as $bad) {
          $address_list .= $bad . ', ';
        }
        $address_list = rtrim($address_list, ', ');
        printf($string['badaddressesmsg'], $address_list);
?>
<br /><br /><a href="./lab_details.php?labID=<?php echo $lab_id ?>"><?php echo $string['backtolab'] ?></a></td></tr>
</body>
</html>
<?php
        exit;
      }
      echo "</table>\n";
      echo "<br />\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; margin-left:10px; margin-right:10px\">\n<tr><td style=\"vertical-align:top; width:200px\"><div><strong>" . $string['ipaddresses'] . "</strong></div>\n";
      echo "<textarea cols=\"20\" rows=\"28\" style=\"width:250px; height:590px\" name=\"addresses\" required>\n";
    }
    echo $address . "\n";
    $ip_no++;
  }
  $result->close();

  echo "</textarea></td><td style=\"width:50px\"></td><td style=\"vertical-align:top\">\n";
  echo "<div><strong>" . $string['name'] . "</strong></div>\n<div><input type=\"text\" size=\"40\" maxlength=\"255\" name=\"name\" value=\"$name\" required /></div>\n";
  echo "<br /><div><strong>" . $string['campus'] . "</strong></div>\n<div><select name=\"campus\">\n";
  $campusobj = new campus($mysqli);
  $campuses = $campusobj->get_all_campus_details();
  foreach ($campuses as $key => $campusarray) {
    if ($campus == $key) {
      echo "<option value=\"" . $key . "\" selected>" . $campusarray['campusname'] . "</option>\n";
    } else {
      echo "<option value=\"" . $key . "\">" . $campusarray['campusname']. "</option>\n";
    }
  }
  echo "</select></div>\n";
  echo "<br /><div><strong>" . $string['building'] . "</strong></div>\n<div><input type=\"text\" size=\"40\" maxlength=\"255\" name=\"building\" value=\"$building\" required /></div>\n";
  echo "<br /><div><strong>" . $string['roomnumber'] . "</strong></div>\n<div><input type=\"text\" size=\"10\" maxlength=\"255\" name=\"room_no\" value=\"$room_no\" required /></div>\n";
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
$mysqli->close();
?>