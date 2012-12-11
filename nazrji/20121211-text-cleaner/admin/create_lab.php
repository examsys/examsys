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

$bad_addresses = array();
if (isset($_POST['submit'])) {
  // Insert into Lab table.
  $result = $mysqli->prepare("INSERT INTO labs VALUES (NULL,?,?,?,?,?,?,?)");
  
  $lab_name = $_POST['lab_name'];
  $campus = $_POST['campus'];
  $building = $_POST['building'];
  $room_no = $_POST['room_no'];
  $timetabling = $_POST['timetabling'];
  $it_support = $_POST['it_support'];
  $plagarism = $_POST['plagarism'];
  
  $result->bind_param('sssssss', $lab_name,$campus,$building,$room_no,$timetabling,$it_support,$plagarism);
  $result->execute();  
  $labID = $mysqli->insert_id;
  $result->close();

  // Insert the new IP addresses.
  $addresses = explode('<br />',nl2br($_POST['addresses']));
  foreach ($addresses as $individual_address) {
    $ip_address = trim($individual_address);
    if ($ip_address != '') {
      if (preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $ip_address)) {
        if ($ip_address != '') {
          $hostname = gethostbyaddr($ip_address);
          $result = $mysqli->prepare("INSERT INTO ip_addresses VALUES (NULL,?,?,?,?)");
          $result->bind_param('issi', $labID, $ip_address, $hostname, $_POST['low_bandwidth']);
          $result->execute();  
          $result->close();
        }
      } else {
        $bad_addresses[] = $ip_address;
      }
    }
  }
  
  if (count($bad_addresses) == 0) header("location: list_labs.php");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['createnewlab']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    input, textarea {line-height:140%}
  </style>

  <script language="JavaScript">
    function clearName() {
      document.getElementById("lab_name").value = '';
      document.getElementById("lab_name").style.color = 'black';    
    }
    
    function checkForm() {
      if (document.getElementById('addresses').value == '') {
        alert('<?php echo $string['noipaddresses']; ?>');
        return false;
      }
    }
  </script>
</head>

<body>
<?php
  require '../include/lab_options.inc';
?>
<div id="content" class="content">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return checkForm()">
<table class="header">
<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><?php echo $string['createnewlab']; ?></div></th></tr>
<tr><th class="bevel"></th></tr>
<?php
if (count($bad_addresses) > 0) {
?>
<tr><td style="color: #f00; font-weight: bold">
<?php
  $address_list = '';
  foreach ($bad_addresses as $bad) {
    $address_list .= $bad . ', ';
  }
  $address_list = rtrim($address_list, ', ');
  printf($string['badaddressesmsg'], $address_list);
?>
<br /><br /><a href="./list_labs.php"><?php echo $string['backtolabs'] ?></a></td></tr>
</table>
</body>
</html>
<?php
} else {
?>
</table>
<br />
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; margin-left:10px; margin-right:10px">
<tr><td style="vertical-align:top; width:200px"><div><strong><?php echo $string['ipaddresses']; ?></strong></div>
<textarea cols="20" rows="28" style="width:200px; height:590px" name="addresses" id="addresses"></textarea></td><td style="width:50px"></td><td style="vertical-align:top">

<div><strong><?php echo $string['name']; ?></strong></div>
<div><input type="text" size="40" name="lab_name" value="" /></div>
<?php
  echo "<br /><div><strong>" . $string['campus'] . "</strong></div>\n<div><select name=\"campus\">\n<option value=\"\"></option>\n";
  foreach ($cfg_campus_list as $choice) {
    echo "<option value=\"$choice\">$choice</option>\n";
  }
  echo "</select></div>\n";
?>
<br /><div><strong><?php echo $string['building']; ?></strong></div>
<div><input type="text" size="40" name="building" value="" /></div>
<br /><div><strong><?php echo $string['roomnumber']; ?></strong></div>
<div><input type="text" size="10" name="room_no" value="" /></div>
<br /><div><strong><?php echo $string['bandwidth']; ?></strong></div><div><input type="radio" name="low_bandwidth" value="1" /><?php echo $string['low']; ?>&nbsp;&nbsp;&nbsp;<input type="radio" name="low_bandwidth" value="0" checked /><?php echo $string['high']; ?></div>
<br /><div><strong><?php echo $string['timetabling']; ?></strong></div>
<div><textarea name="timetabling" rows="3" cols="100"></textarea></div>
<br /><div><strong><?php echo $string['itsupport']; ?></strong></div>
<div><textarea name="it_support" rows="3" cols="100"></textarea></div>
<br /><div><strong><?php echo $string['plagarism']; ?></strong></div>
<div><textarea name="plagarism" rows="3" cols="100"></textarea></div>
<br /><br /><input type="submit" name="submit" value="<?php echo $string['save']; ?>" style="width:120px" />
</td></tr></table>

</form>
</div>

</body>
</html>
<?php
}
?>