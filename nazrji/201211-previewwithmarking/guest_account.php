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
* Looks up the next free temporary account and reserves it for the current user.
* Use 'class_totals.php' to reassign marks after the exam.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once './config/config.inc.php';
require_once './classes/lang.class.php';
require_once './include/auth.inc';
require_once './classes/networkutils.class.php';

$mysqli = new mysqli($cfg_db_host, $cfg_db_student_user, $cfg_db_student_passwd, $cfg_db_database);

// Check that the ip_address of the current user is within the exam lab.
$paper_match = false;
$ip_match = false;
$results = $mysqli->prepare("SELECT labs FROM properties WHERE start_date < DATE_ADD(NOW(), interval 15 minute) AND end_date > NOW() AND paper_type IN ('1','2') AND labs != ''");
$results->execute();
$results->store_result();
$results->bind_result($labs);
while ($results->fetch()) {
  $paper_match = true;
  $sub_results = $mysqli->prepare("SELECT address FROM ip_addresses WHERE lab IN ($labs)");
  $sub_results->execute();
  $sub_results->store_result();
  $sub_results->bind_result($address);
  while ($sub_results->fetch()) {
    if (NetworkUtils::get_ipaddress() == $address) $ip_match = true;
  }
  $sub_results->close();
}
$results->close();

if ($paper_match == false) {
  access_denied($string['cannotfindexams'], false, true);
} elseif ($ip_match == false) {
  access_denied($string['denied_msg'], false, true);
}
?>

<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['guestaccount']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="./css/body.css" />
  <link rel="stylesheet" type="text/css" href="./css/guest_account.css" />
</head>

<body>

<?php
if (isset($_POST['submit'])) {
  // Update the temp_user table with the completed student details.
  $tmp_first_names = trim($_POST['first_names']);
  $tmp_surname = trim($_POST['surname']);
  $tmp_student_id = trim($_POST['student_id']);
  
  $stmt = $mysqli->prepare("UPDATE temp_users SET first_names=?, surname=?, title=?, student_id=? WHERE id=?");
  $stmt->bind_param('ssssi', $tmp_first_names, $tmp_surname, $_POST['title'], $tmp_student_id, $_POST['recordID']);
  $stmt->execute();
  $stmt->close();
  
  echo '<div align="center"><table cellpadding="0" cellspacing="0" style="text-align:left; width:450px; border:1px #C8C8C8 solid">';
  echo '<tr><td class="topbar" style="padding-left:6px; width:60px"><img src="./artwork/guest_account.png" width="48" height="48" /></td><td class="topbar" style="width:390px">' . $string['allocatedaccount'] . '</td></tr>';
  echo '<tr><td colspan="2"><table style="width:100%; text-align:left"><tr><td style="padding:6px">' . $string['username'] . '</td><td><tt>' . $_POST['username'] . '</tt></td></tr>';
  echo '<tr><td style="padding:6px">' . $string['password'] . '</td><td><tt>' . $_POST['password'] . '</tt></td></tr>';
  echo '<tr><td colspan="2"><td>&nbsp;</td></tr>';
  echo '<tr><td style="text-align:center"><td><input type="button" name="login" value="' . $string['login'] . '" style="width:120px" onclick="window.location=\'' . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . '/index.php\';" /></td></tr>';
  echo '<tr><td><td>&nbsp;</td></tr>';
  echo '</table></td></tr></table></div>';
} else {
  $used_accounts = array();
  
  $results = $mysqli->prepare("SELECT assigned_account FROM temp_users");
  $results->execute();
  $results->bind_result($assigned_account);
  while ($row=$results->fetch()) {
    $used_accounts[$assigned_account] = true;
  }
  $results->close();
  
  $free_account = '';
  for ($i=1; $i<=100; $i++) {
    if (!isset($used_accounts['user' . $i])) {
      $free_account = 'user' . $i;
      break;
    }
  }
  
  // Reserve this free account first.
  $stmt = $mysqli->prepare("INSERT INTO temp_users VALUES (NULL, NULL, NULL, NULL, NULL, ?, NOW())");
  $stmt->bind_param('s', $free_account);
  $stmt->execute();
  $stmt->close();
  $recordID = $mysqli->insert_id;
  
  // Reset password on the chosen guest account.
  $color = array('blue', 'green', 'orange', 'gold', 'silver', 'purple', 'white', 'black', 'yellow');
  $random_password = $color[rand(0,4)] . rand(10,99);
  $tmp_password = encpw($cfg_encrypt_salt, $free_account, $random_password);
  $stmt = $mysqli->prepare("UPDATE users SET password=? WHERE username=?");
  $stmt->bind_param('ss', $tmp_password, $free_account);
  $stmt->execute();
  $stmt->close();
  
  
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['guestaccount']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="./css/body.css" />
  <link rel="stylesheet" type="text/css" href="./css/guest_account.css" />
</head>

<body>
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div style="text-align:center">
<table cellpadding="0" cellspacing="0" style="text-align:left; margin-left:auto; margin-right:auto; width:450px; border:1px #C8C8C8 solid">
<tr><td class="topbar" style="padding-left:6px; width:60px"><img src="./artwork/guest_account.png" width="48" height="48" /></td><td class="topbar" style="width:390px"><?php echo $string['guestaccountreg']; ?></td></tr>

<tr><td style="text-align:center; padding:6px" colspan="2">
<table cellpadding="2" cellspacing="0" style="width:100%; border:0px; text-align:left">
<tr><td><?php echo $string['title']; ?></td><td><select name="title"><option value="Mr">Mr</option><option value="Miss">Miss</option><option value="Mrs">Mrs</option><option value="Ms">Ms</option><option value="Dr">Dr</option></select></td></tr>
<tr><td><?php echo $string['firstname']; ?></td><td><input type="text" name="first_names" value="" size="40" /></td></tr>
<tr><td><?php echo $string['surname']; ?></td><td><input type="text" name="surname" value="" size="40" /></td></tr>
<tr><td><?php echo $string['studentid']; ?></td><td><input type="text" name="student_id" value="" size="20" /></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['ok']; ?>" style="width:100px" /></td></tr>
</table>
</td></tr>
</table>

</div>
<input type="hidden" name="recordID" value="<?php echo $recordID; ?>" />
<input type="hidden" name="username" value="user<?php echo $i; ?>" />
<input type="hidden" name="password" value="<?php echo $random_password; ?>" />
</form>
</body>
</html>
<?php
}
$mysqli->close();
?>