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

$bad_addresses = array();
if (isset($_POST['submit'])) {
  // Insert into Lab table.

  $lab_name = $_POST['lab_name'];
  $campus = $_POST['campus'];
  $building = $_POST['building'];
  $room_no = $_POST['room_no'];
  $timetabling = $_POST['timetabling'];
  $it_support = $_POST['it_support'];
  $plagarism = $_POST['plagarism'];

  $result = $mysqli->prepare("INSERT INTO labs VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)");
  $result->bind_param('sisssss', $lab_name, $campus, $building, $room_no, $timetabling, $it_support, $plagarism);
  $result->execute();
  $labID = $mysqli->insert_id;
  $result->close();

	// Get a list of all existing addresses.
	$existing_addresses = array();
  $result = $mysqli->prepare("SELECT address FROM client_identifiers");
  $result->execute();
	$result->bind_result($address);
	while ($result->fetch()) {
		$existing_addresses[$address] = 1;
	}
  $result->close();

  // Insert the new IP addresses.
  $addresses = explode('<br />', nl2br($_POST['addresses']));
  $addresscount = 0;
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
        $result->bind_param('issi', $labID, $address, $hostname, $_POST['low_bandwidth']);
        $result->execute();
        $result->close();
        $addresscount++;
      } else {
        $bad_addresses[] = $address;
      }
    }
  }
  // Delete lab if no client identifiers assoiciated.
  if ($addresscount == 0) {
    $result = $mysqli->prepare("DELETE FROM labs WHERE id = ?");
    $result->bind_param('i', $labID);
    $result->execute();
    $result->close();
  }
  if (count($bad_addresses) == 0) {
    header("location: list_labs.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['createnewlab']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
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
	
	echo draw_toprightmenu(233);
?>
<div id="content">
<form id="theform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./list_labs.php"><?php echo $string['computerlabs'] ?></a></div>
  <div class="page_title"><?php echo $string['createnewlab'] ?></div>
</div>

<?php
if (count($bad_addresses) > 0) {
?>
<div style="color: #f00; font-weight: bold">
<?php
  $address_list = '';
  foreach ($bad_addresses as $bad) {
    $address_list .= $bad . ', ';
  }
  $address_list = rtrim($address_list, ', ');
  printf($string['badaddressesmsg'], $address_list);
?>
<br /><br /><a href="./list_labs.php"><?php echo $string['backtolabs'] ?></a></div>
</body>
</html>
<?php
} else {
?>
</table>
<br />
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; margin-left:10px; margin-right:10px">
<tr><td style="vertical-align:top; width:200px"><div><?php echo $string['ipaddresses'] ?></div>
<textarea cols="20" rows="28" style="width:200px; height:590px" name="addresses" id="addresses" required></textarea></td><td style="width:50px"></td><td style="vertical-align:top">

<div><?php echo $string['name'] ?></div>
<div><input type="text" size="40" maxlength="255" name="lab_name" id="lab_name" value="" required /></div>
<?php
  echo "<br /><div>" . $string['campus'] . "</div>\n<div><select name=\"campus\">\n";
  $campusobj = new campus($mysqli);
  $campuses = $campusobj->get_all_campus_details();
  foreach ($campuses as $key => $campusarray) {
    if ($campusarray['isdefault']) {
      echo "<option value=\"" . $key . "\" selected>" . $campusarray['campusname'] . "</option>\n";
    } else {
      echo "<option value=\"" . $key . "\">" . $campusarray['campusname'] . "</option>\n";
    }
  }
  echo "</select></div>\n";
?>
<br /><div><?php echo $string['building'] ?></div>
<div><input type="text" size="40" maxlength="255" name="building" value="" required /></div>
<br /><div><?php echo $string['roomnumber'] ?></div>
<div><input type="text" size="10" maxlength="255" name="room_no" value="" required /></div>
<br /><div><?php echo $string['bandwidth'] ?></div><div><input type="radio" name="low_bandwidth" value="1" /><?php echo $string['low'] ?>&nbsp;&nbsp;&nbsp;<input type="radio" name="low_bandwidth" value="0" checked /><?php echo $string['high'] ?></div>
<br /><div><?php echo $string['timetabling'] ?></div>
<div><textarea name="timetabling" rows="3" cols="100"></textarea></div>
<br /><div><?php echo $string['itsupport'] ?></div>
<div><textarea name="it_support" rows="3" cols="100"></textarea></div>
<br /><div><?php echo $string['plagarism'] ?></div>
<div><textarea name="plagarism" rows="3" cols="100"></textarea></div>
<br /><br /><input type="submit" name="submit" value="<?php echo $string['save'] ?>" class="ok" />
</td></tr></table>

</form>
</div>

</body>
</html>
<?php
}
?>