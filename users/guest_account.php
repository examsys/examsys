<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

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

$language = LangUtils::getLang($cfg_web_root);
LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));

$mysqli = new mysqli($configObject->get('cfg_db_host'), $configObject->get('cfg_db_student_user'), $configObject->get('cfg_db_student_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_port'));
$configObject->set_db_object($mysqli);
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
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
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
    $tmp_first_names = trim(param::optional('first_names', '', param::TEXT));
    $tmp_surname = trim(param::optional('surname', '', param::TEXT));
    $tmp_student_id = trim(param::optional('student_id', '', param::ALPHANUM));
    $recordID = param::optional('recordID', 0, param::INT);
    $username = param::optional('username', '', param::TEXT);
    $password = param::optional('password', '', param::TEXT);
    $title = param::optional('title', '', param::TEXT);

    if ($tmp_first_names == '' or $tmp_surname == '') {
        $notice->display_notice_and_exit($mysqli, $string['error'], $string['mandatory'], $string['error'], '/artwork/exclamation_red_bg.png', '#C00000', false, true);
    }

    if (!GuestAccountManager::isReservationValid($recordID)) {
        $notice->display_notice_and_exit(null, $string['reservationexpired'], $string['reservationexpiredmessage'], $string['reservationexpired'], '/artwork/exclamation_red_bg.png', '#C00000', false, true);
    }

    GuestAccountManager::setDetails($tmp_first_names, $tmp_surname, $tmp_student_id, $title, $recordID);

    echo '<form method="post" action="' . $configObject->get('cfg_root_path') . '/paper/index.php" autocomplete="off">';
    echo '<input type="hidden" name="ROGO_USER" value="' . $username . '" />';
    echo '<input type="hidden" name="ROGO_PW" value="' . $password . '" />';
    echo '<div align="center"><table cellpadding="0" cellspacing="0" style="text-align:left; width:450px; border:1px #7F9DB9 solid; background-color:#EEF4FF">';
    echo '<tr><td class="topbar" style="padding-left:6px; width:60px"><img src="../artwork/guest_account.png" width="48" height="48" /></td><td class="topbar" style="width:390px">' . $string['allocatedaccount'] . '</td></tr>';
    echo '<tr><td colspan="2" style="padding:8px">' . $string['msg'] . '</td></tr>';
    echo '<tr><td colspan="2"><table style="width:100%; text-align:left"><tr><td style="padding:6px">' . $string['username'] . '</td><td><tt>' . $username . '</tt></td></tr>';
    echo '<tr><td style="padding:6px">' . $string['password'] . '</td><td><tt>' . $password . '</tt></td></tr>';
    echo '<tr><td colspan="2"><td>&nbsp;</td></tr>';
    echo '<tr><td style="text-align:center"><td><input type="submit" name="rogo-login-form-std" value="' . $string['login'] . '" class="ok" /></td></tr>';
    echo '<tr><td><td>&nbsp;</td></tr>';
    echo '</table></td></tr></table></div></form>';
} else {
    try {
        $account = GuestAccountManager::reserve();
    } catch (NoFreeGuestAccounts $e) {
        $notice->display_notice_and_exit(null, $string['nofreeaccounts'], $string['nofreeaccountsmessage'], $string['nofreeaccounts'], '/artwork/exclamation_red_bg.png', '#C00000', false, true);
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

  <script src="../js/require.js"></script>
  <script src="../js/main.min.js"></script>
  <script src="../js/guestaccountinit.min.js"></script>
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
<input type="hidden" name="recordID" value="<?php echo $account->reservationid; ?>" />
<input type="hidden" name="username" value="<?php echo $account->username; ?>" />
<input type="hidden" name="password" value="<?php echo $account->password; ?>" />
</form>
</body>
</html>
    <?php
}
$mysqli->close();
