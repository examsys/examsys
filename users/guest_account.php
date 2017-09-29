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
 * @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/load_config.php';
require_once '../include/auth.inc';

$language = LangUtils::getLang($cfg_web_root);
LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));

$mysqli = new mysqli($configObject->get('cfg_db_host'), $configObject->get('cfg_db_student_user'), $configObject->get('cfg_db_student_passwd'), $configObject->get('cfg_db_database'));

// Check client address of current user is in a lab.
$address = NetworkUtils::get_client_address();
$labfactory = new LabFactory($mysqli);
$lab = false;
$lab = $labfactory->get_lab_from_address($address);
// Check lab of current user is running an exam.
$paper = false;
if ($lab) {
  $paper = PaperUtils::paper_available_in_lab_now($lab, $mysqli);
}

if (!$lab) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/page_not_found.png', '#C00000', true, true);
} elseif ($paper === false) {
  $notice->access_denied($mysqli, $string, $string['cannotfindexams'], false, true);
} 
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['guestaccount']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/guest_account.css" />
</head>

<body>

<?php
if (isset($_POST['submit'])) {
  // Update the temp_user table with the completed student details.
  $tmp_first_names = trim($_POST['first_names']);
  $tmp_surname = trim($_POST['surname']);
  $tmp_student_id = trim($_POST['student_id']);
	
	if ($tmp_first_names == '' or $tmp_surname == '') {
		$notice->display_notice_and_exit($mysqli, $string['error'], $string['mandatory'], $string['error'], '../artwork/exclamation_red_bg.png', '#C00000', false, true);
	}

  $stmt = $mysqli->prepare("UPDATE temp_users SET first_names = ?, surname = ?, title = ?, student_id = ? WHERE id = ?");
  $stmt->bind_param('ssssi', $tmp_first_names, $tmp_surname, $_POST['title'], $tmp_student_id, $_POST['recordID']);
  $stmt->execute();
  $stmt->close();

  echo '<form method="post" action="' . $configObject->get('cfg_root_path') . '/paper/index.php" autocomplete="off">';
  echo '<input type="hidden" name="ROGO_USER" value="' . $_POST['username'] . '" />';
  echo '<input type="hidden" name="ROGO_PW" value="' . $_POST['password'] . '" />';
  echo '<div align="center"><table cellpadding="0" cellspacing="0" style="text-align:left; width:450px; border:1px #7F9DB9 solid; background-color:#EEF4FF">';
  echo '<tr><td class="topbar" style="padding-left:6px; width:60px"><img src="../artwork/guest_account.png" width="48" height="48" /></td><td class="topbar" style="width:390px">' . $string['allocatedaccount'] . '</td></tr>';
  echo '<tr><td colspan="2" style="padding:8px">' . $string['msg'] . '</td></tr>';
  echo '<tr><td colspan="2"><table style="width:100%; text-align:left"><tr><td style="padding:6px">' . $string['username'] . '</td><td><tt>' . $_POST['username'] . '</tt></td></tr>';
  echo '<tr><td style="padding:6px">' . $string['password'] . '</td><td><tt>' . $_POST['password'] . '</tt></td></tr>';
  echo '<tr><td colspan="2"><td>&nbsp;</td></tr>';
  echo '<tr><td style="text-align:center"><td><input type="submit" name="rogo-login-form-std" value="' . $string['login'] . '" class="ok" /></td></tr>';
  echo '<tr><td><td>&nbsp;</td></tr>';
  echo '</table></td></tr></table></div></form>';
} else {
  $used_accounts = array();

  $results = $mysqli->prepare("SELECT assigned_account FROM temp_users");
  $results->execute();
  $results->bind_result($assigned_account);
  while ($results->fetch()) {
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

  // Get the user ID
  $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->bind_param('s', $free_account);
  $stmt->execute();
  $stmt->bind_result($temp_user_id);
  $stmt->fetch();
  $stmt->close();

  // Reset password on the chosen guest account.
  $color = array('blue', 'green', 'orange', 'gold', 'silver', 'purple', 'white', 'black', 'yellow');
  $random_password = $color[rand(0, 4)] . rand(10, 99);
  UserUtils::update_password($free_account, $random_password, $temp_user_id, $mysqli);

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['guestaccount']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/guest_account.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
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
<form name="theform" id="theform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
<div style="text-align:center">
<table cellpadding="0" cellspacing="0" style="text-align:left; margin-left:auto; margin-right:auto; width:450px; border:1px #7F9DB9 solid; background-color:#EEF4FF">
<tr><td class="topbar" style="padding-left:6px; width:60px"><img src="../artwork/guest_account.png" width="48" height="48" /></td><td class="topbar" style="width:390px"><?php echo $string['guestaccountreg']; ?></td></tr>

<tr><td style="text-align:center; padding:6px" colspan="2">
<table cellpadding="2" cellspacing="0" style="width:100%; border:0px; text-align:left">
<tr><td><?php echo $string['title']; ?></td><td><input type="radio" name="title" id="mx" value="Mx" /><label for="mx">Mx</label>&nbsp;&nbsp;<input type="radio" name="title" id="mr" value="Mr" /><label for="mr">Mr</label>&nbsp;&nbsp;<input type="radio" name="title" id="miss" value="Miss" /><label for="miss">Miss</label>&nbsp;&nbsp;<input type="radio" name="title" id="mrs" value="Mrs" /><label for="mrs">Mrs</label>&nbsp;&nbsp;<input type="radio" name="title" id="ms" value="Ms" /><label for="ms">Ms</label>&nbsp;&nbsp;<input type="radio" name="title" id="dr" value="Dr" /><label for="dr">Dr</label></td></tr>
<tr><td><?php echo $string['firstname']; ?></td><td><input type="text" name="first_names" id="first_names" value="" size="40" maxlength="60" required /></td></tr>
<tr><td><?php echo $string['surname']; ?></td><td><input type="text" name="surname" id="surname" value="" size="40" maxlength="50" required /></td></tr>
<tr><td><?php echo $string['studentid']; ?></td><td><input type="text" name="student_id" value="" size="20" /></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['ok']; ?>" class="ok" /></td></tr>
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